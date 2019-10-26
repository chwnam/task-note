<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Task_Note_Custom_Types {
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_type' ] );

		register_activation_hook( TASK_NOTE_MAIN, [ $this, 'activation_callback' ] );
		register_deactivation_hook( TASK_NOTE_MAIN, [ $this, 'deactivation_callback' ] );
	}

	public function activation_callback() {
		$this->register_post_type();
		flush_rewrite_rules();
	}

	public function deactivation_callback() {
		flush_rewrite_rules();
	}

	public function register_post_type() {
		register_post_type(
			'task_note',
			[
				'labels'              => [
					'name'                     => '업무일지들',
					'singular_name'            => '업무일지',
					'add_new'                  => '새로 작성',
					'add_new_item'             => '새 업무일지 작성',
					'new_item'                 => '새 업무일지',
					'view_item'                => '업무일지 보기',
					'view_items'               => '업무일지 보기',
					'edit_item'                => '업무일지 수정',
					'search_items'             => '업무일지 검색',
					'not_found'                => '업무일지 찾을 수 없음.',
					'not_found_in_trash'       => '휴지통에서 업무일지 찾을 수 없음.',
					'all_items'                => '모든 업무일지',
					'archives'                 => '업무일지 목록',
					'attributes'               => '업무일지 속성',
					'insert_into_item'         => '업무일지에 삽입',
					'uploaded_to_this_item'    => '이 업무일지로 업로드',
					'menu_name'                => '업무일지',
					'filter_items_list'        => '업무일지 목록 필터',
					'items_list_navigation'    => '업무일지 목록 탐색',
					'items_list'               => '업무일지 목록',
					'name_admin_bar'           => '업무일지',
					'item_published'           => '업무일지 발행됨',
					'item_published_privately' => '업무일지가 비공개로 발행됨',
					'item_reverted_to_draft'   => '업무일지 임시글로 변경됨',
					'item_scheduled'           => '업무일지 발행 예약됨',
					'item_updated'             => '업무일지 업데이트됨.',
				],
				'description'         => '업무일지 포스트 타입',
				'public'              => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_nav_menus'   => false,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-text-page',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'editor' ],
				'has_archive'         => false,
				'rewrite'             => [
					'slug'       => 'task-note',
					'with_front' => true,
					'feeds'      => false,
					'pages'      => false,
					'ep_mask'    => EP_PERMALINK,
				],
				'can_export'          => true,
				'delete_with_user'    => false,
				'show_in_rest'        => true,
			]
		);
	}
}
