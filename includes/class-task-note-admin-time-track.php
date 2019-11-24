<?php

class Task_Note_Admin_Time_Track {
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'] );
			add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
			add_action( 'current_screen', [ $this, 'current_screen' ] );
			add_action( 'save_post_time_track', [ $this, 'save_post' ], 10, 3 );
		}
	}

	public function pre_get_posts( WP_Query &$query ) {
		if ( $query->is_main_query() && $query->is_post_type_archive( TNCT::TIME_TRACK ) && isset( $_GET['orderby'] ) ) {
			remove_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );

			switch ( $_GET['orderby'] ?? '' ) {
				case 'beg':
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'order', $_GET['order'] ?? 'desc' );
					$query->set( 'meta_key', TNCT::DATE_BEGIN );
					break;

				case 'end':
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'order', $_GET['order'] ?? 'desc' );
					$query->set( 'meta_key', TNCT::DATE_END );
					break;
			}
		}
	}

	public function current_screen( WP_Screen $screen ): void {
		if ( 'time_track' !== $screen->post_type ) {
			return;
		}

		switch ( $screen->base ) {
			case 'edit':
				add_filter( 'manage_' . TNCT::TIME_TRACK . '_posts_columns', [ $this, 'custom_columns' ] );
				add_filter( 'manage_' . $screen->id . '_sortable_columns', [ $this, 'sortable_columns' ] );
				add_action(
					'manage_' . TNCT::TIME_TRACK . '_posts_custom_column',
					[ $this, 'custom_column_data' ],
					10,
					2
				);
				break;

			case 'post':
				add_action( 'add_meta_boxes_time_track', [ $this, 'add_meta_boxes' ] );
				break;
		}
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'time-track',
			'작업 시간',
			[ $this, 'output_time_track_meta_box' ],
			null,
			'normal',
			'default'
		);
	}

	public function output_time_track_meta_box( WP_Post $post ): void {
		$year         = '';
		$month        = '';
		$day          = '';
		$dow          = '';
		$begin_hour   = '';
		$begin_minute = '';
		$end_hour     = '';
		$end_minute   = '';
		$timespan     = 0;

		$beg = absint( get_post_meta( $post->ID, Task_Note_Custom_Types::DATE_BEGIN, true ) );
		$end = absint( get_post_meta( $post->ID, Task_Note_Custom_Types::DATE_END, true ) );
		$tz  = wp_timezone();

		$begin_datetime = null;
		if ( $beg > 0 ) {
			$begin_datetime = DateTime::createFromFormat( 'U', $beg );
			if ( $begin_datetime ) {
				$begin_datetime->setTimezone( $tz );
				$year         = $begin_datetime->format( 'Y' );
				$month        = $begin_datetime->format( 'm' );
				$day          = $begin_datetime->format( 'd' );
				$dow          = date_i18n( 'D', $begin_datetime->getTimestamp() );
				$begin_hour   = $begin_datetime->format( 'H' );
				$begin_minute = $begin_datetime->format( 'i' );
			}
		}

		$end_datetime = null;
		if ( $end > 0 ) {
			$end_datetime = DateTime::createFromFormat( 'U', $end );
			if ( $end_datetime ) {
				$end_datetime->setTimezone( $tz );
				$end_hour   = $end_datetime->format( 'H' );
				$end_minute = $end_datetime->format( 'i' );
			}
		}

		if ( $begin_datetime && $end_datetime ) {
			$timespan = $end_datetime->getTimestamp() - $begin_datetime->getTimestamp();
		}

		tn_template(
			'admin/meta-boxes/time-track-properties.php',
			[
				'year'         => $year,
				'month'        => $month,
				'day'          => $day,
				'dow'          => $dow,
				'begin_hour'   => $begin_hour,
				'begin_minute' => $begin_minute,
				'end_hour'     => $end_hour,
				'end_minute'   => $end_minute,
				'timespan'     => $timespan,
			]
		);

		wp_enqueue_script( 'tn-time-track-properties' );
		wp_enqueue_style( 'tn-time-track-properties' );
	}

	public function custom_columns( array $columns ): array {
		$idx = array_search( 'date', array_keys( $columns ) );

		if ( false !== $idx ) {
			$left  = array_slice( $columns, 0, $idx, true );
			$right = array_slice( $columns, $idx, null, true );

			$left['date_begin'] = '시작일';
			$left['date_end']   = '종료일';
			$left['timespan']   = '작업시간';

			$columns = array_merge( $left, $right );
		}

		return $columns;
	}

	public function sortable_columns( array $columns ): array {
		$columns['date_begin'] = [ 'beg', true ];
		$columns['date_end']   = [ 'end', true ];

		return $columns;
	}

	public function custom_column_data( string $column_name, int $post_id ): void {
		switch ( $column_name ) {
			case 'date_begin':
				$this->print_datetime( get_post_meta( $post_id, TNCT::DATE_BEGIN, true ) );
				break;

			case 'date_end':
				$this->print_datetime( get_post_meta( $post_id, TNCT::DATE_END, true ) );
				break;

			case 'timespan':
				$end = get_post_meta( $post_id, TNCT::DATE_END, true );
				$beg = get_post_meta( $post_id, TNCT::DATE_BEGIN, true );
				if ( $beg && $end && $end > $beg ) {
					echo format_timespan( $end - $beg );
				}
				break;
		}
	}

	public function save_post( int $post_id, WP_Post $post, bool $updated ) {
		if (
			( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
			'trash' === $post->post_status ||
			TNCT::TIME_TRACK !== $post->post_type
		) {
			return;
		}

		if ( ! $updated ) {
			add_post_meta( $post_id, TNCT::DATE_BEGIN, 0, true );
			add_post_meta( $post_id, TNCT::DATE_END, 0, true );
		} elseif (
			isset( $_REQUEST['time_track_date'] ) &&
			isset( $_REQUEST['time_track_begin'] ) &&
			isset( $_REQUEST['time_track_end'] )
		) {
			check_admin_referer( 'time_track_properties', 'time_track_nonce' );

			$date = $_REQUEST['time_track_date'] ?? [];

			$year  = sprintf( '%04d', intval( $date['year'] ?? 0 ) );
			$month = sprintf( '%02d', intval( $date['month'] ?? 0 ) );
			$day   = sprintf( '%02d', intval( $date['day'] ?? 0 ) );

			$begin        = $_REQUEST['time_track_begin'] ?? [];
			$begin_hour   = sprintf( '%02d', intval( $begin['hour'] ?? 0 ) );
			$begin_minute = sprintf( '%02d', intval( $begin['minute'] ?? 0 ) );

			$end        = $_REQUEST['time_track_end'] ?? [];
			$end_hour   = sprintf( '%02d', intval( $end['hour'] ?? 0 ) );
			$end_minute = sprintf( '%02d', intval( $end['minute'] ?? 0 ) );

			$begin_datetime = DateTime::createFromFormat(
				'Y-m-d H:i:s',
				"{$year}-{$month}-{$day} {$begin_hour}:{$begin_minute}:00",
				wp_timezone()
			);

			if ( $begin_datetime ) {
				update_post_meta( $post_id, Task_Note_Custom_Types::DATE_BEGIN, $begin_datetime->getTimestamp() );
			}

			$end_datetime = DateTime::createFromFormat(
				'Y-m-d H:i:s',
				"{$year}-{$month}-{$day} {$end_hour}:{$end_minute}:00",
				wp_timezone()
			);

			if ( $end_datetime ) {
				update_post_meta( $post_id, Task_Note_Custom_Types::DATE_END, $end_datetime->getTimestamp() );
			}
		}
	}

	private function print_datetime( $timestamp ) {
		printf(
			'<time datetime="%1$s %2$s">%1$s<br>%2$s</time>',
			format_datetime( $timestamp, 'Y-m-d' ),
			format_datetime( $timestamp, 'H:i:s' )
		);
	}
}
