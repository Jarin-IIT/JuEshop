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


if( !function_exists('multipurpose_shop_theme_pro_theme_name') ):
	function multipurpose_shop_theme_pro_theme_name( $arg ){
		return esc_html__( 'Shop Pro Theme', 'multipurpose-shop' );
	}
	add_filter( 'corporatesource_pro_theme_name','multipurpose_shop_theme_pro_theme_name');
endif;


if( !function_exists('multipurpose_shop_theme_pro_theme_url') ):
	function multipurpose_shop_theme_pro_theme_url( $arg ){
		return esc_url('https://edatastyle.com/product/multipurpose-shop-child-theme-of-corporatesource/');
	}
	add_filter( 'corporatesource_pro_theme_url','multipurpose_shop_theme_pro_theme_url');
endif;


if( !function_exists('multipurpose_pro_demo_url') ):
	function multipurpose_pro_demo_url( $arg ){
		return esc_url('https://eds.edatastyle.com/demo/mp-shop/');
	}
	add_filter( 'corporatesource_pro_demo_url','multipurpose_pro_demo_url');
endif;




if( !function_exists('multipurpose_shop_getting_started') ):
	function multipurpose_shop_getting_started( $arg ){
		return esc_attr__('About Multipurpose Shop', 'multipurpose-shop');
	}
	add_filter( 'corporatesource_getting_started','multipurpose_shop_getting_started');
endif;




if( ! function_exists( 'multipurpose_blog_read_more_link' ) ) :

	/**
	* Adds custom Read More.
	*
	*/
	function multipurpose_blog_read_more_link() {
	
		$text = esc_html__( 'Continue Reading ', 'multipurpose-shop' );
		
		return sprintf( '<div class="clearfix"><a class="theme-btn move-eff mt read-more" href="%1$s"><span>%2$s </span></a></div>',
			get_permalink( get_the_ID() ),
			esc_html($text)
		);
	}
	add_filter( 'the_content_more_link', 'multipurpose_blog_read_more_link' );
endif;



if( ! function_exists( 'multipurpose_shop_excerpt_more' ) ) :
	/**
	* Adds custom Read More Button to excerpt.
	*
	*/

	function multipurpose_shop_excerpt_more( $more ) {
		if ( ! is_admin() ) {
			$text = esc_html__( 'Continue Reading ', 'multipurpose-shop' );
			
			return sprintf( '<div class="clearfix"><a class="theme-btn move-eff mt read-more" href="%1$s"><span>%2$s</span></a></div>',
				get_permalink( get_the_ID() ),
				esc_html($text)
			);
		}else{
			return $more;
		}
	}
	add_filter( 'excerpt_more', 'multipurpose_shop_excerpt_more' );
endif;