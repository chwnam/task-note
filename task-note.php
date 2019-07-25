<?php
/**
 * Plugin Name: 업무 일지 플러그인
 * Description: 실험적인 업무 일지 플러그인
 * Version:     0.0.0 (실험 버전)
 * Author:      남창우
 * Author URI:  mailto://cs.chwnam@gmail.com
 * Plugin URI:  https://github.com/chwnam/task-note/
 */

define( 'TASK_NOTE_VERSION', '0.0.0' );
define( 'TASK_NOTE_MAIN', __FILE__ );

function task_note_callback( $atts ) {
	if ( ! is_user_logged_in() ) {
		return sprintf(
			'<a href="%s">로그인</a>하셔야 사용할 수 있습니다.',
			esc_url( wp_login_url( $_SERVER['REQUEST_URI'] ?? '' ) )
		);
	}

	ob_start();

	echo "<div id='task-note'></div>";

	wp_enqueue_script(
		'task-note',
		plugins_url( 'dist/task-note/index.js', TASK_NOTE_MAIN ),
		[
			'jquery',
			'moment',
			'react',
			'react-dom',
			'underscore',
			'wp-api',
		],
		TASK_NOTE_VERSION,
		true
	);

	return ob_get_clean();
}

add_shortcode( 'task_note', 'task_note_callback' );

function task_note_register_custom_post_types() {
	register_post_type(
		'task_note',
		[
			'labels'                => [
				'name'                  => _X( '업무 노트', 'register_post_type() argument', 'task_note' ),
				'singular_name'         => _X( '업무 노트', 'register_post_type() argument', 'task_note' ),
				'add_new'               => _X( '새로 추가', 'register_post_type() argument', 'task_note' ),
				'add_new_item'          => _X( '새 업무 노트 추가', 'register_post_type() argument', 'task_note' ),
				'edit_item'             => _X( '업무 노트 보기', 'register_post_type() argument', 'task_note' ),
				'new_item'              => _X( '새 업무 노트', 'register_post_type() argument', 'task_note' ),
				'view_item'             => _X( '업무 노트 보기', 'register_post_type() argument', 'task_note' ),
				'view_items'            => _X( '업무 노트 보기', 'register_post_type() argument', 'task_note' ),
				'search_items'          => _X( '노트 검색', 'register_post_type() argument', 'task_note' ),
				'not_found'             => _X( '찾을 수 없음', 'register_post_type() argument', 'task_note' ),
				'not_found_in_trash'    => _X( '휴지통에서 찾을 수 없음', 'register_post_type() argument', 'task_note' ),
				'parent_item_colon'     => _X( '상위 업무 노트:', 'register_post_type() argument', 'task_note' ),
				'all_items'             => _X( '모든 업무 노트', 'register_post_type() argument', 'task_note' ),
				'archives'              => _X( '업무 노트 아카이브', 'register_post_type() argument', 'task_note' ),
				'attributes'            => _X( '업무 노트 속성들', 'register_post_type() argument', 'task_note' ),
				// 'insert_into_item'         => _X( '', 'register_post_type() argument', 'task_note' ),
				// 'upload_to_this_item'      => _X( '', 'register_post_type() argument', 'task_note' ),
				// 'featured_image'           => _X( '', 'register_post_type() argument', 'task_note' ),
				// 'set_featured_image'       => _X( '', 'register_post_type() argument', 'task_note' ),
				// 'remove_featured_image'    => _X( '', 'register_post_type() argument', 'task_note' ),
				// 'use_featured_image'       => _X( '', 'register_post_type() argument', 'task_note' ),
				'menu_name'             => _X( '업무 노트', 'register_post_type() argument', 'task_note' ),
				'filter_items_list'     => _X( '업무 노트 필터', 'register_post_type() argument', 'task_note' ),
				'items_list_navigation' => _X( '발신 목록 탐색', 'register_post_type() argument', 'task_note' ),
				'items_list'            => _X( '업무 노트 목록', 'register_post_type() argument', 'task_note' ),
				// 'name_admin_bar'           => _X( '', 'register_post_type() argument', 'task_note' ),
				'item_published'        => _X( '업무 노트 발행됨', 'register_post_type() argument', 'task_note' ),
				// 'item_published_privately' => _X( '', 'register_post_type() argument', 'task_note' ),
				// 'item_reverted_to_draft'   => _X( '', 'register_post_type() argument', 'task_note' ),
				// 'item_scheduled'           => _X( '', 'register_post_type() argument', 'task_note' ),
				// 'item_updated'             => _X( '', 'register_post_type() argument', 'task_note' ),
			],
			'description'           => '업무 노트',
			'public'                => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_nav_menus'     => false,
			'show_in_menu'          => true,
			'show_in_admin_bar'     => true,
			'menu_position'         => null,
			'menu_icon'             => null,
			'capability_type'       => [ 'post', 'posts' ],
			'capabilities'          => [
				// Meta capabilities
				//				'edit_post'              => 'edit_task_note_msg_send',
				//				'read_post'              => 'read_task_note_msg_send',
				//				'delete_post'            => 'delete_task_note_msg_send',
				//
				//				// Primitive capabilities used outside of map_meta_cap():
				//				'edit_posts'             => 'edit_task_note_msg_sends',
				//				'edit_others_posts'      => 'edit_others_task_note_msg_sends',
				//				'publish_posts'          => 'publish_task_note_msg_sends',
				//				'read_private_posts'     => 'read_private_task_note_msg_sends',
				//
				//				// Primitive capabilities used within map_meta_cap():
				//				'delete_posts'           => 'delete_task_note_msg_sends',
				//				'delete_private_posts'   => 'delete_private_task_note_msg_sends',
				//				'delete_published_posts' => 'delete_published_task_note_msg_sends',
				//				'delete_others_posts'    => 'delete_others_task_note_msg_sends',
				//				'edit_private_posts'     => 'edit_private_task_note_msg_sends',
				//				'edit_published_posts'   => 'edit_published_task_note_msg_sends',
				//				'create_posts'           => 'create_task_note_msg_sends',
			],
			'map_meta_cap'          => true,
			'hierarchical'          => false,
			'supports'              => [ 'title', 'editor' ],
			'register_meta_box_cb'  => null,
			'taxonomies'            => [],
			'has_archive'           => true,
			'rewrite'               => [
				'slug'       => 'task-note',
				'with_front' => true,
				'feeds'      => true,
				'pages'      => true,
				'ep_mask'    => EP_PERMALINK,
			],
			'permalink_epmask'      => EP_PERMALINK,
			'query_var'             => 'task-note',
			'can_export'            => true,
			'delete_with_user'      => true,
			'show_in_rest'          => true,
			'rest_base'             => 'task-note',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		]
	);
}

add_action( 'init', 'task_note_register_custom_post_types' );

function task_note_prepare_custom_types() {
	task_note_register_custom_post_types();
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'task_note_prepare_custom_types' );

function task_note_remove_custom_types() {
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'task_note_remove_custom_types' );