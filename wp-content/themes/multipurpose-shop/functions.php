<?php
/*This file is part of multipurpose-shop, corporatesource child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/
/**
 * Activates default theme features
 */
function multipurpose_shop_theme_setup(){

	// Make theme available for translation.
	// Translations can be filed in the /languages/ directory.
	// uncomment to enable (remove the // before load_theme_textdomain )
	load_theme_textdomain( 'multipurpose-shop', get_stylesheet_directory() . '/languages' );

}

/**
 * Register our scripts (js/css)
 */
function multipurpose_shop_enqueue_child_styles() {
	
	$parent_style = 'corporatesource-style-parent'; 
	wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css' );
	
	wp_enqueue_style( 
		'corporatesource-style', 
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_style ),
		wp_get_theme()->get('Version') );
	wp_enqueue_style( 'multipurpose-shop-animation', get_stylesheet_directory_uri() . '/assets/woocommerce/animation.css' );	
	wp_enqueue_style( 'multipurpose-shop-woocommerce', get_stylesheet_directory_uri() . '/assets/woocommerce/woocommerce.css' );
	
	
	/*THEME JS */
	wp_enqueue_script( 'customselect', get_stylesheet_directory_uri (). '/assets/js/customselect.js', 0, '', true );
	wp_enqueue_script( 'multipurpose-shop-js', get_stylesheet_directory_uri( ).'/assets/js/multipurpose-shop.js', 0, '', true );
	
	}
add_action( 'wp_enqueue_scripts', 'multipurpose_shop_enqueue_child_styles',999 );




/**
 * Disable things from parent
 */
if( !function_exists('multipurpose_shop_disable_from_parent') ): 

	add_action('init','multipurpose_shop_disable_from_parent',50);
	function multipurpose_shop_disable_from_parent(){
		
		remove_action( 'corporatesource_footer_container', 'corporatesource_footer', 10 );
		if ( class_exists( 'WooCommerce' ) ) {
		remove_action( 'corporatesource_page_wrp_after', 'corporatesource_blog_widgets', 20 );
		}
		remove_action( 'corporatesource_blog_loop_footer', 'corporatesource_blog_loop_footer', 10 );
	}

endif;


/*-----------------------------------------
* FOOTER
*----------------------------------------*/

if( !function_exists('multipurpose_shop_footer') ){
	add_action( 'corporatesource_footer_container', 'multipurpose_shop_footer', 10 );
	/**
	*
	* @since 1.0.0
	*/
	function multipurpose_shop_footer(){
	?>
    
    <?php if ( is_active_sidebar( 'footer' ) ) { ?>
    <footer class="footer-main container-fluid no-padding">
        <!-- Container -->
        
        <div class="container">
         	<div class="row d-flex">
           <?php dynamic_sidebar( 'footer' ); ?>
           </div>
        </div><!-- Container -->
    </footer>
    <?php }?>
    
    <div class="bottom-footer  ">
		<!-- Container -->
		<div class="container">
       
            <?php if ( corporatesource_get_option('mailing_section_show') == 1 && count( corporatesource_get_option('mailing_section_content') ) > 0 )  : ?>
			<div class="row">
			 <div class="address-box">	
             
             
                <?php $i=0; foreach ( corporatesource_get_option('mailing_section_content') as $key => $text): $i++; ?>	
						
                  <div class="col-md-4 col-sm-4 col-xs-6 <?php echo ( $i == 2) ? 'address-content-1' : '';?>">
					<div class="address-content">
						<i class="fa <?php echo esc_attr( $text['icon'] );?>" aria-hidden="true"></i>
						<h6><?php echo esc_html( $text['title'] );?></h6>
						<p><?php echo esc_html( $text['text'] );?></p>
					</div>
				  </div>
                		
				<?php endforeach;?>			
                            
			</div>
            </div>
            <?php endif;?>
		</div><!-- Container /- -->
		<div class="footer-copyright">
			<p><?php  echo esc_html ( corporatesource_get_option('copyright_text') ); ?></p>
           		 
                        <a href="<?php /* translators:straing */ echo esc_url( esc_html__( 'https://wordpress.org/', 'multipurpose-shop' ) ); ?>"><?php /* translators:straing */  printf( esc_html__( 'Proudly powered by %s .', 'multipurpose-shop' ), 'WordPress' ); ?></a>
                        
                        <?php
                        printf( /* translators:straing */  esc_html__( 'Theme: %1$s by %2$s.', 'multipurpose-shop' ), 'multipurpose shop', '<a href="' . esc_url( __( 'https://edatastyle.com/', 'multipurpose-shop' ) ) . '" target="_blank">' . esc_html__( 'eDataStyle', 'multipurpose-shop' ) . '</a>' ); ?>
		</div>
	</div>
	<a href="#" id="ui-to-top" class="ui-to-top fa fa-angle-up active"></a>
    
    
    <?php
	}
}



/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	
	require get_stylesheet_directory() . '/inc/shop-helper.php';
	require get_stylesheet_directory() . '/inc/woocommerce.php';
	require get_stylesheet_directory() . '/inc/woocommerce-action.php';
	
}



require get_stylesheet_directory() . '/inc/core-theme.php';


if( !function_exists('multipurpose_shop_custom_header_image') ){
	
	add_filter( 'corporatesource_custom_header_args', 'multipurpose_shop_custom_header_image' );
	/**
	*
	* @since 1.0.0
	*/
	function multipurpose_shop_custom_header_image( $args ){
		$args['default-image'] = get_stylesheet_directory_uri().'/assets/image/custom-header.jpg';
		return $args;
	}
}



if( ! function_exists( 'multipurpose_blog_loop_footer' ) ) :

    /**
     * corporatesource_blog_loop_footer
     *
     * @return html
     */
    function multipurpose_blog_loop_footer(){
        $formats = get_post_format(get_the_ID());
		
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		
		if ( get_the_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() )
		);

		$posted_on = sprintf(
			/* translators: %s: post date. */
			esc_html_x( 'Posted on %s', 'post date', 'multipurpose-shop' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);
		?>
        <div class="post-modern__footer">
          <ul class="post-modern__meta">
            <li><i class="fa fa-clock-o" aria-hidden="true"></i>
              <?php echo $posted_on;?>
            </li>
            
            <?php
			// Hide category and tag text for pages.
		if ( 'post' === get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( esc_html__( ', ', 'multipurpose-shop' ) );
			if ( $categories_list ) {
				
				printf( ' <li><i class="fa fa-folder-o" aria-hidden="true"></i>' .$categories_list . ' </li>' ); // WPCS: XSS OK.
			}

		}
		?> 
        
			<?php
            if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
            echo '<li><span class="comments-link"><i class="fa fa-comment-o" aria-hidden="true"></i>';
            comments_popup_link(
            sprintf(
                wp_kses(
                    /* translators: %s: post title */
                    __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'multipurpose-shop' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                get_the_title()
            )
            );
            echo '</span></li>';
            }
            ?>
          </ul>
          <div class="post-modern__label">
            <a href="<?php echo  esc_url( get_permalink() ) ;?>">
           	  <?php echo esc_html__( 'Continue Reading', 'multipurpose-shop' ) ;?> <i class="fa fa-fw fa-angle-double-right"></i>
            </a>
          </div>
        </div>
        <?php
       

    }

add_action( 'corporatesource_blog_loop_footer', 'multipurpose_blog_loop_footer', 10 );
endif; 
