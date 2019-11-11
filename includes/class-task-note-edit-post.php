<?php

class Task_Note_Edit_Post {
	public function __construct() {
		add_action( 'init', [ $this, 'register_plugins' ] );
		add_action( 'current_screen', [ $this, 'current_screen' ] );
	}

	public function current_screen( WP_Screen $screen ): void {
		if ( 'task_note' === $screen->post_type && 'post' === $screen->base ) {
			add_action( 'enqueue_block_editor_assets', [ $this, 'add_plugins' ] );
		}
	}

	public function register_plugins(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		/**
		 * @noinspection PhpIncludeInspection
		 * @var array $deps
		 */
		$deps = include_once plugin_dir_path( TASK_NOTE_MAIN ) . 'build/index.asset.php';

		wp_register_script(
			'task-note-date',
			plugins_url( 'build/index.js', TASK_NOTE_MAIN ),
			array_merge(
				[
					'wp-components',
					'wp-compose',
					'wp-data',
					'wp-date',
					'wp-edit-post',
					'wp-plugins',
				],
				$deps['dependencies']
			),
			$deps['version']
		);
	}

	public function add_plugins(): void {
		wp_enqueue_script( 'task-note-date' );
	}
}
