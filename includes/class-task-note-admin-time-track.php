<?php

class Task_Note_Admin_Time_Track {
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'current_screen', [ $this, 'current_screen' ] );
			add_action( 'save_post_time_track', [ $this, 'save_post' ], 10, 3 );
		} else {

		}
	}

	public function current_screen( WP_Screen $screen ): void {
		if ( 'time_track' === $screen->post_type && 'post' === $screen->base ) {
			add_action( 'add_meta_boxes_time_track', [ $this, 'add_meta_boxes' ] );
		}
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'time-track',
			'시간추적',
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
		$timespan     = '';

		$beg = absint( get_post_meta( $post->ID, Task_Note_Custom_Types::DATE_BEGIN, true ) );
		$end = absint( get_post_meta( $post->ID, Task_Note_Custom_Types::DATE_END, true ) );
		$tz  = new DateTimeZone( 'Asia/Seoul' );

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

	public function save_post( int $post_id, WP_Post $post, bool $updated ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( $updated && $post && $post->post_status != 'trash' &&
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
				new DateTimeZone( 'Asia/Seoul' )
			);

			if ( $begin_datetime ) {
				update_post_meta( $post_id, Task_Note_Custom_Types::DATE_BEGIN, $begin_datetime->getTimestamp() );
			}

			$end_datetime = DateTime::createFromFormat(
				'Y-m-d H:i:s',
				"{$year}-{$month}-{$day} {$end_hour}:{$end_minute}:00",
				new DateTimeZone( 'Asia/Seoul' )
			);

			if ( $end_datetime ) {
				update_post_meta( $post_id, Task_Note_Custom_Types::DATE_END, $end_datetime->getTimestamp() );
			}
		}
	}
}
