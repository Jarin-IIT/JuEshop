<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wpstream.net
 * @since      3.0.1
 *
 * @package    Wpstream
 * @subpackage Wpstream/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wpstream
 * @subpackage Wpstream/public
 * @author     wpstream <office@wpstream.net>
 */
class Wpstream_Public {

    
        
        /**
         * Store plugin main class to allow public access.
         *
         * @since    20180622
         * @var object      The main class.
         */
        public $main;
	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ,$plugin_main) {
                $this->main = $plugin_main;
		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    3.0.1
	 */
	public function enqueue_styles() {

                wp_enqueue_style('wpstream-style',          plugin_dir_url( __FILE__ ) .'/css/wpstream_style.css',array(), WPSTREAM_PLUGIN_VERSION, 'all' );
                wp_enqueue_style('video-js.min',            plugin_dir_url( __FILE__ ).'/css/video-js.min.css', array(), WPSTREAM_PLUGIN_VERSION, 'all');
                wp_enqueue_style('videojs-wpstream-player',    plugin_dir_url( __FILE__ ).'/css/videojs-wpstream.css', array(), WPSTREAM_PLUGIN_VERSION, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    3.0.1
	 */
	public function enqueue_scripts() {

                wp_enqueue_script('jquery');
                wp_enqueue_script('video.min',              'https://vjs.zencdn.net/7.8.4/video.js', WPSTREAM_PLUGIN_VERSION, false);
                wp_enqueue_script('youtube.min',            plugin_dir_url( __FILE__ ).'js/youtube.min.js',array('video.min'), WPSTREAM_PLUGIN_VERSION, false);
                wp_enqueue_script('videojs-vimeo.min',      plugin_dir_url( __FILE__ ).'js/videojs-vimeo.min.js',array('video.min'), WPSTREAM_PLUGIN_VERSION, false);    
                wp_enqueue_script('wpstream-player',        plugin_dir_url( __FILE__ ).'js/wpstream-player.js',array('video.min'), WPSTREAM_PLUGIN_VERSION, false);
                wp_enqueue_script('sldp-v2.12.1.min',       plugin_dir_url( __FILE__ ).'js/sldp-v2.12.1.min.js',array(), WPSTREAM_PLUGIN_VERSION, false);
    
                wp_localize_script('wpstream-player', 'wpstream_player_vars', 
                    array( 
                        'admin_url'             =>  get_admin_url(),
                        'chat_not_connected'    =>  esc_html__('Inactive Channel - Chat is disabled.','wpstream'),
                        'server_up'             =>  esc_html__('The live stream is paused and may resume shortly.','wpstream')
                        )
                );
                
                wp_enqueue_script( 'jquery-ui-autocomplete' );
                wp_enqueue_script( "jquery-effects-core");
                
                wp_register_script( 'sockjs-0.3.min', plugin_dir_url( __FILE__ ) . '/chat_lib/sockjs-0.3.min.js', array('jquery'), true );
                wp_register_script( 'emojione.min.js',plugin_dir_url( __FILE__ ). '/chat_lib/emojione.min.js', array('jquery'), true );
            
                wp_register_script( 'jquery.linkify.min.js', plugin_dir_url( __FILE__ ). '/chat_lib/jquery.linkify.min.js', array('jquery'), true );
                wp_register_script( 'ripples.min.js',plugin_dir_url( __FILE__ ). '/chat_lib/ripples.min.js', array('jquery'), true );
                wp_register_script( 'material.min.js"', plugin_dir_url( __FILE__ ). '/chat_lib/material.min.js', array('jquery'), true );
                wp_register_script( 'chat.js', plugin_dir_url( __FILE__ ). '/chat_lib/chat.js', array('jquery'), true );
              


                wp_register_style( 'chat.css',plugin_dir_url( __FILE__ ).'/chat_lib/css/chat.css', array(), '1.0', 'all');
                wp_register_style( 'ripples.css',plugin_dir_url( __FILE__ ).'/chat_lib/css/ripples.css', array(), '1.0', 'all');
                wp_register_style( 'emojione.min.css',plugin_dir_url( __FILE__ ).'/chat_lib/css/emojione.min.css', array(), '1.0', 'all');

                
                
                wp_enqueue_script('wpstream-start-streaming',   plugin_dir_url( __FILE__ ) .'js/start_streaming.js',array(),  WPSTREAM_PLUGIN_VERSION, true); 
                wp_localize_script('wpstream-start-streaming', 'wpstream_start_streaming_vars', 
                    array( 
                        'admin_url'             =>  get_admin_url(),
                        'loading_url'           =>  WPSTREAM_PLUGIN_DIR_URL.'/img/loading.gif',
                        'download_mess'         =>  esc_html__('Click to download!','wpstream'),
                        'uploading'             =>  esc_html('We are uploading your file.Do not close this window!','wpstream'),
                        'upload_complete2'      =>  esc_html('Upload Complete! You can upload another file!','wpstream'),
                        'not_accepted'          =>  esc_html('The file is not an accepted video format','wpstream'),
                        'upload_complete'       =>  esc_html('Upload Complete!','wpstream'),
                        'no_band'               =>  esc_html('Not enough bandwidth.','wpsteam'),
                        'no_band_no_store'      =>  esc_html('Not enough bandwidth or storage.','wpsteam')

                    ));
                
                
                wp_enqueue_style( 'wpstream_front_style', plugin_dir_url( __DIR__ ) . 'admin/css/wpstream-admin.css', array(), WPSTREAM_PLUGIN_VERSION, 'all' );
                       
	}
        
        
      
        /**
	 * add custom end points for woocomerce
	 *
	 * @since     3.0.1
	 * @return    nothing
        */
        public function wpstream_my_custom_endpoints() {
            add_rewrite_endpoint( 'video-list', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'event-list', EP_ROOT | EP_PAGES );
        }

        /**
	 * add custom query vars
	 *
	 * @since     3.0.1
	 * @return    nothing
        */
        public function wpstream_my_custom_query_vars( $vars ) {
            $vars[] = 'video-list';
            $vars[] = 'event-list';
            return $vars;
        }


        /**
	 * Hust flush rewrite rules
	 *
	 * @since     3.0.1
	 * 
	 */
        public function wpstream_custom_flush_rewrite_rules() {
            flush_rewrite_rules();
        }


        /**
	 * Add new sections in woocomerce account
	 *
	 * @since     3.0.1
	*/
        public function wpstream_custom_my_account_menu_items( $items ) {
            if(function_exists('wpstream_is_global_subscription') && wpstream_is_global_subscription()){
                $items = array(
                    'dashboard'         => __( 'Dashboard', 'woocommerce' ),
                    'orders'            => __( 'Orders', 'woocommerce' ),
                    'edit-address'      => __( 'Addresses', 'woocommerce' ),
                    'edit-account'      => __( 'Edit Account', 'woocommerce' ),
                    'customer-logout'   => __( 'Logout', 'woocommerce' ),
                );
            }else{
                $items = array(
                    'dashboard'         => __( 'Dashboard', 'woocommerce' ),
                    'orders'            => __( 'Orders', 'woocommerce' ),
                    'edit-address'      => __( 'Addresses', 'woocommerce' ),
                    'edit-account'      => __( 'Edit Account', 'woocommerce' ), 
                    'event-list'        => __( 'Events', 'wpstream' ),
                    'video-list'        => __( 'Videos', 'wpstream' ),
                    'customer-logout'   => __( 'Logout', 'woocommerce' ),
                );
            }
            return $items;
    }
    
    
    
        /**
	 * Add new endpoint
	 *
	 * @since     3.0.1
	*/
        public function wpstream_custom_endpoint_content_event_list() {
            include plugin_dir_path( __DIR__ ).'woocommerce/myaccount/event_list.php';
        }


        /**
	 * Add new endpoint
	 *
	 * @since     3.0.1
	*/
        public function wpstream_custom_endpoint_video_list() {
            include plugin_dir_path( __DIR__ ).'woocommerce/myaccount/video_list.php';
        }

        
        
        
     
        
        /**
	 * register shortcodes
	 *
	 * @since     3.0.1
         * 
	*/
        public function wpstream_shortcodes(){
            add_shortcode('wpstream_player',        array($this,'wpstream_insert_player_inpage_local') );
            add_shortcode('wpstream_list_products', array($this,'wpstream_list_products_function') );
            add_shortcode('wpstream_chat',          array($this,'wpstream_chat_function') );
            add_shortcode('wpstream_player_low_latency', array($this,'wpstream_insert_player_inpage_low_latency') );
            add_shortcode('wpstream_go_live', array($this,'wpstream_start_streaming_shortocde') );
            
            
            // register shortcodes for visual composer  
            if( function_exists('vc_map') ):
                
                vc_map(
                    array(
                       "name" => esc_html__( "WpStream Start Streaming Button","wpestate"),
                       "base" => "wpstream_go_live",
                       "class" => "",
                       "category" => esc_html__( 'WpStream','wpstream'),
                       'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
                       'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
                       'weight'=>100,
                       'icon'   =>'',
                       'description'=>esc_html__( 'Insert WpStream Start Streaming Button','wpstream'),
                       "params" => array(
                            array(
                                "type" => "textfield",
                                "holder" => "div",
                                "class" => "",
                                "heading" => esc_html__( "Product/Free Product Id","wpestate"),
                                "param_name" => "id",
                                "value" => "",
                                "description" => esc_html__( "If you leave this option blank we will stream on the first free/paid channel for this user","wpestate")
                            ),
                          

                       )
                    )
                );
            
            
            
                vc_map(
                    array(
                       "name" => esc_html__( "WpStream Chat - Beta Version","wpestate"),
                       "base" => "wpstream_chat",
                       "class" => "",
                       "category" => esc_html__( 'WpStream','wpstream'),
                       'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
                       'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
                       'weight'=>100,
                       'icon'   =>'',
                       'description'=>esc_html__( 'Insert WpStream Chat','wpstream'),
                       "params" => array(
                            array(
                                "type" => "textfield",
                                "holder" => "div",
                                "class" => "",
                                "heading" => esc_html__( "Live Stream Id","wpestate"),
                                "param_name" => "id",
                                "value" => "0",
                                "description" => esc_html__( "Add here the live stream id","wpestate")
                            ),

                       )
                    )
                );
            
            
       
                vc_map(
                    array(
                       "name" => esc_html__( "WpStream Player","wpestate"),
                       "base" => "wpstream_player",
                       "class" => "",
                       "category" => esc_html__( 'WpStream','wpstream'),
                       'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
                       'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
                       'weight'=>100,
                       'icon'   =>'',
                       'description'=>esc_html__( 'Insert WpStream Player','wpstream'),
                       "params" => array(
                            array(
                                "type" => "textfield",
                                "holder" => "div",
                                "class" => "",
                                "heading" => esc_html__( "Product/Free Product Id","wpestate"),
                                "param_name" => "id",
                                "value" => "0",
                                "description" => esc_html__( "Add here the live stream id or the video id","wpestate")
                            ),  
                           array(
                                "type" => "textfield",
                                "holder" => "div",
                                "class" => "",
                                "heading" => esc_html__( "User Id","wpestate"),
                                "param_name" => "user_id",
                                "value" => "",
                                "description" => esc_html__( "We will use the first channel of this user id(product id will be ignored.).","wpestate")
                            ),

                       )
                    )
                );

                
                vc_map(
                    array(
                       "name" => esc_html__( "WpStream Player - Low Latency - Private Beta / Requires Approval","wpestate"),
                       "base" => "wpstream_player_low_latency",
                       "class" => "",
                       "category" => esc_html__( 'WpStream','wpstream'),
                       'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
                       'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
                       'weight'=>100,
                       'icon'   =>'',
                       'description'=>esc_html__( 'Insert WpStream Player','wpstream'),
                       "params" => array(
                            array(
                                "type" => "textfield",
                                "holder" => "div",
                                "class" => "",
                                "heading" => esc_html__( "Product/Free Product Id","wpestate"),
                                "param_name" => "id",
                                "value" => "0",
                                "description" => esc_html__( "Add here the live stream id or the video id","wpestate")
                            ),
                             array(
                                "type" => "textfield",
                                "holder" => "div",
                                "class" => "",
                                "heading" => esc_html__( "User Id","wpestate"),
                                "param_name" => "user_id",
                                "value" => "",
                                "description" => esc_html__( "We will use the first channel of this user id(product id will be ignored.).","wpestate")
                            ),

                       )
                    )
                );


                $product_type=array(
                        '1' =>  __('Free Live Channel','wpstream'),
                        '2' =>  __('Free Video','wpstream')
                );

                vc_map(
                array(
                   "name" => esc_html__( "WpStream Products List","wpestate"),
                   "base" => "wpstream_list_products",
                   "class" => "",
                   "category" => esc_html__( 'WpStream','wpstream'),
                   'admin_enqueue_js' => array(get_template_directory_uri().'/vc_extend/bartag.js'),
                   'admin_enqueue_css' => array(get_template_directory_uri().'/vc_extend/bartag.css'),
                   'weight'=>100,
                   'icon'   =>'',
                   'description'=>esc_html__( ' List wpstream products','wpstream'),
                   "params" => array(
                        array(
                             "type" => "textfield",
                             "holder" => "div",
                             "class" => "",
                             "heading" => esc_html__( "Media number","wpestate"),
                             "param_name" => "media_number",
                             "value" => "",
                             "description" => esc_html__( "No of media ","wpestate")
                         ),

                        array(
                            "type" => "dropdown",
                            "holder" => "div",
                            "class" => "",
                            "heading" => esc_html__( "Product type","wpestate"),
                            "param_name" => "product_type",
                            "value" => $product_type,
                            "description" => esc_html__( "What type of products ","wpestate")
                        ),

                   )
                )
                );
            endif;
            
            
            // add shorcotes to editor interface
            if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
                return;
            }

            if (get_user_option('rich_editing') == 'true') {
                add_filter('mce_external_plugins', array( $this,'wpstream_add_plugin') );
                add_filter('mce_buttons_2', array($this,'wpstream_register_button') );    
            }
        }
        
              
        /**
	 * shortocode player
	 *
	 * @since     3.0.1
         * 
	*/
        public function wpstream_chat_function($attributes, $content = null){
            $product_id     =   '';
            $return_string  =   '';
            $attributes =   shortcode_atts( 
                array(
                    'id'                       => 0,
                ), $attributes) ;


            if ( isset($attributes['id']) ){
                $product_id=$attributes['id'];
            }
            
            
            $return_string.= '<div class="wpstream_plugin_chat_wrapper">';
            ob_start();
                $this->main->wpstream_player->wpstream_chat_wrapper($product_id);
                $return_string.= ob_get_contents();
            ob_end_clean(); 
            $return_string.='</div>'; 
            $this->main->wpstream_player->wpstream_connect_to_chat($product_id);
            
            return $return_string;
        }
           
        
        
        
        /**
	 * shortocode player
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_insert_player_inpage_local($attributes, $content = null){
                $product_id     =   '';
                $return_string  =   '';
                $attributes =   shortcode_atts( 
                    array(
                        'id'                       => 0,
                        'user_id'                  => 0,
                    ), $attributes) ;


                if ( isset($attributes['id']) ){
                    $product_id = intval( $attributes['id'] );
                }
                
                if ( isset($attributes['user_id']) ){
                    $user_id = intval( $attributes['user_id'] );
                }
  
                if(intval($product_id)==0 && $user_id!=0 ){
                    $product_id= $this->main->wpstream_player_retrive_first_id($user_id);
                }
                
                ob_start();
                $this->main->wpstream_player->wpstream_video_player_shortcode($product_id);
                $return_string= ob_get_contents();
                ob_end_clean(); 

                return $return_string;
        }

          
        /**
	 * shortocode function for start streaming
	 *
	 * @since     3.7
         * 
	*/
        
        
        public function wpstream_start_streaming_shortocde($attributes, $content = null){
                $product_id     =   '';
                $return_string  =   '';
                $attributes =   shortcode_atts( 
                    array(
                        'id'                       => 0,
                    ), $attributes) ;


                if ( isset($attributes['id']) ){
                    $product_id=intval($attributes['id']);
                }
                

                ob_start();
                    global $wpstream_plugin;
                    $wpstream_plugin->wpstream_live_stream_unit_wrapper(   $product_id,'front' );
                    $return_string= ob_get_contents();
                ob_end_clean(); 

                return $return_string;
        }

        
        
        /**
	 * shortocode player low latency
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_insert_player_inpage_low_latency($attributes, $content = null){
                $product_id     =   '';
                $return_string  =   '';
                $attributes =   shortcode_atts( 
                    array(
                        'id'                       => 0,
                         'user_id'                  => 0,
                    ), $attributes) ;


                if ( isset($attributes['id']) ){
                    $product_id=$attributes['id'];
                }
                
                   
                if ( isset($attributes['user_id']) ){
                    $user_id = intval( $attributes['user_id'] );
                }

                
                if(intval($product_id)==0 && $user_id!=0){
                    $product_id= $this->main->wpstream_player_retrive_first_id($user_id);
                }
                
                ob_start();
                $this->main->wpstream_player->wpstream_video_player_shortcode_low_latency($product_id);
                $return_string= ob_get_contents();
                ob_end_clean(); 

                return $return_string;
        }

        
        
        
        
        /**
	 * list products - shortcode function
	 *
	 * @since     3.0.1
         * 
	*/
        
        public function wpstream_list_products_function($atts, $content=null){

                $media_number     = "";  
                $product_type     = ""; 
                $attributes = shortcode_atts(
                        array(
                                'media_number' =>   '4',
                                'product_type' =>   __('Free Live Channel','wpstream'),

                        ), $atts);

                if ( isset($attributes['media_number']) ){
                    $media_number=$attributes['media_number'];
                }

                if ( isset($attributes['product_type']) ){
                    $product_type=$attributes['product_type'];
                }

                if($product_type== __('Free Live Channel','wpstream') ){
                    $product_type=1;
                }else{
                    $product_type=2;
                }

                $return_string=""; 



                $args = array(
                    'post_type'      => 'wpstream_product',
                    'post_status'    => 'publish',
                    'meta_query'     =>array(
                                        array(
                                        'key'      => 'wpstream_product_type',
                                        'value'    => $product_type,
                                        'compare'  => '=',
                                        ),
                        ),
                    'posts_per_page' =>$media_number,
                    'page'          => 1
                );

              
                $media_list= new WP_Query($args);

                if($product_type==1){
                    $see_product= __('See Free Live Chanel','wpstream');
                }else{
                    $see_product =_('See Free Video','wpstream');
                }



                while($media_list->have_posts()):$media_list->the_post();
                    $return_string.='<div class="wpstream_product_unit">'
                    .'<div class="product_image" style="background-image:url('.wp_get_attachment_thumb_url(get_post_thumbnail_id()).')"></div>'
                    .'<a href="'.get_permalink().'" class="product_title" >'.get_the_title().'</a>'
                    .'<a href="'.get_permalink().'"class="see_product">'.$see_product.'</a>'
                    .'</div>';
                endwhile;

                wp_reset_postdata();
                wp_reset_query();


                return   '<div class="shortcode_list_wrapper">'.$return_string.'</div>';

        }

        
        
        /**
	 * register shortcodes - add buttons in js
	 *
	 * @since     3.0.1
         * 
	*/
        
        public function wpstream_add_plugin($plugin_array) {   
            $plugin_array['wpstream_player']                = plugin_dir_url( __FILE__ ). '/js/shortcodes.js';
            $plugin_array['wpstream_list_products']         = plugin_dir_url( __FILE__ ). '/js/shortcodes.js';
            return $plugin_array;
        }
         
        /**
	 * register shortcodes - add buttons
	 *
	 * @since     3.0.1
         * 
	*/
        public function wpstream_register_button($buttons) {
            array_push($buttons, "|", "wpstream_player");
            array_push($buttons, "|", "wpstream_list_products");    
            return $buttons;
        }


        
        /**
	 * wpstream cors
	 *
	 * @since     3.0.1
         * 
	*/
        
        public function wpstream_cors_check_and_response(){
            if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
                header('Access-Control-Allow-Methods: POST, GET');
                header('Access-Control-Allow-Headers: Authorization');
                header('Access-Control-Max-Age: 1');  //1728000
                header("Content-Length: 0");
                header("Content-Type: text/plain charset=UTF-8");
                exit(0);
            }
        }
        
        /**
	 * set user cookie
	 *
	 * @since     3.0.1
         * 
	*/

        public function wpstream_set_cookies(){
            $local_event_options =   get_option('wpstream_user_streaming_global_channel_options') ;

            if(isset($local_event_options['ses_encrypt']) && intval($local_event_options['ses_encrypt'])==1 )    {  
            
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                if( !isset( $_REQUEST[ 'keys' ]) && !isset( $_REQUEST[ 'keys2' ]) ) {

                    if( !isset($_SESSION['wpstream_id']) ){
                       // print 'we are setting ' ;
                        $_SESSION['wpstream_id']= uniqid();
                    }
                }
            }
        }
        
        /**
	 * get key for live stream
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_live_streaming_key(){
            
            $local_event_options =   get_option('wpstream_user_streaming_global_channel_options') ;
           
            if( isset( $_REQUEST[ 'keys' ]) && $_REQUEST[ 'keys' ]!=''  ) {

                if(isset($local_event_options['ses_encrypt']) && intval($local_event_options['ses_encrypt'])==1 )    {  
                    if( !isset( $_SESSION['wpstream_id'] ) ){
                     session_write_close ();
                        return ('no session');
                    }
                }
        
                
                $streamname_received    =   esc_html($_REQUEST[ 'keys' ]);
                $stream_key_array       =   explode('-', $streamname_received);
             
                $streamname             =   $stream_key_array[0];
                $current_user           =   wp_get_current_user();        
              
                $event_list_free_posts =    get_transient(  'free_event_streamName_'.$streamname ) ;
                if ( false === $event_list_free_posts ) {   
              
                    $args_free = array(
                        'posts_per_page'    => -1,
                        'post_type'         => 'wpstream_product',
                        'post_status'       => 'publish',
                        'meta_query'        =>      array(
                                                        array(
                                                        'key'     => 'streamName',
                                                        'value'   => $streamname,
                                                        'compare' => '=',
                                                        )
                                                    ),
                        'fields'=>'ids',
                    );
                
                    $event_list_free = new WP_Query($args_free);
                    set_transient(  'free_event_streamName_'.$streamname, $event_list_free->posts ,600);
                }

                if ( !empty($event_list_free_posts )  ){
                    ////////////////////////////////////////////////////////////   
                    // when we have a free event
                    ////////////////////////////////////////////////////////////
                    $the_id                     =   $event_list_free_posts[0];
                    $show_id                    =   $the_id;  
                    $get_key                    =   $this->wpstream_get_encryption_key_remonting($show_id,$streamname_received);
                    print $get_key;
                    die();
                        
                        
                }else{
                    ////////////////////////////////////////////////////////////
                    //  this is for paid products
                    ////////////////////////////////////////////////////////////
                    
                    if ( is_user_logged_in() && intval($current_user->ID)!=0 ) {  

                            $event_list_paid_posts  =    get_transient(  'paid_event_streamName_'.$streamname ) ;
                         
                        
                        
                            if ( false === $event_list_paid_posts ) {  
                                $args = array(
                                    'posts_per_page'    => -1,
                                    'post_type'         => 'product',
                                    'post_status'       => 'publish',
                                    'meta_query' => array(
                                        array(
                                                'key'     => 'streamName',
                                                'value'   => $streamname,
                                                'compare' => '=',
                                        ),
                                    ),
                                    'tax_query'         => array(
                                                'relation'  => 'AND',
                                                array(
                                                    'taxonomy'  =>  'product_type',
                                                    'field'     =>  'slug',
                                                    'terms'     =>  array('live_stream','subscription')
                                                )
                                            ),
                                     'fields'=>'ids',
                                );


                                $event_list = new WP_Query($args);
                                $event_list_paid_posts = $event_list->posts;
                              
                                set_transient(  'paid_event_streamName_'.$streamname, $event_list->posts ,600);
                            }
                         
                            if ( !empty($event_list_paid_posts )  ){
                             
                                $the_id     =    $event_list_paid_posts[0];
                                $show_id    =   $the_id;


                                $is_valid_subscription=0;
                                if(class_exists ('WC_Subscription')){
                                    $is_valid_subscription = wcs_user_has_subscription( $current_user->ID, $show_id ,'active');
                                }


                                if(function_exists('wpstream_check_global_subscription_model')){
                                    if( wpstream_check_global_subscription_model() ){
                                        $is_valid_subscription=1;// this is global subscription
                                    }
                                }


                                if( wc_customer_bought_product( $current_user->email, $current_user->ID, $show_id) || $is_valid_subscription==1 ){     
                                    $get_key = $this->wpstream_get_encryption_key_remonting($show_id,$streamname_received);                            
                                    print $get_key;
                                    die();

                                }else{
                                    exit('live - no ticket ');
                                }

                            } else{
                                exit('live - no event');
                            }

                        }else{
                            exit('live - user not log or awserr');
                        }
                        
                }
                exit('no free or paid event');
                
            }else{
                return;
            }

        }
         
         
         
             
         /**
	 * get remote key for live
	 *
	 * @since     3.0.1
         * 
	*/
         
        public function wpstream_get_encryption_key_remonting ($show_id,$streamname_received){

              if ( false === ( $get_key = get_transient( $show_id.'_api20_streamName' ) ) ) {           
                    
                    $url = get_post_meta($show_id,'hlsKeyRetrievalUrl',true).'/'.$streamname_received;
                   
                    $get= wp_remote_get( $url );

                    if(is_array($get)){
                        $get_key = $get['body'];
                    }else{
                       $get_key='';
                    }
                    
                    set_transient(  $show_id.'_api20_streamName', $get_key, 30 );
                }
                return $get_key;
        }

         
         
         /**
	 * get key for 3rdparty
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_live_streaming_key_for_3rdparty(){
   
            if( isset( $_REQUEST[ 'thirdkeys' ]) && $_REQUEST[ 'thirdkeys' ]!='' ) {
            
                $thirdkeys         =   esc_html($_REQUEST[ 'thirdkeys' ]);
              
                //live_event_carnat2
                
                $args = array(
                    'post_type'      => array('product','wpstream_product'),
                    'post_status'    => 'publish',
                    'meta_query'     =>array(
                                        array(
                                        'key'      => 'live_event_carnat2',
                                        'value'    => $thirdkeys,
                                        'compare'  => '=',
                                        ),
                        ),
                    
                  
                );

         
                $media_list= new WP_Query($args);
                if($media_list->have_posts()){
                    while($media_list->have_posts()):$media_list->the_post();
                
                        $media_id       =   get_the_ID();
                        $replay_array   =   array(
                           // '', // fb will be here
                            stripslashes( get_post_meta($media_id,'wpstream_youtube_rtmp',true )),
                            stripslashes( get_post_meta($media_id,'wpstream_twich_rtmp',true) ),
                        );
                        
                        $reply_final=array('rtmp_urls'=>$replay_array);
                        header('Content-Type: application/json;charset=utf-8');
                        print json_encode($reply_final,JSON_UNESCAPED_SLASHES);
                        die();
                        
                        
                    endwhile;
                }else{
                    print'{}';
                    die('');
                }
                
            }

         }
        
          
         
         /**
	 * get key for vod
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_live_streaming_key_vod(){
   
            if( isset( $_REQUEST[ 'keys2' ]) && $_REQUEST[ 'keys2' ]!='' ) {
                global $wp_query; 
                $current_user   =   wp_get_current_user();
                

                $keys2        =   esc_html($_REQUEST[ 'keys2' ]);
               
         
                $keys2= ltrim($keys2,"/");
                $keys2= rtrim($keys2,"/");
                $keys2_array=explode('/',$keys2);
                
                
                $folder=$keys2_array[0];
                $movie=$keys2_array[1];
                
               
                $free_args = array(
                    'posts_per_page'    => -1,
                    'post_type'         => 'wpstream_product',
                    'meta_query' => array(
                        array(
                            'key'     => 'wpstream_free_video',
                            'value'   => $movie,
                            'compare' => '=',
                        ),
                    )
                );


                $free_video_list = new WP_Query($free_args);

                if ($free_video_list->have_posts() ){
                        $get_key = $this->wpstream_get_vod_key($folder.'/'.$movie);
                        echo ($get_key);
                        die();
                }else{
                    if ( is_user_logged_in() && intval($current_user->ID)!=0 ) {  

                        $args = array(
                            'posts_per_page'    => -1,
                            'post_type'         => 'product',
                            'meta_query' => array(
                                array(
                                        'key'     => '_movie_url',
                                        'value'   => $movie,
                                        'compare' => '=',
                                ),
                            ),
                            'tax_query'         => array(
                                        'relation'  => 'AND',
                                        array(
                                            'taxonomy'  =>  'product_type',
                                            'field'     =>  'slug',
                                            'terms'     =>  array('video_on_demand','subscription')
                                        )
                                ),

                        );


                        $video_list = new WP_Query($args);

                        $video_id   =0;
                        $ticket_flag=0;
                        if ($video_list->have_posts() ){
                            while ( $video_list->have_posts() ): 
                                $video_list->the_post(); 
                                $video_id     =   get_the_ID();

                                $show_id='';
                                $is_valid_subscription=0;
                                if(class_exists ('WC_Subscription')){
                             
                                    $is_valid_subscription = wcs_user_has_subscription( $current_user->ID, $show_id ,'active');
                                }

                                if(function_exists('wpstream_check_global_subscription_model')){
                                    if( wpstream_check_global_subscription_model() ){
                                        $is_valid_subscription=1;// this is global subscription
                                    }
                                }


                                if( wc_customer_bought_product( $current_user->email, $current_user->ID, $video_id) || 
                                        $is_valid_subscription==1 ){  
                                    
                                            $get_key = $this->wpstream_get_vod_key($folder.'/'.$movie);
                                            echo ($get_key);
                                            exit();
                                }else{
                                    $ticket_flag=1;
                                    exit('no ticket loop'.$current_user->ID.'/'.$video_id.'/'.$is_valid_subscription );
                                }
                            endwhile;

                            if($ticket_flag==0){
                                exit($current_user->email.'no ticket ukpt'.$current_user->ID.'.'.$video_id);
                            }

                        }else{
                            exit('no video found :'.$movie);
                        }

                    }else{
                        exit('user not log');
                    }

                }

            }

         }
         
         
          
         /**
	 * request key for vod from wpstream
	 *
	 * @since     3.0.1
         * 
	*/
        
         
        public function wpstream_get_vod_key($filename){   
           global $wpstream_plugin;
            $vod_key = get_transient("vod_key".$filename);
            if(false===$vod_key){
                $token  = $wpstream_plugin->wpstream_live_connection->wpstream_get_token();
                $domain = parse_url ( get_site_url() );

                $values_array=array(
                    "filename"           =>  $filename,
                );
                $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/uservodkey/get/?access_token=".$token;


                $arguments = array(
                    'method'        => 'GET',
                    'timeout'       => 45,
                    'redirection'   => 5,
                    'httpversion'   => '1.0',
                    'blocking'      => true,
                    'headers'       => array(),
                    'body'          => $values_array,
                    'cookies'       => array()
                );
                $response       = wp_remote_post($url,$arguments);
                $received_data  = json_decode( wp_remote_retrieve_body($response) ,true);


                if( isset($response['response']['code']) && $response['response']['code']=='200'){
                    set_transient("vod_key".$filename,$received_data,120);
                    return ($received_data);
                }else{     
                    return 'failed connection';
                }
            }else{
                return $vod_key;
            }

        }
        
        
        
        
        
        
        
        
         
        /**
	 * wrapper start around woo
	 *
	 * @since     3.0.1
         * 
	*/
        
        
        public function wpstream_non_image_content_wrapper_start() {
            if ( is_user_logged_in() ) {
                global $product;
                $current_user   =   wp_get_current_user();
                $product        =   wc_get_product();

                if($product){
                    $product_id = $product->get_id();
                    if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id) ){
                        echo '<div id="wpstream_product_wrap">';
                    }else{
                        echo '<div id="wpstream_product_wrap_no_buy">';
                    }
                }
            }

        }

        /**
	 * wrapper end around woo
	 *
	 * @since     3.0.1
         * 
	*/
        

        function wpstream_non_image_content_wrapper_end() { 
           // echo '</div>';
        }
}
