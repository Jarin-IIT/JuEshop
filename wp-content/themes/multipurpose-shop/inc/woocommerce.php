<?php
/**
 * WooCommerce Compatibility File
 *
 * @link https://woocommerce.com/
 *
 * @package moduagency
 */

/**
 * WooCommerce setup function.
 *
 * @link https://docs.woocommerce.com/document/third-party-custom-theme-compatibility/
 * @link https://github.com/woocommerce/woocommerce/wiki/Enabling-product-gallery-features-(zoom,-swipe,-lightbox)-in-3.0.0
 *
 * @return void
 */
function multipurpose_shop_woocommerce_setup() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'multipurpose_shop_woocommerce_setup' );

/**
 * WooCommerce specific scripts & stylesheets.
 *
 * @return void
 */
function multipurpose_shop_woocommerce_scripts() {
	wp_enqueue_style( 'moduagency-woocommerce-style', get_template_directory_uri() . '/woocommerce.css' );

	$font_path   = WC()->plugin_url() . '/assets/fonts/';
	$inline_font = '@font-face {
			font-family: "star";
			src: url("' . $font_path . 'star.eot");
			src: url("' . $font_path . 'star.eot?#iefix") format("embedded-opentype"),
				url("' . $font_path . 'star.woff") format("woff"),
				url("' . $font_path . 'star.ttf") format("truetype"),
				url("' . $font_path . 'star.svg#star") format("svg");
			font-weight: normal;
			font-style: normal;
		}';

	wp_add_inline_style( 'multipurpose-woocommerce-style', $inline_font );
}
add_action( 'wp_enqueue_scripts', 'multipurpose_shop_woocommerce_scripts' );

/**
 * Disable the default WooCommerce stylesheet.
 *
 * Removing the default WooCommerce stylesheet and enqueing your own will
 * protect you during WooCommerce core updates.
 *
 * @link https://docs.woocommerce.com/document/disable-the-default-stylesheet/
 */
//add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Add 'woocommerce-active' class to the body tag.
 *
 * @param  array $classes CSS classes applied to the body tag.
 * @return array $classes modified to include 'woocommerce-active' class.
 */
function multipurpose_shop_woocommerce_active_body_class( $classes ) {
	$classes[] = 'woocommerce-active';

	return $classes;
}
add_filter( 'body_class', 'multipurpose_shop_woocommerce_active_body_class' );

/**
 * Products per page.
 *
 * @return integer number of products.
 */
function multipurpose_shop_woocommerce_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'multipurpose_shop_woocommerce_products_per_page' );

/**
 * Product gallery thumnbail columns.
 *
 * @return integer number of columns.
 */
function multipurpose_shop_woocommerce_thumbnail_columns() {
	return 4;
}
add_filter( 'woocommerce_product_thumbnails_columns', 'multipurpose_shop_woocommerce_thumbnail_columns' );

/**
 * Default loop columns on product archives.
 *
 * @return integer products per row.
 */
function multipurpose_shop_woocommerce_loop_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'multipurpose_shop_woocommerce_loop_columns' );

/**
 * Related Products Args.
 *
 * @param array $args related products args.
 * @return array $args related products args.
 */
function multipurpose_shop_woocommerce_related_products_args( $args ) {
	$defaults = array(
		'posts_per_page' => 3,
		'columns'        => 3,
	);

	$args = wp_parse_args( $defaults, $args );

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'multipurpose_shop_woocommerce_related_products_args' );

if ( ! function_exists( 'multipurpose_shop_woocommerce_product_columns_wrapper' ) ) {
	/**
	 * Product columns wrapper.
	 *
	 * @return  void
	 */
	function multipurpose_shop_woocommerce_product_columns_wrapper() {
		$columns = multipurpose_shop_woocommerce_loop_columns();
		echo '<div class="shop-product-wrapper desktop columns-' . absint( $columns ) . '">';
	}
}
add_action( 'woocommerce_before_shop_loop', 'multipurpose_shop_woocommerce_product_columns_wrapper', 40 );

if ( ! function_exists( 'multipurpose_shop_woocommerce_product_columns_wrapper_close' ) ) {
	/**
	 * Product columns wrapper close.
	 *
	 * @return  void
	 */
	function multipurpose_shop_woocommerce_product_columns_wrapper_close() {
		echo '</div>';
	}
}
add_action( 'woocommerce_after_shop_loop', 'multipurpose_shop_woocommerce_product_columns_wrapper_close', 40 );

/**
 * Remove default WooCommerce wrapper.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

if ( ! function_exists( 'multipurpose_shop_woocommerce_wrapper_before' ) ) {
	/**
	 * Before Content.
	 *
	 * Wraps all WooCommerce content in wrappers which match the theme markup.
	 *
	 * @return void
	 */
	function multipurpose_shop_woocommerce_wrapper_before() {
		/**
		* Hook - multipurpose_shop_page_container_start.
		*
		* @hooked multipurpose_shop_page_wrp_container_start - 10
		* @hooked multipurpose_shop_page_column - 20
		*/
		do_action( 'corporatesource_page_wrp_before', 'full-container' );
	}
}
add_action( 'woocommerce_before_main_content', 'multipurpose_shop_woocommerce_wrapper_before' );

if ( ! function_exists( 'multipurpose_shop_woocommerce_wrapper_after' ) ) {
	/**
	 * After Content.
	 *
	 * Closes the wrapping divs.
	 *
	 * @return void
	 */
	function multipurpose_shop_woocommerce_wrapper_after() {
		/**
		* Hook - multipurpose_shop_page_container_end.
		*
		* @hooked multipurpose_shop_page_column_end - 10
		* @hooked multipurpose_shop_page_sidebar - 20
		* @hooked multipurpose_shop_page_wrp_container_end - 30
		*/
		do_action( 'corporatesource_page_wrp_after', 'full-container' );
	}
}
add_action( 'woocommerce_after_main_content', 'multipurpose_shop_woocommerce_wrapper_after' );


/* 
 TOOL BAR
*/

remove_action('woocommerce_before_shop_loop','woocommerce_result_count',20);

if ( ! function_exists( 'multipurpose_shop_header_toolbar_start' ) ) {
	/**
	 * Insert the opening anchor tag for products in the loop.
	 */
	function multipurpose_shop_header_toolbar_start() {
		echo '<div class="multipurpose-shop-toolbar clearfix">';
	}
	
	add_action('woocommerce_before_shop_loop','multipurpose_shop_header_toolbar_start',20);
}

if ( ! function_exists( 'multipurpose_shop_grid_list_buttons' ) ) :
	/**
	 * Insert the opening anchor tag for products in the loop.
	 */
function multipurpose_shop_grid_list_buttons() {

	// Titles
	$grid_view = esc_html__( 'Grid view', 'multipurpose-shop' );
	$list_view = esc_html__( 'List view', 'multipurpose-shop' );

	// Active class
	$grid = 'active ';
	$list = '';

	$output = sprintf( '<nav class="multipurpose-shop-grid-list"><a href="#" id="multipurpose-shop-grid" title="%1$s" class="%2$sgrid-btn"><i class="fa fa-th-large" aria-hidden="true"></i></a><a href="#" id="multipurpose-shop-list" title="%3$s" class="%4$slist-btn"><i class="fa fa-list" aria-hidden="true"></i></a></nav>', esc_html( $grid_view ), esc_attr( $grid ), esc_html( $list_view ), esc_attr( $list ) );

	echo wp_kses_post( apply_filters( 'multipurpose_shop_grid_list_buttons_output', $output ) );
}
add_action('woocommerce_before_shop_loop','multipurpose_shop_grid_list_buttons',25);
endif;


function multipurpose_shop_result_count() {
	get_template_part( 'woocommerce/result-count' );
}
add_action('woocommerce_before_shop_loop','multipurpose_shop_result_count',30);


if ( ! function_exists( 'multipurpose_shop_header_toolbar_end' ) ) {
	/**
	 * Insert the opening anchor tag for products in the loop.
	 */
	function multipurpose_shop_header_toolbar_end() {
		echo '<div class="clearfix"></div></div>';
	}
	
	add_action('woocommerce_before_shop_loop','multipurpose_shop_header_toolbar_end',30);
}



/* 
 BASE ON LOOP PRODUCT ( content-product.php )
*/

if ( ! function_exists( 'multipurpose_shop_woocommerce_template_loop_product_link_open' ) ) {
	/**
	 * Insert the opening anchor tag for products in the loop.
	 */
	function multipurpose_shop_woocommerce_template_loop_product_link_open() {
		echo '<div class="product-wrap">';
	}
	remove_action('woocommerce_before_shop_loop_item','woocommerce_template_loop_product_link_open',10);
	add_action('woocommerce_before_shop_loop_item','multipurpose_shop_woocommerce_template_loop_product_link_open',40);
}

if ( ! function_exists( 'multipurpose_shop_woocommerce_template_loop_product_link_close' ) ) {
	/**
	 * Insert the opening anchor tag for products in the loop.
	 */
	function multipurpose_shop_woocommerce_template_loop_product_link_close() {
		echo '</div> ';
	}
	remove_action('woocommerce_after_shop_loop_item','woocommerce_template_loop_product_link_close',10);
	add_action('woocommerce_after_shop_loop_item','multipurpose_shop_woocommerce_template_loop_product_link_close',10);
}

if ( ! function_exists( 'multipurpose_shop_template_loop_product_title' ) ) {

	/**
	 * Show the product title in the product loop. By default this is an H2.
	 */
	function multipurpose_shop_template_loop_product_title() {
		
		echo '<h5 class="theme-loop-product__title"><a href="'.esc_url( get_the_permalink() ).'" rel="bookmark">' . get_the_title() . '</a></h5>';
	}
	
	remove_action( 'woocommerce_shop_loop_item_title','woocommerce_template_loop_product_title',10 );
	add_action( 'woocommerce_shop_loop_item_title','multipurpose_shop_template_loop_product_title',10 );
}


if ( ! function_exists( 'multipurpose_shop_woocommerce_template_loop_product_thumbnail' ) ) {

	/**
	 * Get the product thumbnail for the loop.
	 */
	function multipurpose_shop_woocommerce_template_loop_product_thumbnail() {
		global $product;
		$attachment_ids   = $product->get_gallery_image_ids();
		
		
		$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );
		echo '<div class="product-image">';
			 echo '<a href="' . esc_url( $link ) . '">';
			if( isset( $attachment_ids[0] ) && $attachment_ids[0] != "" ) {
			
				$img_tag = array(
				'class'         => 'woo-entry-image-secondary',
				'alt'           => get_the_title(),
				);
				
				echo '<figure class="hover_action">'. woocommerce_get_product_thumbnail() . wp_get_attachment_image( $attachment_ids[0], 'shop_catalog', '', $img_tag ) .'</figure>';	
				
			}else{
				echo '<figure>'.woocommerce_get_product_thumbnail().'</figure>';	
			}
			echo '</a>';
			
			
			if ( $price_html = $product->get_price_html() ) : 
				echo '<div class="product-price">'.$price_html.'</div>';
			endif;
			
			
		echo '</div>';
	}
	remove_action( 'woocommerce_before_shop_loop_item_title','woocommerce_template_loop_product_thumbnail',10 );
	add_action( 'woocommerce_before_shop_loop_item_title','multipurpose_shop_woocommerce_template_loop_product_thumbnail',10 );
	
	remove_action( 'woocommerce_after_shop_loop_item_title','woocommerce_template_loop_price',10 );
	
}

if( !function_exists( 'multipurpose_shop_list_view_container' ) && !function_exists('multipurpose_shop_list_view_container_end') ):

	function multipurpose_shop_list_view_container_start(){
		echo '<div class="multipurpose-shop-list-view-container">';
	}
	add_action( 'woocommerce_after_shop_loop_item_title','multipurpose_shop_list_view_container_start',80 );
	
	add_action( 'woocommerce_after_shop_loop_item_title','woocommerce_template_loop_price',82 );
	
	add_action( 'woocommerce_after_shop_loop_item_title','woocommerce_template_single_excerpt',84 );
	
	function multipurpose_shop_list_view_container_end(){
		echo '</div>';
	}
	add_action( 'woocommerce_after_shop_loop_item_title','multipurpose_shop_list_view_container_end',86 );
	
	
	
	
endif;

