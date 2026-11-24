<?php
/**
 * VK_Breadcrumb
 *
 * @package vektor-inc/vk-breadcrumb
 * @license GPL-2.0+
 *
 * @version 0.2.8
 */

namespace VektorInc\VK_Breadcrumb;

use VektorInc\VK_Helpers\VkHelpers;

/**
 * Bread Crumb
 */
class VkBreadcrumb {

	/**
	 * Bread Crumb Array
	 */
	public static function get_array() {

		global $wp_query;

		// Get Post top page info
		// get_post_type() だとtaxonomyページで該当の投稿がない時に投稿タイプを取得できないため VkHelpers::get_post_type_info() を使用.
		$post_type_info = VkHelpers::get_post_type_info();
		$post_top_info  = VkHelpers::get_post_top_info();
		$post_type      = $post_type_info['slug'];
		$show_on_front  = get_option( 'show_on_front' );
		$page_on_front  = get_option( 'page_on_front' );
		$post           = $wp_query->get_queried_object();

		// Home.
		$front_page_name = 'HOME';
		$page_on_front   = get_option( 'page_on_front' );

		if ( $page_on_front ) {
			$front_page_name = get_the_title( $page_on_front );
		}

		$breadcrumb_array = array(
			array(
				'name'  => $front_page_name,
				'id'    => '',
				'url'   => home_url(),
				'class' => 'breadcrumb-list__item--home',
				'icon'  => 'fa-solid fa-fw fa-house',
			),
		);

		if ( is_home() && ! is_front_page() ) {
			$breadcrumb_array[] = array(
				'name'  => esc_html( $post_top_info['name'] ),
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);

		} elseif ( is_404() ) {
			$breadcrumb_array[] = array(
				'name'  => __( 'Not found', 'lightning' ),
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);
		} elseif ( is_attachment() ) {
			$breadcrumb_array[] = array(
				'name'  => get_the_title(),
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);

		} elseif ( is_author() ) {
			$user_obj           = get_queried_object();
			$breadcrumb_array[] = array(
				'name'  => $user_obj->display_name,
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);

		} elseif ( is_search() ) {
			// For filter search term & keywords or term & no keyword.
			if ( get_search_query() ) {
				// translators: search keyword .
				$name = sprintf( __( 'Search Results for : %s', 'lightning' ), get_search_query() );
			} else {
				$name = __( 'Search Results', 'lightning' );
			}
			$breadcrumb_array[] = array(
				'name'  => $name,
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);

		} elseif ( is_page() && ! is_front_page() ) {
			$post = $wp_query->get_queried_object();
			// 第一階層.
			if ( 0 === $post->post_parent ) {
				$breadcrumb_array[] = array(
					'name'  => strip_tags( apply_filters( 'single_post_title', get_the_title() ) ),
					'id'    => '',
					'url'   => '',
					'class' => '',
					'icon'  => '',
				);
			} else {
				// 子階層がある場合.
				$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
				array_push( $ancestors, $post->ID );
				foreach ( $ancestors as $ancestor ) {
					if ( end( $ancestors ) !== $ancestor ) {
						$breadcrumb_array[] = array(
							'name'  => strip_tags( apply_filters( 'single_post_title', get_the_title( $ancestor ) ) ),
							'id'    => '',
							'url'   => get_permalink( $ancestor ),
							'class' => '',
							'icon'  => '',
						);
					} else {
						$breadcrumb_array[] = array(
							'name'  => strip_tags( apply_filters( 'single_post_title', get_the_title( $ancestor ) ) ),
							'id'    => '',
							'url'   => '',
							'class' => '',
							'icon'  => '',
						);
					}
				}
			}
		} elseif ( is_post_type_archive() && ! is_date() ) {
			$breadcrumb_array[] = array(
				'name'  => $post_type_info['name'],
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);
		} elseif ( ( is_single() || is_archive() ) || is_date() && ! is_post_type_archive() && ! is_search() ) {
			if ( 'post' !== $post_type_info['slug'] || $post_top_info['use'] ) {
				$breadcrumb_array[] = array(
					'name'  => $post_type_info['name'],
					'id'    => '',
					'url'   => $post_type_info['url'],
					'class' => '',
					'icon'  => '',
				);
			}
		}

		/*****************************************************
		 * Under 3 hierarchy
		 */

		if ( is_date() && ! is_search() ) {
			$breadcrumb_array[] = array(
				'name'  => get_the_archive_title(),
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);

		} elseif ( is_tag() && ! is_search() ) {
			$breadcrumb_array[] = array(
				'name'  => single_tag_title( '', false ),
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);

		} elseif ( is_category() && ! is_search() ) {

			/*****************************************
			 *  Category
			 */

			// Get category information & insert to $cat.
			$cat = get_queried_object();

			// parent !== 0  >>>  Parent exist.
			if ( 0 !== $cat->parent ) {
				// 祖先のカテゴリー情報を逆順で取得.
				$ancestors = array_reverse( get_ancestors( $cat->cat_ID, 'category' ) );
				// 祖先階層の配列回数分ループ.
				foreach ( $ancestors as $ancestor ) :
					$breadcrumb_array[] = array(
						'name'  => get_cat_name( $ancestor ),
						'id'    => '',
						'url'   => get_category_link( $ancestor ),
						'class' => '',
						'icon'  => '',
					);
				endforeach;
			}
			$breadcrumb_array[] = array(
				'name'  => $cat->cat_name,
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);

		} elseif ( is_tax() && ! is_search() ) {

			/*****************************************
			 * Term
			 */
			$now_term        = $wp_query->queried_object->term_id;
			$now_term_parent = $wp_query->queried_object->parent;
			$now_taxonomy    = $wp_query->queried_object->taxonomy;

			// parent が !0 の場合 = 親カテゴリーが存在する場合.
			if ( 0 !== $now_term_parent ) {
				// 祖先のカテゴリー情報を逆順で取得.
				$ancestors = array_reverse( get_ancestors( $now_term, $now_taxonomy ) );
				// 祖先階層の配列回数分ループ.
				foreach ( $ancestors as $ancestor ) :
					$pan_term           = get_term( $ancestor, $now_taxonomy );
					$breadcrumb_array[] = array(
						'name'  => esc_html( $pan_term->name ),
						'id'    => '',
						'url'   => get_term_link( $ancestor, $now_taxonomy ),
						'class' => '',
						'icon'  => '',
					);
				endforeach;
			}
			$breadcrumb_array[] = array(
				'name'  => single_cat_title( '', '', false ),
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);

		} elseif ( is_single() ) {

			/**********************************
			 * Single
			 */

			// Case of post.
			if ( 'post' == $post_type_info['slug'] ) {
				$category = get_the_category();
				if ( $category ) {
					// get parent category info.
					$parents = array_reverse( get_ancestors( $category[0]->term_id, 'category', 'taxonomy' ) );
					array_push( $parents, $category[0]->term_id );
					foreach ( $parents as $parent_term_id ) {
						$parent_obj         = get_term( $parent_term_id, 'category' );
						$term_url           = get_term_link( $parent_obj->term_id, $parent_obj->taxonomy );
						$breadcrumb_array[] = array(
							'name'  => $parent_obj->name,
							'id'    => '',
							'url'   => $term_url,
							'class' => '',
							'icon'  => '',
						);
					}
				}
			} else {
				// Case of custom post type.
				// Taxonomy of Single or Page.
				$get_taxonomies = get_the_taxonomies();

				// 非公開のタクソノミーを自動的に除外.
				foreach ( $get_taxonomies as $taxonomy => $value ) {
					$taxonomy_info = get_taxonomy( $taxonomy );
					if ( empty( $taxonomy_info->public ) ) {
						unset( $get_taxonomies[ $taxonomy ] );
					}
				}

				$taxonomies = array();

				// 一旦タクソノミーの文字列配列に変換しないと色々と面倒.
				foreach ( $get_taxonomies as $key => $value ) {
					$taxonomies[] = $key;
				}

				// 除外するタクソノミーの文字列配列.
				$exclusion = array();
				$exclusion = apply_filters( 'vk_breadcrumb_taxonomies_exludion', $exclusion );

				// タクソノミーの差分を採用.
				if ( $exclusion ) {
					$taxonomies = array_diff( $taxonomies, $exclusion );
				}

				if ( $taxonomies ) {

					foreach ( $taxonomies as $key ) {
						$taxonomy = $key;
						break;
					}

					$terms = get_the_terms( get_the_ID(), $taxonomy );

					// keeps only the first term (categ).
					$term = reset( $terms );
					if ( 0 != $term->parent ) {

						// Get term ancestors info.
						$ancestors = array_reverse( get_ancestors( $term->term_id, $taxonomy ) );
						// Print loop term ancestors.
						foreach ( $ancestors as $ancestor ) {
							$pan_term           = get_term( $ancestor, $taxonomy );
							$breadcrumb_array[] = array(
								'name'  => $pan_term->name,
								'id'    => '',
								'url'   => get_term_link( $ancestor, $taxonomy ),
								'class' => '',
								'icon'  => '',
							);
						}
					}
					$term_url           = get_term_link( $term->term_id, $taxonomy );
					$breadcrumb_array[] = array(
						'name'  => $term->name,
						'id'    => '',
						'url'   => $term_url,
						'class' => '',
						'icon'  => '',
					);
				}
			}

			$breadcrumb_array[] = array(
				'name'  => get_the_title(),
				'id'    => '',
				'url'   => '',
				'class' => '',
				'icon'  => '',
			);
		} // is_single

		return apply_filters( 'vk_breadcrumb_array', $breadcrumb_array );

	}


	/**
	 * Get Bread Crumb
	 *
	 * @param array $options オプション配列。id_outer / class_outer / wrapper_attributes / class_inner / class_list / class_list_item を指定できる。
	 * @return string パンくずの HTML 文字列。
	 */
	public static function get_breadcrumb( $options = array() ) {

		if ( ! $options ) {
			$options = array(
				'id_outer'           => 'breadcrumb',
				'class_outer'        => 'breadcrumb',
				'wrapper_attributes' => '',
				'class_inner'        => 'container',
				'class_list'         => 'breadcrumb-list',
				'class_list_item'    => 'breadcrumb-list__item',
			);
		}

		$options = apply_filters( 'breadcrumb_html_options', $options );

		$breadcrumb_array = self::get_array();

		// Microdata
		// Refference http://schema.org/BreadcrumbList .
		/*-------------------------------------------*/
		$microdata_li        = ' itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"';
		$microdata_li_a      = ' itemprop="item"';
		$microdata_li_a_span = ' itemprop="name"';

		// スクリーンリーダー向けのランドマーク aria-label。翻訳可能かつフィルタで上書き可能にする。
		$nav_aria_label = apply_filters( 'vk_breadcrumb_nav_aria_label', __( 'Breadcrumb', 'lightning' ) );

		// <nav> は外側の div（非表示クラスが付く枠）の内側に置く。
		// 枠ごと隠れたときに、中身のない nav ランドマークだけが残らないようにするため。
		$breadcrumb_html = '<!-- [ #' . esc_attr( $options['class_outer'] ) . ' ] -->';
		if ( ! empty( $options['wrapper_attributes'] ) ) {
			$breadcrumb_html .= '<div id="' . esc_attr( $options['id_outer'] ) . '" ' . $options['wrapper_attributes'] . '>';
		} else {
			$breadcrumb_html .= '<div id="' . esc_attr( $options['id_outer'] ) . '" class="' . $options['class_outer'] . '">';
		}
		$breadcrumb_html .= '<nav aria-label="' . esc_attr( $nav_aria_label ) . '">';
		$breadcrumb_html .= '<div class="' . esc_attr( $options['class_inner'] ) . '">';
		$breadcrumb_html .= '<ol class="' . esc_attr( $options['class_list'] ) . '" itemscope itemtype="https://schema.org/BreadcrumbList">';

		// パンくず配列の最後のキーを取得。現在ページの判定に使う。
		$last_key = array_key_last( $breadcrumb_array );

		$position = 0;
		foreach ( $breadcrumb_array as $key => $value ) {

			$id = ( $value['id'] ) ? ' id="' . esc_attr( $value['id'] ) . '"' : '';

			$class = ' class="' . esc_attr( $options['class_list_item'] );
			if ( ! empty( $value['class'] ) ) {
				$class .= ' ' . esc_attr( $value['class'] );
			}
			$class .= '"';

			// 最後の要素（現在ページ）の <li> に aria-current="page" を付与する。
			// span ではなく li に付けることで、末端要素がリンク（<a>）を持つ場合でも
			// 「リンク内の非インタラクティブ子要素に aria-current」という構造を避けられ、
			// リンクの有無に関わらず「最後のリスト項目＝現在地」が構造的に明確になる。
			$aria_current_attr = ( $key === $last_key ) ? ' aria-current="page"' : '';

			$breadcrumb_html .= '<li' . $id . $class . $microdata_li . $aria_current_attr . '>';

			if ( $value['url'] ) {
				$breadcrumb_html .= '<a href="' . esc_url( $value['url'] ) . '"' . $microdata_li_a . '>';
			}

			if ( ! empty( $value['icon'] ) ) {
				$breadcrumb_html .= '<i class="' . $value['icon'] . '"></i>';
			}

			$breadclumb_post_title_allowed_html = array(
				'i'    => array(
					'id'    => array(),
					'class' => array(),
				),
				'ruby' => array(),
				'rt'   => array(),
			);

			$breadcrumb_html .= '<span' . $microdata_li_a_span . '>' . wp_kses( $value['name'], $breadclumb_post_title_allowed_html ) . '</span>';

			if ( $value['url'] ) {
				$breadcrumb_html .= '</a>';
			}
			++$position;
			$breadcrumb_html .= '<meta itemprop="position" content="' . $position . '" />';

			$breadcrumb_html .= '</li>';

		}

		$breadcrumb_html .= '</ol>';
		$breadcrumb_html .= '</div>';
		$breadcrumb_html .= '</nav>';
		$breadcrumb_html .= '</div>';
		$breadcrumb_html .= '<!-- [ /#' . esc_attr( $options['class_outer'] ) . ' ] -->';
		$breadcrumb_html  = apply_filters( 'vk_breadcrumb_html', $breadcrumb_html );
		return $breadcrumb_html;

	}


	/**
	 * Print Bread Crumb
	 *
	 * wp_kses でサニタイズして出力する。get_breadcrumb() で生成した <nav> / aria-current が
	 * 剥がされないよう、許可リストに nav タグと aria-current 属性を含めている。
	 */
	public static function the_breadcrumb() {
		$allowed_html = array(
			// ナビゲーションランドマーク。aria-label が剥がされないよう許可する。
			'nav'  => array(
				'id'         => array(),
				'class'      => array(),
				'aria-label' => array(),
			),
			'div'  => array(
				'id'        => array(),
				'class'     => array(),
				'itemprop'  => array(),
				'itemscope' => array(),
				'itemtype'  => array(),
			),
			'ol'   => array(
				'id'        => array(),
				'class'     => array(),
				'itemprop'  => array(),
				'itemscope' => array(),
				'itemtype'  => array(),
			),
			'li'   => array(
				'id'        => array(),
				'class'     => array(),
				'itemprop'  => array(),
				'itemscope' => array(),
				'itemtype'  => array(),
				// 現在ページを示す aria-current が剥がされないよう許可する。
				'aria-current' => array(),
			),
			'a'    => array(
				'id'       => array(),
				'class'    => array(),
				'href'     => array(),
				'target'   => array(),
				'itemprop' => array(),
			),
			'span' => array(
				'id'        => array(),
				'class'     => array(),
				'itemprop'  => array(),
				'itemscope' => array(),
				'itemtype'  => array(),
			),
			'i'    => array(
				'id'    => array(),
				'class' => array(),
			),
			'meta' => array(
				'itemprop' => array(),
				'content'  => array(),
			),
			'ruby' => array(),
			'rt'   => array(),
		);
		echo wp_kses( self::get_breadcrumb(), $allowed_html );
	}


	/**
	 * 構造化データ（JSON-LD）用のパンくず配列を返す
	 *
	 * get_array() のパンくず配列を、schema.org の BreadcrumbList の形に組み替える。
	 * name はタグを除き、文字参照（&amp; など）を元の文字に戻した文字列。
	 * item は文字参照を元の文字に戻し、esc_url_raw() で無害化した URL。
	 * どちらも HTML 用にはエスケープしていないため、HTML に出力するときは呼び出し側でエスケープすること。
	 *
	 * @return array $json_array BreadcrumbList 形式の配列。
	 */
	public static function get_scheme_json_array() {
		$array                  = self::get_array();
		$json_array             = array();
		$json_array['@context'] = 'https://schema.org';
		$json_array['@type']    = 'BreadcrumbList';
		$count                  = 1;
		foreach ( $array as $key => $value ) {
			$json_array['itemListElement'][ $key ]['@type']    = 'ListItem';
			$json_array['itemListElement'][ $key ]['position'] = $count;

			// 名前は HTML 用の書き方（保存時の &amp; や、表示用の整形でできる &#038; &#8217; など）で入ってくる。
			// JSON は HTML として読まれないため、文字参照のまま検索エンジンに伝わらないよう、タグを除いて元の文字に戻す。
			// タグの始まりにならない単独の <（例: x<5）は、タグを除く前に &lt; にしておく（そのままだと < 以降がタグとして消える）。
			$name = preg_replace( '/<(?![a-zA-Z\/!?])/', '&lt;', (string) $value['name'] );
			// タグを除いてから文字参照を戻すこと（逆にすると、文字参照で書かれた < > までタグとして消える）。
			$json_array['itemListElement'][ $key ]['name'] = html_entity_decode( wp_strip_all_tags( $name ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

			if ( ! empty( $value['url'] ) ) {
				// URL は esc_url() 済み（& が &#038;）で入ってくることがあるため、文字参照を元の文字に戻す。
				// esc_url() は & を &#038; に変えるため、& をそのまま残す esc_url_raw() で無害化する。
				// 文字参照で書いた javascript: なども弾けるよう、戻してから無害化すること（順番を逆にしない）。
				$json_array['itemListElement'][ $key ]['item'] = esc_url_raw( html_entity_decode( $value['url'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
			}
			$count++;
		}
		return $json_array;
	}

	/**
	 * 構造化データ（JSON-LD）の script 要素を出力する
	 *
	 * get_scheme_json_array() の配列を JSON にして、<script type="application/ld+json"> で出力する。
	 *
	 * @return void
	 */
	public static function the_scheme_script() {
		$json_array = self::get_scheme_json_array();
		echo '<script type="application/ld+json">' . "\n";
		// JSON_HEX_TAG を外さないこと。< と > を \u003C・\u003E にして、値の中の </script> や <!-- で script 要素が閉じたり壊れたりするのを防いでいる。
		echo wp_json_encode( $json_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG );
		echo '</script>';
	}
}
