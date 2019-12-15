<?php

class Task_Note_Time_Track_Check {
	public function __construct() {
		add_action( 'wp_before_admin_bar_render', [ $this, 'wp_before_admin_bar_render' ] );
		add_action( 'wp_ajax_time_track_checkpoint', [ $this, 'time_track_checkpoint' ] );
		add_action( 'wp_loaded', [ $this, 'time_track' ] );
	}

	public function wp_before_admin_bar_render() {
		/** @global WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		$post = get_post();
		if ( $post && has_shortcode( $post->post_content, 'time_track' ) ) {
			return;
		}

		$tracking = $this->get_tracking();

		$wp_admin_bar->add_menu(
			[
				'id'     => 'time-track',
				'parent' => 'top-secondary',
				'title'  => '시간 추적',
				'meta'   => [
					'class' => $tracking ? 'active' : '',
				],
			]
		);
	}

	public function time_track() {
		if (
			! current_user_can( 'administrator' ) ||
			( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
			( defined( 'DOING_CRON' ) && DOING_CRON ) ||
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		) {
			return;
		}
		add_action( 'wp_footer', [ $this, 'output_time_track_form' ] );
		add_action( 'admin_footer', [ $this, 'output_time_track_form' ] );
	}

	public function output_time_track_form() {
		$projects = get_Terms(
			[
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'taxonomy'   => Task_Note_Custom_Types::PROJECT_TAG
			]
		);

		$project_slug  = '';
		$tracking_name = '';
		$untagged      = '프로젝트 미선택';
		$untitled      = '제목 없음';

		$post = get_post();
		if ( has_shortcode( $post->post_content, 'time_track' ) ) {
			return;
		}

		tn_template(
			'time-track.php',
			[
				'show'          => ! is_null( $this->get_tracking() ),
				'projects'      => array_combine( wp_list_pluck( $projects, 'slug' ), wp_list_pluck( $projects, 'name' ) ),
				'project_slug'  => $project_slug,
				'tracking_name' => empty( $tracking_name ) ? $untitled : $tracking_name,
				'untagged'      => $untagged,
				'untitled'      => $untitled,
			]
		);

		wp_localize_script(
			'tn-time-track-check',
			'tnTimeTrackCheck',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'tn-time-track-check' ),
			]
		);
		wp_enqueue_script( 'tn-time-track-check' );
		wp_enqueue_style( 'tn-time-track-check' );

		wp_enqueue_script( 'tn-time-track-admin-bar' );
		wp_enqueue_style( 'tn-time-track-admin-bar' );

	}

	public function get_tracking() {
		$posts = get_posts(
			[
				'post_status'      => 'pending',
				'post_type'        => TNCT::TIME_TRACK,
				'post_author'      => get_current_user_id(),
				'orderby'          => 'date',
				'order'            => 'ASC',
				'posts_per_page'   => 1,
				'suppress_filters' => 1,
			]
		);

		return $posts ? $posts[0] : null;
	}

	public function start_tracking() {
		$track = $this->get_tracking();

		if ( ! $track ) {
			$post_id = wp_insert_post(
				[
					'post_status'  => 'pending',
					'post_type'    => TNCT::TIME_TRACK,
					'post_author'  => get_current_user_id(),
					'post_date'    => current_time( 'mysql' ),
					'post_title'   => '',
					'post_content' => '',
				]
			);

			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			} else {
				$track = get_post( $post_id );
			}
		}

		$begin = get_post_meta( $track->ID, TNCT::DATE_BEGIN, true );
		if ( ! $begin ) {
			update_post_meta( $track->ID, TNCT::DATE_BEGIN, time() );
		}

		return $track;
	}

	public function stop_tracking() {
		$track = $this->get_tracking();

		if ( ! $track ) {
			return new WP_Error( 'tracking_error', '트래킹 종료 에러. 생성된 트래킹이 없음.' );
		}

		$track->post_status = 'publish';

		wp_update_post( $track );

		update_post_meta( $track->ID, TNCT::DATE_END, time() );

		return $track;
	}

	public function update_tracking( array $data = [] ) {
		$track = $this->get_tracking();

		if ( $track ) {
			$default = static::get_default_data();
			$data    = array_intersect_key( wp_parse_args( $data, $default ), $default );

			$track->post_title = sanitize_text_field( $data['track_title'] ?? '' );
			wp_update_post( $track );

			$project_slug = sanitize_key( $data['project_slug'] ?? '' );
			if ( $project_slug ) {
				wp_set_object_terms( $track->ID, $project_slug, TNCT::PROJECT_TAG, false );
			} else {
				wp_delete_object_term_relationships( $track->ID, TNCT::PROJECT_TAG );
			}
		}

		return $track;
	}

	public function time_track_checkpoint() {
		check_ajax_referer( 'tn-time-track-check', 'nonce' );

		switch ( $_REQUEST['checkpoint'] ?? '' ) {
			case 'get':
				$this->time_track_checkpoint__get();
				break;

			case 'start':
				$this->time_track_checkpoint__start();
				break;

			case 'stop':
				$this->time_track_checkpoint__stop();
				break;

			case 'update':
				$this->time_track_checkpoint__update();
				break;
		}
	}

	protected function time_track_checkpoint__get() {
		$tracking = $this->get_tracking();

		if ( is_wp_error( $tracking ) ) {
			wp_send_json_error( $tracking );
		} elseif ( ! $tracking ) {
			wp_send_json_success( [ 'track_id' => null ] );
		} else {
			wp_send_json_success( static::post_to_data( $tracking ) );
		}
	}

	protected function time_track_checkpoint__start() {
		$tracking = $this->start_tracking();

		if ( is_wp_error( $tracking ) ) {
			wp_send_json_error( $tracking );
		} else {
			wp_send_json_success( static::post_to_data( $tracking ) );
		}
	}

	protected function time_track_checkpoint__stop() {
		$tracking = $this->stop_tracking();

		if ( is_wp_error( $tracking ) ) {
			wp_send_json_error( $tracking );
		} else {
			wp_send_json_success( static::post_to_data( $tracking ) );
		}
	}

	protected function time_track_checkpoint__update() {
		$tracking = $this->update_tracking( $_REQUEST );

		if ( is_wp_error( $tracking ) ) {
			wp_send_json_error( $tracking );
		} else {
			wp_send_json_success( static::post_to_data( $tracking ) );
		}
	}

	public static function get_default_data() {
		return [
			'track_id'     => null,
			'track_title'  => '',
			'project_slug' => '',
			'track_begin'  => null,
		];
	}

	public static function post_to_data( WP_Post $post ): array {
		$data = self::get_default_data();

		$terms = wp_get_post_terms( $post->ID, TNCT::PROJECT_TAG, [ 'fields' => 'id=>slug' ] );
		if ( is_array( $terms ) && sizeof( $terms ) ) {
			reset( $terms );
			$slug = current( $terms );
		} else {
			$slug = '';
		}

		$data['track_id']     = $post->ID;
		$data['track_title']  = $post->post_title;
		$data['project_slug'] = $slug;
		$data['track_begin']  = get_post_meta( $post->ID, TNCT::DATE_BEGIN, true );

		return $data;
	}
}
