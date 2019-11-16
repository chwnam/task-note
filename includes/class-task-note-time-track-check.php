<?php

class Task_Note_Time_Track_Check {
	public function __construct() {
		add_action( 'wp_loaded', [ $this, 'time_track' ] );
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

		tn_template(
			'time-track.php',
			[
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
	}
}
