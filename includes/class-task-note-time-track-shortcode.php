<?php

class Task_Note_Time_Track_Shortcode {
	public function __construct() {
		add_shortcode( 'time_track', [ $this, 'handle_shortcode' ] );

		add_action( 'wp_ajax_request_time_track_shortcode_list', [ $this, 'response_time_track_shortcode_list' ] );
		add_action( 'wp_ajax_request_time_track_shortcode_save', [ $this, 'response_time_track_shortcode_save' ] );
		add_action( 'wp_ajax_request_time_track_shortcode_delete', [ $this, 'response_time_track_shortcode_delete' ] );
		add_action( 'wp_ajax_request_time_track_shortcode_finish', [ $this, 'response_time_track_shortcode_finish' ] );
	}

	public function handle_shortcode() {
		if ( ! current_user_can( 'administrator' ) ) {
			return '<p>죄송합니다. 이 페이지는 사이트 관리자만 접근할 수 있습니다.</p>' .
			       '<p><a href="' . esc_url( wp_login_url( $_SERVER['REQUEST_URI'] ) ) . '">로그인</a></p>';
		}

		wp_localize_script(
			'tn-time-track-shortcode',
			'timeTrackShortcode',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'task_note_time_track_shortcode' ),
			]
		);

		wp_enqueue_script( 'tn-time-track-shortcode' );
		wp_enqueue_style( 'tn-time-track-shortcode' );

		try {
			$current_date = new DateTimeImmutable( 'now', wp_timezone() );
		} catch ( Exception $e ) {
			wp_send_json_error( new WP_Error( $e->getCode(), $e->getMessage() ) );
			die();
		}

		return tn_template(
			'time-track-shortcode.php',
			[
				'current_date' => $current_date,
			],
			false
		);
	}

	public function response_time_track_shortcode_list() {
		check_ajax_referer( 'task_note_time_track_shortcode', 'nonce' );

		try {
			$today    = new DateTimeImmutable( 'today midnight', wp_timezone() );
			$tomorrow = $today->add( new DateInterval( 'P1D' ) );
		} catch ( Exception $e ) {
			wp_send_json_error( new WP_Error( $e->getCode(), $e->getMessage() ) );
			die();
		}

		$query = new WP_Query(
			[
				'post_type'        => TNCT::TIME_TRACK,
				'post_status'      => [ 'publish', 'pending', 'draft' ],
				'post_author'      => get_current_user_id(),
				'orderby'          => 'meta_value',
				'order'            => 'ASC',
				'meta_key'         => TNCT::DATE_BEGIN,
				'suppress_filters' => 1,
				'no_found_rows'    => true,
				'nopaging'         => true,
				'meta_query'       => [
					'relation' => 'OR',
					[
						'relation' => 'AND',
						[
							'key'     => TNCT::DATE_BEGIN,
							'value'   => $today->getTimestamp(),
							'compare' => '>=',
							'type'    => 'NUMERIC',
						],
						[
							'relation' => 'OR',
							[
								'key'     => TNCT::DATE_END,
								'value'   => $tomorrow->getTimestamp(),
								'compare' => '<',
								'type'    => 'NUMERIC',
							],
							[
								'key'   => TNCT::DATE_END,
								'value' => 0,
								'type'  => 'NUMERIC',
							],
						]
					],
					[
						'relation' => 'AND',
						[
							'key'   => TNCT::DATE_BEGIN,
							'value' => 0,
							'type'  => 'NUMERIC',
						],
						[
							'key'   => TNCT::DATE_END,
							'value' => 0,
							'type'  => 'NUMERIC',
						],
					]
				]
			]
		);

		$response = [];

		while ( $query->have_posts() ) {
			$query->the_post();
			$response[] = $this->from_post_to_response_object( get_post() );
		}

		wp_reset_postdata();

		wp_send_json_success( $response );
	}

	public function response_time_track_shortcode_save() {
		check_ajax_referer( 'task_note_time_track_shortcode', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( new WP_Error( 'error', '권한 없음' ) );
		}

		$post_id    = absint( $_REQUEST['post_id'] ?? 0 );
		$date       = sanitize_text_field( $_REQUEST['date'] ?? '' );
		$title      = sanitize_text_field( $_REQUEST['title'] ?? '' );
		$project_id = intval( $_REQUEST['project_id'] ?? 0 );
		$content    = sanitize_textarea_field( $_REQUEST['content'] ?? '' );

		if ( isset( $_REQUEST['begin_hour'] ) && is_numeric( $_REQUEST['begin_hour'] ) ) {
			$begin_hour = intval( $_REQUEST['begin_hour'] );
		} else {
			$begin_hour = - 1;
		}

		if ( isset( $_REQUEST['begin_minute'] ) && is_numeric( $_REQUEST['begin_minute'] ) ) {
			$begin_minute = intval( $_REQUEST['begin_minute'] );
		} else {
			$begin_minute = - 1;
		}

		if ( isset( $_REQUEST['end_hour'] ) && is_numeric( $_REQUEST['end_hour'] ) ) {
			$end_hour = intval( $_REQUEST['end_hour'] );
		} else {
			$end_hour = - 1;
		}

		if ( isset( $_REQUEST['end_minute'] ) && is_numeric( $_REQUEST['end_minute'] ) ) {
			$end_minute = intval( $_REQUEST['end_minute'] );
		} else {
			$end_minute = - 1;
		}

		$estimated = absint( $_REQUEST['estimated'] ?? 0 );

		try {
			if ( $begin_hour > - 1 && $begin_minute > - 1 ) {
				$begin = new DateTimeImmutable(
					sprintf( '%s %02d:%02d:00', $date, $begin_hour, $begin_minute ),
					wp_timezone()
				);
			} else {
				$begin = null;
			}
			if ( $end_hour > - 1 && $end_minute > - 1 ) {
				$end = new DateTimeImmutable(
					sprintf( '%s %02d:%02d:00', $date, $end_hour, $end_minute ),
					wp_timezone()
				);
			} else {
				$end = null;
			}
		} catch ( Exception $e ) {
			wp_send_json_error( new WP_Error( $e->getCode(), $e->getMessage() ) );
			die();
		}

		if ( $project_id > 0 ) {
			$term = get_term_by( 'id', $project_id, TNCT::PROJECT_TAG );
		} else {
			$term = null;
		}

		if ( $begin && $end ) {
			$post_status = 'publish';
		} elseif ( $begin && ! $end ) {
			$post_status = 'pending';
		} else {
			$post_status = 'draft';
		}

		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post || TNCT::TIME_TRACK !== $post->post_type ) {
				wp_send_json_error( new WP_Error( 'error', '잘못된 포스트' ) );
			}
			$post->post_author  = get_current_user_id();
			$post->post_title   = $title;
			$post->post_content = $content;
			$post->post_status  = $post_status;
			$post->post_type    = TNCT::TIME_TRACK;

			$post_id = wp_update_post( $post );
		} else {
			$post_id = wp_insert_post(
				[
					'post_author'    => get_current_user_id(),
					'post_title'     => $title,
					'post_content'   => $content,
					'post_status'    => $post_status,
					'post_type'      => TNCT::TIME_TRACK,
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				]
			);
		}

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( $post_id );
		}

		if ( $begin ) {
			update_post_meta( $post_id, TNCT::DATE_BEGIN, $begin->getTimestamp() );
		} else {
			update_post_meta( $post_id, TNCT::DATE_BEGIN, 0 );
		}

		if ( $end ) {
			update_post_meta( $post_id, TNCT::DATE_END, $end->getTimestamp() );
		} else {
			update_post_meta( $post_id, TNCT::DATE_END, 0 );
		}

		if ( $estimated ) {
			update_post_meta( $post_id, TNCT::ESTIMATED, $estimated );
		} else {
			update_post_meta( $post_id, TNCT::ESTIMATED, 0 );
		}

		if ( $term ) {
			wp_set_object_terms( $post_id, $term->term_id, TNCT::PROJECT_TAG, false );
		} else {
			wp_delete_object_term_relationships( $post_id, TNCT::PROJECT_TAG );
		}

		wp_send_json_success( $this->from_post_to_response_object( $post_id ) );
	}

	public function response_time_track_shortcode_delete() {
		check_ajax_referer( 'task_note_time_track_shortcode', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( new WP_Error( 'error', '권한 없음' ) );
		}

		$post_id = absint( $_REQUEST['post_id'] ?? 0 );
		$post    = get_post( $post_id );

		if ( ! $post || TNCT::TIME_TRACK !== $post->post_type ) {
			wp_send_json_error( new WP_Error( 'error', '잘못된 포스트' ) );
		}

		wp_delete_post( $post_id );

		wp_send_json_success( $this->from_post_to_response_object( $post_id ) );
	}

	public function response_time_track_shortcode_finish() {
		check_ajax_referer( 'task_note_time_track_shortcode', 'nonce' );

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( new WP_Error( 'error', '권한 없음' ) );
		}

		$post_id = absint( $_REQUEST['post_id'] ?? 0 );
		$post    = get_post( $post_id );

		if ( ! $post_id || ! $post || TNCT::TIME_TRACK !== $post->post_type ) {
			wp_send_json_error( new WP_Error( 'error', '잘못된 ID' ) );
		}

		$post->post_status = 'publish';

		wp_update_post( $post );

		update_post_meta( $post->ID, TNCT::DATE_END, time() );

		wp_send_json_success( $this->from_post_to_response_object( $post_id ) );
	}

	protected function from_post_to_response_object( $post ): array {
		$post    = get_post( $post );
		$project = get_the_terms( $post, TNCT::PROJECT_TAG );

		if ( is_array( $project ) && count( $project ) ) {
			$project = array_shift( $project );
		} else {
			$project = false;
		}

		$begin = get_post_meta( $post->ID, TNCT::DATE_BEGIN, true );
		if ( $begin ) {
			$begin_datetime = date_create( '@' . $begin );
			$begin_datetime->setTimezone( wp_timezone() );
		} else {
			$begin_datetime = false;
		}

		$end = get_post_meta( $post->ID, TNCT::DATE_END, true );
		if ( $end ) {
			$end_datetime = date_create( '@' . $end );
			$end_datetime->setTimezone( wp_timezone() );
		} else {
			$end_datetime = false;
		}

		$title = get_the_title( $post );
		if ( empty( $title ) ) {
			$title = '(제목 없음)';
		}

		return [
			'post_id'         => $post->ID,
			'title'           => $title,
			'content'         => $post->post_content,
			'status'          => get_post_field( 'post_status', $post ),
			'project_id'      => $project ? $project->term_id : null,
			'project_name'    => $project ? $project->name : null,
			'date'            => $begin_datetime ? $begin_datetime->format( 'Y-m-d' ) : null,
			'begin_hour'      => $begin_datetime ? $begin_datetime->format( 'H' ) : null,
			'begin_minute'    => $begin_datetime ? $begin_datetime->format( 'i' ) : null,
			'begin_timestamp' => $begin ? $begin : null,
			'end_hour'        => $end_datetime ? $end_datetime->format( 'H' ) : null,
			'end_minute'      => $end_datetime ? $end_datetime->format( 'i' ) : null,
			'end_timestamp'   => $end ? $end : null,
			'estimated'       => get_post_meta( $post->ID, TNCT::ESTIMATED, true ),
			'evaluated'       => ( $begin && $end ) ? format_timespan( $end - $begin ) : null,
			'evaluated_raw'   => ( $begin && $end ) ? $end - $begin : null,
		];
	}
}
