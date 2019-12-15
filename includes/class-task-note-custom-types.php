<?php

/**
 * Class Task_Note_Custom_Types
 */
class Task_Note_Custom_Types {
	const TASK_NOTE   = 'task_note';
	const TIME_TRACK  = 'time_track';
	const TASK_RECIPE = 'task_recipe';

	const PROJECT_TAG = 'project_tag';
	const RECIPE_TAG  = 'recipe_tag';

	const DATE_BEGIN = 'tn_time_track_date_begin';
	const DATE_END   = 'tn_time_track_date_end';
	const ESTIMATED  = 'tn_time_track_estimated';

	public function __construct() {
		add_action( 'init', [ $this, 'init_callback' ] );

		register_activation_hook( TASK_NOTE_MAIN, [ $this, 'activation_callback' ] );
		register_deactivation_hook( TASK_NOTE_MAIN, [ $this, 'deactivation_callback' ] );

		if ( is_admin() ) {
			add_filter( 'enter_title_here', [ $this, 'modify_editor_title' ], 10, 2 );
			add_filter( 'use_block_editor_for_post_type', [ $this, 'is_block_editor_used' ], 10, 2 );
		} else {
			add_filter( 'posts_pre_query', [ $this, 'restrict_to_frontend' ], 10, 2 );
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
		$this->register_meta_fields();
	}

	public function register_post_type(): void {
		register_post_type(
			self::TASK_NOTE,
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
				'taxonomies'          => [ self::PROJECT_TAG ],
				'has_archive'         => true,
				'rewrite'             => [
					'slug'       => 'task-note',
					'with_front' => true,
					'feeds'      => false,
					'pages'      => true,
					'ep_mask'    => EP_PERMALINK,
				],
				'query_var'           => 'task-note',
				'can_export'          => true,
				'delete_with_user'    => false,
				'show_in_rest'        => true,
			]
		);

		register_post_Type(
			self::TIME_TRACK,
			[
				'labels'              => [
					'name'                     => '시간추적들',
					'singular_name'            => '시간추적',
					'add_new'                  => '새로 작성',
					'add_new_item'             => '새 시간추적 작성',
					'new_item'                 => '새 시간추적',
					'view_item'                => '시간추적 보기',
					'view_items'               => '시간추적 보기',
					'edit_item'                => '시간추적 수정',
					'search_items'             => '시간추적 검색',
					'not_found'                => '시간추적 찾을 수 없음.',
					'not_found_in_trash'       => '휴지통에서 시간추적 찾을 수 없음.',
					'all_items'                => '모든 시간추적',
					'archives'                 => '시간추적 목록',
					'attributes'               => '시간추적 속성',
					'insert_into_item'         => '시간추적에 삽입',
					'uploaded_to_this_item'    => '이 시간추적로 업로드',
					'menu_name'                => '시간추적',
					'filter_items_list'        => '시간추적 목록 필터',
					'items_list_navigation'    => '시간추적 목록 탐색',
					'items_list'               => '시간추적 목록',
					'name_admin_bar'           => '시간추적',
					'item_published'           => '시간추적 발행됨',
					'item_published_privately' => '시간추적가 비공개로 발행됨',
					'item_reverted_to_draft'   => '시간추적 임시글로 변경됨',
					'item_scheduled'           => '시간추적 발행 예약됨',
					'item_updated'             => '시간추적 업데이트됨.',
				],
				'description'         => '시간추적 포스트 타입',
				'public'              => true,
				'exclude_from_search' => true,
				'menu_position'       => 6,
				'menu_icon'           => 'dashicons-clock',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'editor' ],
				'taxonomies'          => [ self::PROJECT_TAG ],
				'has_archive'         => true,
				'rewrite'             => [
					'slug'       => 'time-track',
					'with_front' => true,
					'feeds'      => false,
					'pages'      => true,
					'ep_mask'    => EP_PERMALINK,
				],
				'query_var'           => 'time-track',
				'can_export'          => true,
				'delete_with_user'    => false,
				'show_in_rest'        => true,
			]
		);

		register_post_type(
			self::TASK_RECIPE,
			[
				'labels'              => [
					'name'                     => '업무 레시피들',
					'singular_name'            => '업무 레시피',
					'add_new'                  => '새로 작성',
					'add_new_item'             => '새 레시피 작성',
					'new_item'                 => '새 레시피',
					'view_item'                => '레시피 보기',
					'view_items'               => '레시피 보기',
					'edit_item'                => '레시피 수정',
					'search_items'             => '레시피 검색',
					'not_found'                => '레시피 찾을 수 없음.',
					'not_found_in_trash'       => '휴지통에서 레시피 찾을 수 없음.',
					'all_items'                => '모든 레시피',
					'archives'                 => '레시피 목록',
					'attributes'               => '레시피 속성',
					'insert_into_item'         => '레시피에 삽입',
					'uploaded_to_this_item'    => '이 레시피로 업로드',
					'menu_name'                => '업무 레시피',
					'filter_items_list'        => '레시피 목록 필터',
					'items_list_navigation'    => '레시피 목록 탐색',
					'items_list'               => '레시피 목록',
					'name_admin_bar'           => '레시피',
					'item_published'           => '레시피 발행됨',
					'item_published_privately' => '레시피가 비공개로 발행됨',
					'item_reverted_to_draft'   => '레시피 임시글로 변경됨',
					'item_scheduled'           => '레시피 발행 예약됨',
					'item_updated'             => '레시피 업데이트됨.',
				],
				'description'         => '레시피 포스트 타입',
				'public'              => true,
				'exclude_from_search' => false,
				'menu_position'       => 7,
				'menu_icon'           => 'dashicons-book-alt',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'editor' ],
				'taxonomies'          => [ self::RECIPE_TAG ],
				'has_archive'         => true,
				'rewrite'             => [
					'slug'       => 'recipe',
					'with_front' => true,
					'feeds'      => false,
					'pages'      => true,
					'ep_mask'    => EP_PERMALINK,
				],
				'query_var'           => 'recipe',
				'can_export'          => true,
				'delete_with_user'    => false,
				'show_in_rest'        => true,
			]
		);
	}

	public function register_taxonomy(): void {
		register_taxonomy(
			self::PROJECT_TAG,
			[ self::TASK_NOTE, self::TIME_TRACK ],
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
				'show_in_nav_menus'  => true,
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

		register_taxonomy(
			self::RECIPE_TAG,
			[ self::TASK_RECIPE ],
			[
				'labels'             => [
					'name'                       => '레시피 태그',
					'singular_name'              => '레시피 태그',
					'menu_name'                  => '레시피 태그',
					'all_items'                  => '모든 레시피',
					'edit_item'                  => '레시피 수정',
					'view_item'                  => '레시피 보기',
					'update_item'                => '레시피 갱신',
					'add_new_item'               => '새 레시피 추가',
					'new_item_name'              => '새 레시피 이름',
					'search_items'               => '레시피 검색',
					'popular_items'              => '자주 태그된 레시피들',
					'separate_items_with_commas' => '쉼표로 레시피를 구분',
					'add_or_remove_items'        => '레시피 추가 혹은 삭제',
					'choose_from_most_used'      => '가장 많이 사용된 레시피에서 선택',
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
				'description'        => '레시피 태그',
				'hierarchical'       => false,
				'query_var'          => 'recipe-tag',
				'rewrite'            => [
					'slug'         => 'recipe-tag',
					'with_front'   => true,
					'hierarchical' => false,
					'ep_mask'      => EP_NONE,
				],
			]
		);
	}

	public function register_meta_fields(): void {
		/** @uses Task_Note_Custom_Types::sanitize_datetime() */
		register_meta(
			'post',
			self::DATE_BEGIN,
			[
				'object_subtype'    => self::TIME_TRACK,
				'type'              => 'int',
				'description'       => '시작 시간의 타임스탬프',
				'single'            => true,
				'sanitize_callback' => [ $this, 'sanitize_datetime' ],
				'auth_callback'     => null,
				'show_in_rest'      => false,
			]
		);

		/** @uses Task_Note_Custom_Types::sanitize_datetime() */
		register_meta(
			'post',
			self::DATE_END,
			[
				'object_subtype'    => self::TIME_TRACK,
				'type'              => 'int',
				'description'       => '종료 시각의 타임스탬프',
				'single'            => true,
				'sanitize_callback' => [ $this, 'sanitize_datetime' ],
				'auth_callback'     => null,
				'show_in_rest'      => false,
			]
		);

		register_meta(
			'post',
			self::ESTIMATED,
			[
				'object_subtype'    => self::TIME_TRACK,
				'type'              => 'int',
				'description'       => '예상 작업 시간',
				'single'            => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => null,
				'show_in_rest'      => false,
			]
		);
	}

	public function restrict_to_frontend( ?array $posts, WP_Query $query ): ?array {
		if ( $query->is_main_query() && in_array( $query->get( 'post_type' ), [ 'task_note', 'time_track' ] ) ) {
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

	public function is_block_editor_used( bool $value, string $post_type ): bool {
		if ( 'time_track' === $post_type ) {
			$value = false;
		}

		return $value;
	}

	public function sanitize_datetime( $value ): int {
		$sanitized = false;

		if ( is_int( $value ) ) {
			$sanitized = $value;
		} elseif ( is_string( $value ) ) {
			if ( is_numeric( $value ) ) {
				$sanitized = absint( $value );
			} else {
				$datetime = DateTime::createFromFormat( 'Y-m-d', $value, wp_timezone() );
				if ( $datetime ) {
					$sanitized = $datetime->getTimestamp();
				}
			}
		} elseif ( is_array( $value ) && 3 === sizeof( $value ) ) {
			$datetime = DateTime::createFromFormat( 'Y-m-d', implode( '-', $value ), wp_timezone() );
			if ( $datetime ) {
				$sanitized = $datetime->getTimestamp();
			}
		}

		return intval( $sanitized );
	}
}
