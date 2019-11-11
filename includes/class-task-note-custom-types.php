<?php

/**
 * Class Task_Note_Custom_Types
 */
class Task_Note_Custom_Types {
	public function __construct() {
		add_action( 'init', [ $this, 'init_callback' ] );

		register_activation_hook( TASK_NOTE_MAIN, [ $this, 'activation_callback' ] );
		register_deactivation_hook( TASK_NOTE_MAIN, [ $this, 'deactivation_callback' ] );

		if ( ! is_admin() ) {
			add_filter( 'posts_pre_query', [ $this, 'restrict_to_frontend' ], 10, 2 );
		} else {
			add_filter( 'enter_title_here', [ $this, 'modify_editor_title' ], 10, 2 );
		}
	}

	public function activation_callback(): void {
		$this->init_callback();
		flush_rewrite_rules();
	}

	public function deactivation_callback(): void {
		flush_rewrite_rules();
	}

	public function init_callback(): void {
		$this->register_post_type();
		$this->register_taxonomy();
	}

	public function register_post_type(): void {
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
				'public'              => true,
				'exclude_from_search' => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-text-page',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'editor' ],
				'taxonomies'          => [ 'project_tag' ],
				'has_archive'         => true,
				'rewrite'             => [
					'slug'       => 'task-note',
					'with_front' => true,
					'feeds'      => false,
					'pages'      => false,
					'ep_mask'    => EP_PERMALINK,
				],
				'query_var'           => 'task-note',
				'can_export'          => true,
				'delete_with_user'    => false,
				'show_in_rest'        => true,
			]
		);
	}

	public function register_taxonomy(): void {
		register_taxonomy(
			'project_tag',
			[ 'task_note' ],
			[
				'labels'             => [
					'name'                       => '프로젝트 태그',
					'singular_name'              => '프로젝트 태그',
					'menu_name'                  => '프로젝트 태그',
					'all_items'                  => '모든 프로젝트',
					'edit_item'                  => '프로젝트 수정',
					'view_item'                  => '프로젝트 보기',
					'update_item'                => '프로젝트 갱신',
					'add_new_item'               => '새 프로젝트 추가',
					'new_item_name'              => '새 프로젝트 이름',
					'search_items'               => '프로젝트 검색',
					'popular_items'              => '자주 태그된 프로젝트들',
					'separate_items_with_commas' => '쉼표로 프로젝트를 구분',
					'add_or_remove_items'        => '프로젝트 추가 혹은 삭제',
					'choose_from_most_used'      => '가장 많이 사용된 프로젝트에서 선택',
					'not_found'                  => '찾을 수 없음',
					'back_to_items'              => '목록으로 돌하가기',
				],
				'public'             => true,
				'publicly_queryable' => true,
				'show_admin_column'  => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => false,
				'meta_box_cb'        => null,
				'show_in_rest'       => true,
				'description'        => '노트에 프로젝트 태그를 붙여 그 날 어떤 프로젝트를 진행했는지 기록합니다.',
				'hierarchical'       => false,
				'query_var'          => 'project-tag',
				'rewrite'            => [
					'slug'         => 'project-tag',
					'with_front'   => true,
					'hierarchical' => false,
					'ep_mask'      => EP_NONE,
				],
			]
		);
	}

	public function restrict_to_frontend( ?array $posts, WP_Query $query ): ?array {
		if ( $query->is_main_query() && 'task_note' === $query->get( 'post_type' ) ) {
			remove_filter( 'posts_pre_query', [ $this, 'restrict_to_frontend' ] );
			if ( ! current_user_can( 'administrator' ) ) {
				$posts = [];
			}
		}

		return $posts;
	}

	public function modify_editor_title( string $title, WP_Post $post ): string {
		if ( 'task_note' === $post->post_type ) {
			$day   = date_i18n( 'D', time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
			$title = current_time( "Y년 m월 d일 ({$day}) 업무일지" );
		}

		return $title;
	}
}
