<?php
/**
 * Plugin Name: 업무 노트
 * Description: 업무 일지를 매일매일 작성합시다. 도움이 됩니다.
 * Version:     0.1.0-beta.4
 * Author:      남창우
 * Author URI:  https://blog.changwoo.pe.kr
 * Plugin URI:  https://github.com/chwnam/task-note.git
 * License:     GPLv2 or later
 */

define( 'TASK_NOTE_MAIN', __FILE__ );
define( 'TASK_NOTE_VERSION', '0.1.0-beta.4' );

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
				'Task_Note_Admin_Task_Note'  => $dir . '/class-task-note-admin-task-note.php',
				'Task_Note_Admin_Time_Track' => $dir . '/class-task-note-admin-time-track.php',
				'Task_Note_Custom_Types'     => $dir . '/class-task-note-custom-types.php',
				'Task_Note_Scripts'          => $dir . '/class-task-note-scripts.php',
				'Task_Note_Time_Track_Check' => $dir . '/class-task-note-time-track-check.php',
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
		class_alias( 'Task_Note_Custom_Types', 'TNCT');

		$this->modules['custom_types']     = new Task_Note_Custom_Types();
		$this->modules['edit_task_note']   = new Task_Note_Admin_Task_Note();
		$this->modules['edit_time_track']  = new Task_Note_Admin_Time_Track();
		$this->modules['scripts']          = new Task_Note_Scripts();
		$this->modules['time_track_check'] = new Task_Note_Time_Track_Check();
	}
}

Task_Note::get_instance();
