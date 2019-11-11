<?php
/**
 * Plugin Name: 업무 노트
 * Description: 업무 일지를 매일매일 작성합시다. 도움이 됩니다.
 * Version:     0.0.4
 * Author:      남창우
 * Author URI:  https://blog.changwoo.pe.kr
 * Plugin URI:  https://github.com/chwnam/task-note.git
 * License:     GPLv2 or later
 */

define( 'TASK_NOTE_MAIN', __FILE__ );
define( 'TASK_NOTE_VERSION', '0.0.2' );

final class Task_Note {
	public $modules = [];

	public static function get_instance(): Task_Note {
		static $instance = null;
		if ( is_null( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Task_Note constructor.
	 *
	 * @uses Task_Note::handle_autoload()
	 */
	private function __construct() {
		require_once __DIR__ . '/includes/functions.php';
		try {
			spl_autoload_register( [ $this, 'handle_autoload' ] );
		} catch ( Exception $e ) {
			wp_die( $e->getMessage() );
		}

		$this->init_modules();
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function __wakeup() {
	}

	private function __clone() {
	}

	public function handle_autoload( string $class_name ): bool {
		static $class_map = null;

		if ( is_null( $class_map ) ) {
			$dir = dirname( TASK_NOTE_MAIN ) . '/includes';

			$class_map = [
				'Task_Note_Custom_Types' => $dir . '/class-task-note-custom-types.php',
				'Task_Note_Edit_Post'    => $dir . '/class-task-note-edit-post.php',
			];
		}

		if ( isset( $class_map[ $class_name ] ) ) {
			/** @noinspection PhpIncludeInspection */
			require $class_map[ $class_name ];

			return true;
		}

		return false;
	}

	public function init_modules() {
		$this->modules['custom_types'] = new Task_Note_Custom_Types();
		$this->modules['edit_post']    = new Task_Note_Edit_Post();
	}
}

Task_Note::get_instance();
