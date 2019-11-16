<?php

class Task_Note_Scripts {
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		}
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
	}

	public function wp_enqueue_scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$scripts = [
			[
				'handle'    => 'tn-time-track-check',
				'src'       => plugins_url( 'assets/js/time-track-check.js', TASK_NOTE_MAIN ),
				'deps'      => [ 'jquery' ],
				'ver'       => TASK_NOTE_VERSION,
				'in_footer' => true,
			]
		];

		$styles = [
			[
				'handle' => 'tn-time-track-check',
				'src'    => plugins_url( 'assets/css/time-track-check.css', TASK_NOTE_MAIN ),
				'deps'   => [],
				'ver'    => TASK_NOTE_VERSION,
			]
		];

		$this->register_scripts_styles( $scripts, $styles );
	}

	public function admin_enqueue_scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$scripts = [
			[
				'handle'    => 'tn-time-track-properties',
				'src'       => plugins_url( 'assets/js/time-track-properties.js', TASK_NOTE_MAIN ),
				'deps'      => [ 'jquery' ],
				'ver'       => TASK_NOTE_VERSION,
				'in_footer' => true,
			],
			[
				'handle'    => 'tn-time-track-check',
				'src'       => plugins_url( 'assets/js/time-track-check.js', TASK_NOTE_MAIN ),
				'deps'      => [ 'jquery' ],
				'ver'       => TASK_NOTE_VERSION,
				'in_footer' => true,
			]
		];

		$styles = [
			[
				'handle' => 'tn-time-track-check',
				'src'    => plugins_url( 'assets/css/time-track-check.css', TASK_NOTE_MAIN ),
				'deps'   => [],
				'ver'    => TASK_NOTE_VERSION,
			]
		];

		$this->register_scripts_styles( $scripts, $styles );
	}

	protected function register_scripts_styles( array $scripts, array $styles ): void {
		foreach ( $scripts as $script ) {
			wp_register_script(
				$script['handle'],
				$script['src'],
				$script['deps'] ?? [],
				$script['ver'] ?? false,
				$script['in_footer'] ?? false
			);
		}

		foreach ( $styles as $style ) {
			wp_register_style(
				$style['handle'],
				$style['src'],
				$style['deps'] ?? [],
				$style['ver'] ?? false,
				$style['media'] ?? 'all'
			);
		}
	}
}
