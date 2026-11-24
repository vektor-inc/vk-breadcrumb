<?php
use VektorInc\VK_Breadcrumb\VkBreadcrumb;
new VkBreadcrumb();

class VkBreadcrumbTest extends WP_UnitTestCase {

	function test_lightning_bread_crumb() {

		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'vk_bread_crumb' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print PHP_EOL;

		$before_option         = get_option( 'lightning_theme_options' );
		$before_page_for_posts = get_option( 'page_for_posts' ); // 投稿トップに指定するページ
		$before_page_on_front  = get_option( 'page_on_front' ); // フロントに指定する固定ページ
		$before_show_on_front  = get_option( 'show_on_front' ); // トップページ指定するかどうか page or posts

		/*** ↓↓ テスト用事前データ設定（ test_lightning_is_layout_onecolumn と test_lightning_is_subsection_display 共通 ) */

		register_post_type(
			'event',
			array(
				'label'       => 'Event',
				'has_archive' => true,
				'public'      => true,
			)
		);
		register_taxonomy(
			'event_cat',
			'event',
			array(
				'label'        => 'Event Category',
				'rewrite'      => array( 'slug' => 'event_cat' ),
				'hierarchical' => true,
			)
		);

		// Create test category
		$catarr             = array(
			'cat_name' => 'parent_category',
		);
		$parent_category_id = wp_insert_category( $catarr );

		$catarr            = array(
			'cat_name'        => 'child_category',
			'category_parent' => $parent_category_id,
		);
		$child_category_id = wp_insert_category( $catarr );

		$catarr              = array(
			'cat_name' => 'no_post_category',
		);
		$no_post_category_id = wp_insert_category( $catarr );

		// Create test term
		$args          = array(
			'slug' => 'event_category_name',
		);
		$term_info     = wp_insert_term( 'event_category_name', 'event_cat', $args );
		$event_term_id = $term_info['term_id'];

		// Create test post
		$post    = array(
			'post_title'    => 'test',
			'post_status'   => 'publish',
			'post_content'  => 'content',
			'post_category' => array( $parent_category_id ),
		);
		$post_id = wp_insert_post( $post );
		// 投稿にカテゴリー指定
		wp_set_object_terms( $post_id, 'child_category', 'category' );

		// Create test page
		$post           = array(
			'post_title'   => 'parent_page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
		);
		$parent_page_id = wp_insert_post( $post );

		$post = array(
			'post_title'   => 'child_page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
			'post_parent'  => $parent_page_id,

		);
		$child_page_id = wp_insert_post( $post );

		// Create test home page
		$post         = array(
			'post_title'   => 'post_top',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
		);
		$home_page_id = wp_insert_post( $post );

		// Create test home page
		$post          = array(
			'post_title'   => 'front_page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
		);
		$front_page_id = wp_insert_post( $post );

		// custom post type.
		$post          = array(
			'post_title'   => 'event-test-post',
			'post_type'    => 'event',
			'post_status'  => 'publish',
			'post_content' => 'content',
		);
		$event_post_id = wp_insert_post( $post );
		// set event category to event post
		wp_set_object_terms( $event_post_id, 'event_category_name', 'event_cat' );

		/*** ↑↑ テスト用事前データ設定（ test_lightning_is_layout_onecolumn と test_lightning_is_subsection_display 共通 ) */

		/*
		 Test Array
		/*--------------------------------*/
		$test_array = array(

			// 404ページ
			array(
				'target_url' => home_url( '/?name=aaaaa' ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => __( 'Not found', 'lightning' ),
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),
			// 検索結果（検索キーワードなし）
			array(
				'target_url' => home_url( '/?s=' ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => __( 'Search Results', 'lightning' ),
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// 検索結果（検索キーワード:aaa）
			array(
				'target_url' => home_url( '/?s=aaa' ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => sprintf( __( 'Search Results for : %s', 'lightning' ), 'aaa' ),
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに固定ページ
			// トップページに指定した固定ページ名
			array(
				'target_url' => home_url(),
				'options'    => array(
					'page_on_front'  => $front_page_id,
					'show_on_front'  => 'page',
					'page_for_posts' => $home_page_id,
				),
				'correct'    => array(
					array(
						'name'  => 'front_page',
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
				),
			),
			// 固定ページ
			// HOME > 固定ページ名.
			array(
				'target_url' => get_permalink( $parent_page_id ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'parent_page',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに固定ページ / 投稿トップに特定の固定ページ指定 / 固定ページ
			// トップページに固定ページ > 固定ページ名
			array(
				'options'    => array(
					'page_on_front'  => $front_page_id,
					'show_on_front'  => 'page',
					'page_for_posts' => $home_page_id,
				),
				'target_url' => get_permalink( $parent_page_id ),
				'correct'    => array(
					array(
						'name'  => 'front_page',
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'parent_page',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// 固定ページの子ページ
			// トップに指定した固定ページ名 > 親ページ > 子ページ
			array(
				'options'    => array(
					'page_on_front'  => $front_page_id,
					'show_on_front'  => 'page',
					'page_for_posts' => $home_page_id,
				),
				'target_url' => get_permalink( $child_page_id ),
				'correct'    => array(
					array(
						'name'  => 'front_page',
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'parent_page',
						'id'    => '',
						'url'   => get_permalink( $parent_page_id ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'child_page',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページ未指定 / 投稿トップ未指定 / 固定ページの子ページ
			// HOME > 親ページ > 子ページ
			array(
				'target_url' => get_permalink( $child_page_id ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'parent_page',
						'id'    => '',
						'url'   => get_permalink( $parent_page_id ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'child_page',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに最新の投稿（投稿トップ未指定） / 子カテゴリー
			// HOME > 親カテゴリー > 子カテゴリー
			array(
				'options'    => array(
					'page_for_posts' => null,
				),
				'target_url' => get_term_link( $child_category_id, 'category' ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'parent_category',
						'id'    => '',
						'url'   => get_term_link( $parent_category_id, 'category' ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'child_category',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに最新の投稿 / 投稿トップページ無指定 / 記事ページ
			// HOME > 親カテゴリー > 子カテゴリー > 記事タイトル
			array(
				'options'    => array(
					'page_for_posts' => null,
				),
				'target_url' => get_permalink( $post_id ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'parent_category',
						'id'    => '',
						'url'   => get_term_link( $parent_category_id, 'category' ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'child_category',
						'id'    => '',
						'url'   => get_term_link( $child_category_id, 'category' ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'test',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに固定ページ / 投稿トップに特定の固定ページ指定
			// HOME > 投稿トップの固定ページ名
			array(
				'options'    => array(
					'page_on_front'  => $front_page_id,
					'show_on_front'  => 'page',
					'page_for_posts' => $home_page_id,
				),
				'target_url' => get_permalink( $home_page_id ),
				'correct'    => array(
					array(
						'name'  => 'front_page',
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'post_top',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに固定ページ / 投稿トップに特定の固定ページ指定 / 子カテゴリー
			// トップに指定した固定ページ名 > 投稿トップの固定ページ名 > 親カテゴリー > 子カテゴリー
			array(
				'options'    => array(
					'page_on_front'  => $front_page_id,
					'show_on_front'  => 'page',
					'page_for_posts' => $home_page_id,
				),
				'target_url' => get_term_link( $child_category_id, 'category' ),
				'correct'    => array(
					array(
						'name'  => 'front_page',
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'post_top',
						'id'    => '',
						'url'   => get_permalink( $home_page_id ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'parent_category',
						'id'    => '',
						'url'   => get_term_link( $parent_category_id, 'category' ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'child_category',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに固定ページ / 投稿トップに特定の固定ページ指定 / 投稿のないカテゴリーアーカイブページ
			// トップに指定した固定ページ名 > 投稿トップの固定ページ名 > 投稿のないカテゴリー名
			array(
				'options'    => array(
					'page_on_front'  => $front_page_id,
					'show_on_front'  => 'page',
					'page_for_posts' => $home_page_id,
				),
				'target_url' => get_term_link( $no_post_category_id, 'category' ),
				'correct'    => array(
					array(
						'name'  => 'front_page',
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'post_top',
						'id'    => '',
						'url'   => get_permalink( $home_page_id ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'no_post_category',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに固定ページ / 投稿トップに特定の固定ページ指定 / 年別アーカイブ
			// トップに指定した固定ページ名 > 投稿トップの固定ページ名 > アーカイブ名
			array(
				'options'    => array(
					'page_on_front'  => $front_page_id,
					'show_on_front'  => 'page',
					'page_for_posts' => $home_page_id,
				),
				'target_url' => home_url() . '/?post_type=post&year=' . date( 'Y' ),
				'correct'    => array(
					array(
						'name'  => 'front_page',
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'post_top',
						'id'    => '',
						'url'   => get_permalink( $home_page_id ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'Year: <span>' . date( 'Y' ) . '</span>',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// トップページに固定ページ / 投稿トップページ無指定 / 年別アーカイブ
			// HOME > アーカイブ名
			array(
				'options'    => array(
					'page_for_posts' => null,
				),
				'target_url' => home_url() . '/?post_type=post&year=' . date( 'Y' ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'Year: <span>' . date( 'Y' ) . '</span>',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// カスタム投稿タイプトップ
			// HOME > 投稿タイプ名
			array(
				'target_url' => home_url() . '/?post_type=event',
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'Event',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// カスタム投稿タイプ / カスタム分類アーカイブ
			// HOME > 投稿タイプ名 > カスタム分類
			array(
				'target_url' => get_term_link( $event_term_id ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'Event',
						'id'    => '',
						'url'   => get_post_type_archive_link( 'event' ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'event_category_name',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// カスタム投稿タイプ / 年別アーカイブ
			// HOME > 投稿タイプ名 > アーカイブ名
			array(
				'target_url' => home_url() . '/?post_type=event&year=' . date( 'Y' ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'Event',
						'id'    => '',
						'url'   => get_post_type_archive_link( 'event' ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'Year: <span>' . date( 'Y' ) . '</span>',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// カスタム投稿タイプ / 記事詳細
			// HOME > 投稿タイプ名 > カスタム分類 > 記事タイトル
			array(
				'target_url' => get_permalink( $event_post_id ),
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'Event',
						'id'    => '',
						'url'   => get_post_type_archive_link( 'event' ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'event_category_name',
						'id'    => '',
						'url'   => get_term_link( $event_term_id ),
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'event-test-post',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

			// カテゴリーとキーワードでの絞り込み検索
			// HOME > "test"の検索結果 .
			array(
				'target_url' => home_url() . '/?category_name=child_category&s=test',
				'correct'    => array(
					array(
						'name'  => __( 'HOME', 'lightning' ),
						'id'    => '',
						'url'   => home_url(),
						'class' => 'breadcrumb-list__item--home',
						'icon'  => 'fa-solid fa-fw fa-house',
					),
					array(
						'name'  => 'Search Results for : test',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
			),

		);

		foreach ( $test_array as $value ) {
			if ( ! empty( $value['options'] ) && is_array( $value['options'] ) ) {
				foreach ( $value['options'] as $option_key => $option_value ) {
					update_option( $option_key, $option_value );
				}
			}

			// Move to test page
			$this->go_to( $value['target_url'] );
			// $return = $vk_breadcrumb->get_array();
			$return = VkBreadcrumb::get_array();

			global $wp_query;
			print '<pre style="text-align:left">';
			print_r( $wp_query->query );
			print '</pre>';

			print PHP_EOL;
			print $value['target_url'] . PHP_EOL;
			print 'return------------------------------------' . PHP_EOL;
			var_dump( $return ) . PHP_EOL;
			print 'correct------------------------------------' . PHP_EOL;
			var_dump( $value['correct'] ) . PHP_EOL;
			print '------------------------------------' . PHP_EOL;

			$this->assertEquals( $value['correct'], $return );

			if ( ! empty( $value['options'] ) && is_array( $value['options'] ) ) {
				foreach ( $value['options'] as $option_key => $option_value ) {
					delete_option( $option_key );
				}
			}
		}

		/*
		 テスト前の値に戻す
		/*--------------------------------*/
		wp_delete_post( $post_id );
		wp_delete_post( $home_page_id );
		$parent_category_id = wp_delete_category( $catarr );
		update_option( 'lightning_theme_options', $before_option );
		update_option( 'page_for_posts', $before_page_for_posts );
		update_option( 'page_on_front', $before_page_on_front );
		update_option( 'show_on_front', $before_show_on_front );
	}

	/**
	 * get_breadcrumb() の HTML 出力に <nav> ランドマークと aria-current が含まれるか検証する。
	 */
	function test_get_breadcrumb() {

		// テスト用の固定ページを作成（親・子の2階層）
		$parent_page_id = wp_insert_post( array(
			'post_title'   => 'a11y-parent-page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
		) );

		$child_page_id = wp_insert_post( array(
			'post_title'   => 'a11y-child-page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
			'post_parent'  => $parent_page_id,
		) );

		// aria-current="page" が <li> 開始タグの中に付くことを検証する正規表現。
		// （span ではなく li に付与する設計のため、開始タグ <li ... aria-current="page"> を期待する）
		$li_aria_current_regex = '/<li[^>]*aria-current="page"/';

		$test_cases = array(
			array(
				'test_condition_name' => '子固定ページ表示時 => <nav> と aria-label が出力される',
				'target_url'          => get_permalink( $child_page_id ),
				'expected_contains'   => array(
					'<nav ',
					'aria-label=',
				),
				'expected_not_contains' => array(),
				'expected_li_aria_current' => true,
			),
			array(
				'test_condition_name' => '子固定ページ表示時 => 最後の要素（現在ページ）の <li> に aria-current="page" が付く',
				'target_url'          => get_permalink( $child_page_id ),
				'expected_contains'   => array(
					'aria-current="page"',
				),
				'expected_not_contains' => array(),
				'expected_li_aria_current' => true,
			),
			array(
				'test_condition_name' => '親固定ページ（リンクなし末端）でも <li> に aria-current="page" が付く',
				'target_url'          => get_permalink( $parent_page_id ),
				'expected_contains'   => array(
					'aria-current="page"',
				),
				'expected_not_contains' => array(),
				'expected_li_aria_current' => true,
			),
			array(
				'test_condition_name' => 'トップページ（HOME1項目のみ）でも <nav> と唯一の <li> の aria-current が出力される',
				'target_url'          => home_url(),
				'expected_contains'   => array(
					'<nav ',
				),
				'expected_not_contains' => array(),
				'expected_li_aria_current' => true,
			),
		);

		foreach ( $test_cases as $case ) {
			$this->go_to( $case['target_url'] );
			$html = VkBreadcrumb::get_breadcrumb();

			foreach ( $case['expected_contains'] as $needle ) {
				$this->assertStringContainsString( $needle, $html, $case['test_condition_name'] . ' / 期待文字列: ' . $needle );
			}
			foreach ( $case['expected_not_contains'] as $needle ) {
				$this->assertStringNotContainsString( $needle, $html, $case['test_condition_name'] . ' / 含まれてはいけない文字列: ' . $needle );
			}
			// aria-current が <li> 開始タグに付いていることを正規表現で検証する。
			if ( ! empty( $case['expected_li_aria_current'] ) ) {
				$this->assertMatchesRegularExpression( $li_aria_current_regex, $html, $case['test_condition_name'] . ' / <li> に aria-current="page" が付くこと' );
				// 現在ページは1つだけなので、aria-current="page" はちょうど1箇所であることまで固定する。
				preg_match_all( $li_aria_current_regex, $html, $li_aria_current_matches );
				$this->assertSame( 1, count( $li_aria_current_matches[0] ), $case['test_condition_name'] . ' / aria-current="page" が <li> にちょうど1箇所だけ付くこと' );
			}
		}

		// vk_breadcrumb_nav_aria_label フィルタで <nav> の aria-label を上書きできることを検証する。
		// テスト間に漏れないよう、検証後に必ず remove_filter() で外す。
		$custom_aria_label = 'カスタムパンくず';
		$aria_label_filter = function () use ( $custom_aria_label ) {
			return $custom_aria_label;
		};
		add_filter( 'vk_breadcrumb_nav_aria_label', $aria_label_filter );
		$this->go_to( get_permalink( $child_page_id ) );
		$filtered_html = VkBreadcrumb::get_breadcrumb();
		remove_filter( 'vk_breadcrumb_nav_aria_label', $aria_label_filter );
		$this->assertStringContainsString(
			'<nav aria-label="' . $custom_aria_label . '">',
			$filtered_html,
			'vk_breadcrumb_nav_aria_label フィルタで <nav> の aria-label を上書きできること'
		);

		// テストデータを削除
		wp_delete_post( $child_page_id, true );
		wp_delete_post( $parent_page_id, true );
	}

	/**
	 * the_breadcrumb() が wp_kses 通過後も <nav> と aria-current を出力するか検証する。
	 *
	 * wp_kses の許可リストに nav / aria-label / aria-current が追加されていなければ
	 * これらの属性・タグが剥がされてしまう。
	 */
	function test_the_breadcrumb() {

		// テスト用の固定ページを2つ作成
		$parent_page_id = wp_insert_post( array(
			'post_title'   => 'kses-parent-page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
		) );

		$child_page_id = wp_insert_post( array(
			'post_title'   => 'kses-child-page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
			'post_parent'  => $parent_page_id,
		) );

		// wp_kses 通過後も aria-current が <li> に残ることを検証する正規表現。
		$li_aria_current_regex = '/<li[^>]*aria-current="page"/';

		$test_cases = array(
			array(
				'test_condition_name' => 'the_breadcrumb() は wp_kses 後も <nav aria-label と <li の aria-current を出力する',
				'target_url'          => get_permalink( $child_page_id ),
				'expected_contains'   => array(
					'<nav ',
					'aria-label=',
					'aria-current="page"',
				),
				'expected_li_aria_current' => true,
			),
			array(
				'test_condition_name' => '境界値: トップページのみのパンくずでも <nav> と <li の aria-current が出力される',
				'target_url'          => home_url(),
				'expected_contains'   => array(
					'<nav ',
					'</nav>',
				),
				'expected_li_aria_current' => true,
			),
		);

		foreach ( $test_cases as $case ) {
			$this->go_to( $case['target_url'] );

			// the_breadcrumb() は echo するため ob_start() でキャプチャする
			ob_start();
			VkBreadcrumb::the_breadcrumb();
			$html = ob_get_clean();

			foreach ( $case['expected_contains'] as $needle ) {
				$this->assertStringContainsString( $needle, $html, $case['test_condition_name'] . ' / 期待文字列: ' . $needle );
			}
			// wp_kses 後も aria-current が <li> 開始タグに残っていることを検証する。
			if ( ! empty( $case['expected_li_aria_current'] ) ) {
				$this->assertMatchesRegularExpression( $li_aria_current_regex, $html, $case['test_condition_name'] . ' / wp_kses 後も <li> に aria-current="page" が残ること' );
				// 現在ページは1つだけなので、wp_kses 後も aria-current="page" はちょうど1箇所であることまで固定する。
				preg_match_all( $li_aria_current_regex, $html, $li_aria_current_matches );
				$this->assertSame( 1, count( $li_aria_current_matches[0] ), $case['test_condition_name'] . ' / wp_kses 後も aria-current="page" が <li> にちょうど1箇所だけ残ること' );
			}
		}

		// テストデータを削除
		wp_delete_post( $child_page_id, true );
		wp_delete_post( $parent_page_id, true );
	}

	/**
	 * get_scheme_json_array() が、構造化データ（JSON-LD）用に、名前のタグを除き、
	 * 名前と URL の文字参照（&amp; や &#038; など）を元の文字に戻した値を返すか検証する。
	 */
	public function test_get_scheme_json_array() {

		// アーカイブの URL を VkHelpers が esc_url() 済みで返す、テスト用の投稿タイプを登録する。
		register_post_type(
			'scheme_event',
			array(
				'label'       => 'Tom & Jerry Events',
				'has_archive' => true,
				'public'      => true,
			)
		);

		// & を含むカテゴリー（親・子）を作成する。保存時に & は &amp; になる。
		$parent_category_id = wp_insert_category( array( 'cat_name' => 'Tom & Jerry' ) );
		$child_category_id  = wp_insert_category(
			array(
				'cat_name'        => "Q&A It's",
				'category_parent' => $parent_category_id,
			)
		);

		// & ' " を含むタイトルの投稿を作成する。表示時に ' は &#8217; などへ整形される。
		$post_id = wp_insert_post(
			array(
				'post_title'    => 'Tom & Jerry\'s "Show"',
				'post_status'   => 'publish',
				'post_content'  => 'content',
				'post_category' => array( $child_category_id ),
			)
		);

		// テスト用の投稿タイプの投稿を作成する。
		$event_post_id = wp_insert_post(
			array(
				'post_title'   => 'scheme-event-post',
				'post_type'    => 'scheme_event',
				'post_status'  => 'publish',
				'post_content' => 'content',
			)
		);

		// 年別アーカイブ用に、日付を固定した投稿を作成する。
		$year_post_id = wp_insert_post(
			array(
				'post_title'   => 'scheme-year-post',
				'post_status'  => 'publish',
				'post_content' => 'content',
				'post_date'    => '2020-05-05 10:00:00',
			)
		);

		// 期待値に使う URL。
		$home_url             = home_url();
		$parent_category_link = get_term_link( $parent_category_id, 'category' );
		$child_category_link  = get_term_link( $child_category_id, 'category' );

		$test_cases = array(
			array(
				'test_condition_name' => '& を含むカテゴリー名と、& \' " を含む投稿タイトルの投稿ページ => 名前は &amp; や &#8217; ではなく元の文字',
				'conditions'          => array(),
				'target_url'          => get_permalink( $post_id ),
				'expected'            => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => $home_url,
						),
						array(
							'@type'    => 'ListItem',
							'position' => 2,
							'name'     => 'Tom & Jerry',
							'item'     => $parent_category_link,
						),
						array(
							'@type'    => 'ListItem',
							'position' => 3,
							'name'     => "Q&A It's",
							'item'     => $child_category_link,
						),
						array(
							'@type'    => 'ListItem',
							'position' => 4,
							'name'     => 'Tom & Jerry’s “Show”',
						),
					),
				),
			),
			array(
				'test_condition_name' => 'カテゴリーの URL が ?a=1&b=2 を含む => item の & は &amp; や &#038; にならずそのまま',
				'conditions'          => array(
					'filters' => array(
						'term_link' => function ( $link, $term ) {
							return 'https://example.com/?cat=' . $term->term_id . '&a=1&b=2';
						},
					),
				),
				'target_url'          => get_permalink( $post_id ),
				'expected'            => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => $home_url,
						),
						array(
							'@type'    => 'ListItem',
							'position' => 2,
							'name'     => 'Tom & Jerry',
							'item'     => 'https://example.com/?cat=' . $parent_category_id . '&a=1&b=2',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 3,
							'name'     => "Q&A It's",
							'item'     => 'https://example.com/?cat=' . $child_category_id . '&a=1&b=2',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 4,
							'name'     => 'Tom & Jerry’s “Show”',
						),
					),
				),
			),
			array(
				'test_condition_name' => '投稿タイプの名前とアーカイブ URL が esc_html() / esc_url() 済み（&amp; / &#038;）で入る => 元の & に戻る',
				'conditions'          => array(
					'filters' => array(
						'post_type_archive_link' => function () {
							return 'https://example.com/events/?lang=en&ref=a';
						},
					),
				),
				'target_url'          => get_permalink( $event_post_id ),
				'expected'            => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => $home_url,
						),
						array(
							'@type'    => 'ListItem',
							'position' => 2,
							'name'     => 'Tom & Jerry Events',
							'item'     => 'https://example.com/events/?lang=en&ref=a',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 3,
							'name'     => 'scheme-event-post',
						),
					),
				),
			),
			array(
				'test_condition_name' => '検索キーワードに & を含む検索結果ページ => 名前の & は &amp; にならずそのまま',
				'conditions'          => array(),
				'target_url'          => home_url( '/?s=Tom+%26+Jerry' ),
				'expected'            => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => $home_url,
						),
						array(
							'@type'    => 'ListItem',
							'position' => 2,
							'name'     => 'Search Results for : Tom & Jerry',
						),
					),
				),
			),
			array(
				'test_condition_name' => '境界値: 年別アーカイブのタイトルに含まれる <span> タグ => タグは除かれ、文字だけが名前になる',
				'conditions'          => array(),
				'target_url'          => home_url( '/?year=2020' ),
				'expected'            => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => $home_url,
						),
						array(
							'@type'    => 'ListItem',
							'position' => 2,
							'name'     => 'Year: 2020',
						),
					),
				),
			),
			array(
				'test_condition_name' => '異常系: フィルターでタグ・単独の <・文字参照で書いた </script> を含む名前と、文字参照で書いた javascript: の URL を渡す => タグは除かれ、文字参照は元の文字に戻り、javascript: の URL は空になる',
				'conditions'          => array(
					'filters' => array(
						'vk_breadcrumb_array' => function () {
							return array(
								array(
									'name'  => 'HOME',
									'id'    => '',
									'url'   => 'https://example.com/',
									'class' => '',
									'icon'  => '',
								),
								array(
									'name'  => '<ruby>漢字<rt>かんじ</rt></ruby>',
									'id'    => '',
									'url'   => 'https://example.com/?a=1&#038;b=2',
									'class' => '',
									'icon'  => '',
								),
								array(
									'name'  => 'x<5 &amp; y&gt;3',
									'id'    => '',
									'url'   => '&#106;avascript:alert(1)',
									'class' => '',
									'icon'  => '',
								),
								array(
									'name'  => '&lt;/script&gt;&lt;script&gt;alert(1)&lt;/script&gt;',
									'id'    => '',
									'url'   => '',
									'class' => '',
									'icon'  => '',
								),
							);
						},
					),
				),
				'target_url'          => home_url(),
				'expected'            => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => 'https://example.com/',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 2,
							'name'     => '漢字かんじ',
							'item'     => 'https://example.com/?a=1&b=2',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 3,
							'name'     => 'x<5 & y>3',
							'item'     => '',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 4,
							'name'     => '</script><script>alert(1)</script>',
						),
					),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			// ケースごとのフィルターを登録する。
			$filters = isset( $case['conditions']['filters'] ) ? $case['conditions']['filters'] : array();
			foreach ( $filters as $hook => $callback ) {
				add_filter( $hook, $callback, 10, 3 );
			}

			// テスト対象のページに移動して、構造化データ用の配列を取得する。
			$this->go_to( $case['target_url'] );
			$actual = VkBreadcrumb::get_scheme_json_array();

			// 後続のケースに漏れないよう、フィルターを外す。
			foreach ( $filters as $hook => $callback ) {
				remove_filter( $hook, $callback, 10 );
			}

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}

		// テストデータと、登録した投稿タイプを削除する。
		wp_delete_post( $post_id, true );
		wp_delete_post( $event_post_id, true );
		wp_delete_post( $year_post_id, true );
		wp_delete_category( $child_category_id );
		wp_delete_category( $parent_category_id );
		unregister_post_type( 'scheme_event' );
	}

	/**
	 * the_scheme_script() が、& を &amp; に変えずに出力し、値の中の </script> や <!-- で
	 * script 要素が閉じたり壊れたりしない JSON-LD を出力するか検証する。
	 */
	public function test_the_scheme_script() {

		$test_cases = array(
			array(
				'test_condition_name'   => '& を含む名前と URL、&#8217; を含む名前 => script 要素の中に & や ’ がそのまま出力され、読み直すと元の値',
				'breadcrumb_array'      => array(
					array(
						'name'  => 'HOME',
						'id'    => '',
						'url'   => 'https://example.com/',
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'Tom &amp; Jerry',
						'id'    => '',
						'url'   => 'https://example.com/?a=1&#038;b=2',
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => 'It&#8217;s',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
				'expected_contains'     => array(
					'"name": "Tom & Jerry"',
					'"item": "https://example.com/?a=1&b=2"',
					'"name": "It’s"',
				),
				'expected_not_contains' => array(
					'&amp;',
					'&#038;',
					'&#8217;',
				),
				'expected_decoded'      => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => 'https://example.com/',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 2,
							'name'     => 'Tom & Jerry',
							'item'     => 'https://example.com/?a=1&b=2',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 3,
							'name'     => 'It’s',
						),
					),
				),
			),
			array(
				'test_condition_name'   => '名前に </script> や <!-- を含む => < と > は \u003C と \u003E で出力され、script 要素が途中で閉じず、読み直すと元の値',
				'breadcrumb_array'      => array(
					array(
						'name'  => 'HOME',
						'id'    => '',
						'url'   => 'https://example.com/',
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => '&lt;/script&gt;&lt;script&gt;alert(1)&lt;/script&gt;',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
					array(
						'name'  => '&lt;!-- --&gt;',
						'id'    => '',
						'url'   => '',
						'class' => '',
						'icon'  => '',
					),
				),
				'expected_contains'     => array(
					'"name": "\u003C/script\u003E\u003Cscript\u003Ealert(1)\u003C/script\u003E"',
					'"name": "\u003C!-- --\u003E"',
				),
				'expected_not_contains' => array(
					'<',
					'>',
				),
				'expected_decoded'      => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => 'https://example.com/',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 2,
							'name'     => '</script><script>alert(1)</script>',
						),
						array(
							'@type'    => 'ListItem',
							'position' => 3,
							'name'     => '<!-- -->',
						),
					),
				),
			),
			array(
				'test_condition_name'   => '境界値: フィルターを使わないトップページ（HOME の1項目のみ） => HOME だけの JSON-LD が出力される',
				'breadcrumb_array'      => null,
				'expected_contains'     => array(
					'"name": "HOME"',
					'"item": "' . home_url() . '"',
				),
				'expected_not_contains' => array(
					'<',
					'>',
				),
				'expected_decoded'      => array(
					'@context'        => 'https://schema.org',
					'@type'           => 'BreadcrumbList',
					'itemListElement' => array(
						array(
							'@type'    => 'ListItem',
							'position' => 1,
							'name'     => 'HOME',
							'item'     => home_url(),
						),
					),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			// パンくず配列を差し替えるケースでは、フィルターで配列を返す。
			$breadcrumb_array_filter = null;
			if ( is_array( $case['breadcrumb_array'] ) ) {
				$breadcrumb_array        = $case['breadcrumb_array'];
				$breadcrumb_array_filter = function () use ( $breadcrumb_array ) {
					return $breadcrumb_array;
				};
				add_filter( 'vk_breadcrumb_array', $breadcrumb_array_filter );
			}

			// トップページに移動し、the_scheme_script() は echo するため ob_start() で受け取る。
			$this->go_to( home_url() );
			ob_start();
			VkBreadcrumb::the_scheme_script();
			$output = ob_get_clean();

			// 後続のケースに漏れないよう、フィルターを外す。
			if ( $breadcrumb_array_filter ) {
				remove_filter( 'vk_breadcrumb_array', $breadcrumb_array_filter );
			}

			// script 要素は1つだけで、値の中の </script> で途中で閉じていないこと。
			$this->assertSame( 1, substr_count( $output, '</script>' ), $case['test_condition_name'] . ' / </script> は閉じタグの1箇所だけ' );

			// script 要素の中身を取り出す。値の中の </script> で要素が途中で閉じると、中身が途中で切れて以降の検証が一致しなくなる。
			preg_match( '#<script type="application/ld\+json">\n(.*?)</script>#s', $output, $matches );
			$payload = isset( $matches[1] ) ? $matches[1] : '';

			foreach ( $case['expected_contains'] as $needle ) {
				$this->assertStringContainsString( $needle, $payload, $case['test_condition_name'] . ' / 期待文字列: ' . $needle );
			}
			foreach ( $case['expected_not_contains'] as $needle ) {
				$this->assertStringNotContainsString( $needle, $payload, $case['test_condition_name'] . ' / 含まれてはいけない文字列: ' . $needle );
			}

			// JSON として読み直した値が期待どおりであること。
			$this->assertSame( $case['expected_decoded'], json_decode( $payload, true ), $case['test_condition_name'] . ' / JSON として読み直した値' );
		}
	}
}
