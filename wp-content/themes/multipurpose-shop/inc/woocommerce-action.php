<?php
/**
 * Functions hooked to post page.
 *
 * @package Multipurpose Shop
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'multipurpose_shop_loop_shop_per_page' ) ) :
	/**
	 * Returns correct posts per page for the shop
	 *
	 * @since 1.0.0
	 */
	function multipurpose_shop_loop_shop_per_page() {
		
		$posts_per_page = ( isset( $_GET['products-per-page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['products-per-page'] ) ) : 12;

		if ( $posts_per_page == 'all' ) {
			$posts_per_page = wp_count_posts( 'product' )->publish;
		}
		
		return $posts_per_page;
	}
	add_filter( 'loop_shop_per_page', 'multipurpose_shop_loop_shop_per_page', 20 );
endif;


if ( ! function_exists( 'multipurpose_shop_woocommerce_show_page_title' ) ) :
	/**
	 * Returns shop title hide
	 *
	 * @since 1.0.0
	 */
	function multipurpose_shop_woocommerce_show_page_title() {
		return false;
	}
	add_filter( 'woocommerce_show_page_title', 'multipurpose_shop_woocommerce_show_page_title');
endif;


