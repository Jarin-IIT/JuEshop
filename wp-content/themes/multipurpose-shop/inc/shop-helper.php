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

remove_action( 'woocommerce_before_main_content','woocommerce_breadcrumb',20 );
add_action( 'corporatesource_hero_block','woocommerce_breadcrumb',31 );

remove_action( 'woocommerce_single_product_summary','woocommerce_template_single_title',5 );
remove_action( 'woocommerce_single_product_summary','woocommerce_template_single_rating',10 );

add_action( 'corporatesource_hero_block','woocommerce_template_single_rating',32 );


remove_action( 'woocommerce_cart_collaterals','woocommerce_cross_sell_display' );

add_action( 'multipurpose_shop_cross_sell_display','woocommerce_cross_sell_display',20 );


/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function multipurpose_shop_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'WooCommerce Sidebar', 'multipurpose-shop' ),
		'id'            => 'woocommerce',
		'description'   => esc_html__( 'Add WooCmmerce widgets here.', 'multipurpose-shop' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
	
	
}
add_action( 'widgets_init', 'multipurpose_shop_widgets_init' );


if( !function_exists('multipurpose_shop_blog_widgets') ){
	
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	/**
	*
	* @since 1.0.0
	*/
	function multipurpose_shop_blog_widgets($layout = ''){
	
	  if( $layout == '' ){
		 if( is_page() ){
			  $layout = corporatesource_get_option('page_layout');
		 }else{
			 $layout = corporatesource_get_option('blog_layout');
		 }
	 }
		
	if( $layout == 'full-container' || $layout == 'no-sidebar'  ) { return false; }
	?>
    <div class="col-md-4 col-sm-4 <?php echo esc_attr($layout);?>">
    	<?php if( function_exists('is_shop') && is_shop() && is_active_sidebar( 'woocommerce' ) || function_exists('is_product_category') && is_product_category() && is_active_sidebar( 'woocommerce' ) ||  function_exists('is_product') && is_product() && is_active_sidebar( 'woocommerce' )  ) :?>
        		
            <aside id="secondary" class="widget-area sidebar">
            	<?php dynamic_sidebar( 'woocommerce' ); ?>
            </aside><!-- #secondary -->
            
        <?php else: ?>
    		<?php get_sidebar();?>
        <?php endif; ?>
    </div>	
    <?php
	
	}
}
add_action( 'corporatesource_page_wrp_after', 'multipurpose_shop_blog_widgets', 20 );


