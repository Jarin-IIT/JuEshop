<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-wpstream-player
 *
 * @author cretu
 */
class Wpstream_Player{
    public function __construct($plugin_main) {
        $this->main = $plugin_main;
          
        add_filter( 'the_content',array($this, 'wpstream_filter_the_title') );
        add_action( 'woocommerce_before_single_product', array($this,'wpstream_user_logged_in_product_already_bought') );
        
        add_action( 'wp_ajax_wpstream_player_check_status', array($this,'wpstream_player_check_status') );  
        add_action('wp_ajax_nopriv_wpstream_player_check_status', array($this,'wpstream_player_check_status'));
     
        
        add_action( 'wp_ajax_wpstream_force_clear_transient', array($this,'wpstream_force_clear_transient') );  
        add_action('wp_ajax_nopriv_wpstream_force_clear_transient', array($this,'wpstream_force_clear_transient'));
        
        
    }
    
    
        
    /**
    * clear status transients
    *
    * @author cretu
    */
    
    
    public function wpstream_force_clear_transient($event_id){
     
        $transient_name         =   'event_data_to_return_'.$event_id;
        delete_transient($transient_name);
        update_post_meta($event_id,'statsUrl','');
        update_post_meta($event_id,'chatUrl','');
        
    }
    
    
        
    /**
    * check player status
    *
    * @author cretu
    */
    
    public function wpstream_player_check_status(){
        $event_id                   =   intval($_POST['event_id']);
        $show_id=$event_id;
        $transient_name             =   'event_data_to_return_'.$event_id;
        $event_data_for_transient   =   get_transient( $transient_name );
       

        
        if ( false ===  $event_data_for_transient || $event_data_for_transient=='' ) { //ws || $hls_to_return==''
            
            $server_id  =   ''; // server id IS blank - need to retrive it
            $token      =   $this->main->wpstream_live_connection->wpstream_get_token();
           
            if($server_id==''){
                update_post_meta($show_id,'server_id', '' );
        
                $server_id =$this->main->wpstream_live_connection->retrive_server_id_based_on_show_id($show_id);
                
            }
            
    
                       
            update_post_meta($show_id,'server_id', $server_id );
            $values_array=array(
                "server_id"           =>  esc_html($server_id),
                'from_where'          =>  'wpstream_check_event_status_front_player'
            );
        
            if($server_id==''){
                $this->main->wpstream_live_connection->wpstream_reset_event_data($show_id);
                //print 'failed connection';
                return;
            }
   
       
            
            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/api20-livestrem/event_status/?access_token=".$token;
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

            $response       =   wp_remote_post($url,$arguments);           
            $received_data  =   wp_remote_retrieve_body($response);

            if( isset($response['response']['code']) && $response['response']['code']=='200'){
                $received_data_encoded=json_decode($received_data,true);
               
                if( isset($received_data_encoded['success']) && intval( $received_data_encoded['success'] ) ==1    ){
  
                    /*
                     *  we set transient and return the array saved
                     *  false if no data
                     * 
                     * */
                    $event_data_for_transient= $this->main->wpstream_live_connection->api20_wpstream_update_event($received_data_encoded,$show_id,$server_id);
                }else{
                    $this->main->wpstream_live_connection->wpstream_reset_event_data($show_id);
                }
                
            }else{    
                $this->main->wpstream_live_connection->wpstream_reset_event_data($show_id);
            }
            
            

            
            if ( false ===  $event_data_for_transient || $event_data_for_transient=='' ){
                //we are not live 
                $event_data_for_transient=array();
                $event_data_for_transient['hlsPlaybackUrl']='';
                 
            }
        }
        
        
    
       // 1==2 && 
        
        if(   $event_data_for_transient['hlsPlaybackUrl']!=''){
            echo json_encode(   array(
               
                    'started'               =>  'yes',
                    'server_id'             =>  $event_data_for_transient['server_id'],
                    'event_id'              =>  $event_id,
                    'event_uri'             =>  $event_data_for_transient['hlsPlaybackUrl'],
                    'live_conect_views'     =>  $event_data_for_transient['statsUrl'],
                    'chat_url'              =>  $event_data_for_transient['chatUrl'],
                                   
            ));
        }else{
            echo json_encode(   array(
                    'started'               =>  'no',
                    'server_id'             =>  $server_id,
                    'event_id'              =>  $event_id,
                    'event_uri'             =>  '',
                    'live_conect_views'     =>  '',
                    'chat_url'              =>  '',
                                   
            ));
            $this->wpstream_force_clear_transient($event_id);
        }
        die();
    }
    
    
    
    /**
    * Insert player in page
    *
    * @author cretu
    */
    public function wpstream_filter_the_title( $content   ) {
            if( is_singular('wpstream_product')){
                global $post;
                $args=array('id'=>$post->ID);
                $custom_content = $this->wpstream_insert_player_inpage($args);
                $content = '<div class="wpestream_inserted_player">'.$custom_content.'</div>'.$content;
                return $content;
            }else{
                return $content;
            }
    }
    
    /**
    * Insert player in page
    *
    * @author cretu
    */

    public function wpstream_insert_player_inpage($attributes, $content = null){
        $product_id     =   '';
        $return_string  =   '';
        $attributes =   shortcode_atts( 
            array(
                'id'                       => 0,
            ), $attributes) ;


        if ( isset($attributes['id']) ){
            $product_id=$attributes['id'];
        }
         
        if(intval($product_id)==0){
            $product_id= $this->wpstream_player_retrive_first_id();
        }

        ob_start();
     
        $this->wpstream_video_player_shortcode($product_id);
        $return_string= ob_get_contents();
        ob_end_clean(); 

        return $return_string;
    }

    
    
    
    /**
    * Video Player shortcode
    *
    * @author cretu
    */

    public function wpstream_video_player_shortcode($from_sh_id='') {

        if ( is_user_logged_in() ) {
            global $product;
            $current_user   =   wp_get_current_user();
            $product_id     =   intval($from_sh_id);


            if ( ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id) ) || get_post_type($product_id)=='wpstream_product' ){
                global $product;
                echo '<div class="wpstream_player_wrapper wpstream_player_shortcode"><div class="wpstream_player_container">';


                if( get_post_type($product_id) == 'wpstream_product' ){

                    $wpstream_product_type =    esc_html(get_post_meta($product_id, 'wpstream_product_type', true));
                    if($wpstream_product_type==1){
                        $this->wpstream_live_event_player($product_id);
                    } else if($wpstream_product_type==2 || $wpstream_product_type==3){
                        $this->wpstream_video_on_demand_player($product_id);
                    }

                }else{
                    $term_list                  =   wp_get_post_terms($product_id, 'product_type');
                    $is_subscription_live_event =   esc_html(get_post_meta($product_id,'_subscript_live_event',true));

                    if( $term_list[0]->name=='live_stream' || ( $term_list[0]->name=='subscription' && $is_subscription_live_event=='yes' ) ){
                        $this->wpstream_live_event_player($product_id);
                    }else if( $term_list[0]->name=='video_on_demand'  || ($term_list[0]->name=='subscription' && $is_subscription_live_event=='no' ) ){
                        $this->wpstream_video_on_demand_player($product_id);
                    }
                }


                echo '</div></div>';
            }else{
                
                if( get_post_type($product_id) == 'product' ){
                    echo '<div class="wpstream_player_wrapper wpstream_player_shortcode no_buy"><div class="wpstream_player_container">';
                    echo '<div class="wpstream_notice" style="background:#e16767;">'.esc_html__('You did not buy this product!','wpstream').'</div>';
                    echo '</div></div>';
                }
            }

         
        }else{
            
            $product_id     =   intval($from_sh_id);
            if( get_post_type($product_id) == 'wpstream_product' ){

                    $wpstream_product_type =    esc_html(get_post_meta($product_id, 'wpstream_product_type', true));
                    if($wpstream_product_type==1){
                        $this->wpstream_live_event_player($product_id);
                    } else if($wpstream_product_type==2 || $wpstream_product_type==3){
                        $this->wpstream_video_on_demand_player($product_id);
                    }

            }
        }
    }

    
    
    /**
    * Video Player shortcode - low latency
    *
    * @author cretu
    */

    public function wpstream_video_player_shortcode_low_latency($from_sh_id='') {

        if ( is_user_logged_in() ) {
            global $product;
            $current_user   =   wp_get_current_user();
            $product_id     =   intval($from_sh_id);


            if ( ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id) ) || get_post_type($product_id)=='wpstream_product' ){
                global $product;
                echo '<div class="wpstream_player_wrapper wpstream_player_shortcode"><div class="wpstream_player_container">';


                if( get_post_type($product_id) == 'wpstream_product' ){

                    $wpstream_product_type =    esc_html(get_post_meta($product_id, 'wpstream_product_type', true));
                    if($wpstream_product_type==1){
                        $this->wpstream_live_event_player_low_latency($product_id);
                    }

                }else{
                    $term_list                  =   wp_get_post_terms($product_id, 'product_type');
                    $is_subscription_live_event =   esc_html(get_post_meta($product_id,'_subscript_live_event',true));

                    if( $term_list[0]->name=='live_stream' || ( $term_list[0]->name=='subscription' && $is_subscription_live_event=='yes' ) ){
                        $this->wpstream_live_event_player_low_latency($product_id);
                    }
                }


                echo '</div></div>';
            }else{
                
                if( get_post_type($product_id) == 'product' ){
                    echo '<div class="wpstream_player_wrapper wpstream_player_shortcode no_buy"><div class="wpstream_player_container">';
                    echo '<div class="wpstream_notice" style="background:#e16767;">'.esc_html__('You did not buy this product!','wpstream').'</div>';
                    echo '</div></div>';
                }
            }

         
        }else{
            $product_id     =   intval($from_sh_id);
            if( get_post_type($product_id) == 'wpstream_product' ){

                    $wpstream_product_type =    esc_html(get_post_meta($product_id, 'wpstream_product_type', true));
                    if($wpstream_product_type==1){
                        $this->wpstream_live_event_player_low_latency($product_id);
                    } 
            }
        }
    }

    
    
    
    
    /**
    * Live Event Player
    *
    * @author cretu
    */
    function remove_http($url) {
        $disallowed = array('http://', 'https://');
        foreach($disallowed as $d) {
           if(strpos($url, $d) === 0) {
              return str_replace($d, '', $url);
           }
        }
        return $url;
    }
      
    /**
    * Get event settings
    *
    * @author cretu
    */
    
    function wpestream_return_event_settings($product_id){
            
        $local_event_options =   get_post_meta($product_id,'local_event_options',true);
        if(!is_array($local_event_options)){
            $local_event_options =   get_option('wpstream_user_streaming_global_channel_options') ;
        }
        
        return $local_event_options;
    }
    
    
    
    /**
    * Live Event Player
    *
    * @author cretu
    */
    
    function wpstream_live_event_player($product_id,$poster_show='',$use_chat=''){
            
            $event_settings         =   $this->wpestream_return_event_settings($product_id);
            $live_event_uri         =   get_post_meta($product_id,'live_event_uri',true); 
            $api20_event_id         =   get_post_meta($product_id,'server_id',true);
            
            $thumb_id               =   get_post_thumbnail_id($product_id);
            $thumb                  =   wp_get_attachment_image_src($thumb_id,'small');

            $chatUrl=   get_post_meta($product_id,'statsUrl',true);
            
         
            $live_event_uri_final   =   $this->wpstream_request_hls_player_dynamic('',$product_id);// buleala lu Gabi       
            $now                    =   time().rand(0,10);
            $live_conect_array      =   explode('live.streamer.wpstream.net',$live_event_uri_final);
            $live_conect_views      =   $live_conect_array[0].'live.streamer.wpstream.net';
            $live_conect_views      =   $this->remove_http($live_conect_views);
            $usernamestream         =   esc_html ( get_option('wpstream_api_username','') );
            $autoplay               =   'autoplay';
            
            if(isset($event_settings['autoplay']) && intval($event_settings['autoplay'])==0){
                $autoplay='no_autoplay';
            }
 
            echo '<div class="wpstream_live_player_wrapper function_wpstream_live_event_player" data-now="'.$now.'" data-me="'.esc_attr($usernamestream).'" data-product-id="'.$product_id.'" id="wpstream_live_player_wrapper'.$now.'" > ';
                    
                if( ( isset($event_settings['view_count'] ) && intval($event_settings['view_count'])==1 ) || !isset($event_settings['view_count']) ){
                    echo '<div id="wpestream_live_counting" class="wpestream_live_counting"></div>';
                }
                
                    $show_wpstream_not_live_mess=' style="display:none;" ';
                    if(trim($live_event_uri_final) ==''){
                        $show_wpstream_not_live_mess=''; 
                    }
            
                    
                    print '<div class="wpstream_not_live_mess " '.$show_wpstream_not_live_mess.' ><div class="wpstream_not_live_mess_back"></div><div class="wpstream_not_live_mess_mess">'.esc_html__('We are not live at this moment. Please check back later.','wpstream').'</div></div>';
                     
                    
                    $poster_data=' poster="'.$thumb[0].'" ';
                    if($poster_show=='no'){
                        $poster_data='';
                    }
                    
                    $is_muted='';
                    if( isset($event_settings['mute']) && intval($event_settings['mute'])==1){
                        $is_muted=' muted ';
                    }
                    
                   
                    
                    echo'
                    <video id="wpstream-video'.$now.'"     '.$poster_data.'  class="video-js vjs-default-skin  vjs-16-9 vjs-wpstream" playsinline="true" '.$is_muted.' controls data-autoplay='.$autoplay.'>
                    <source
                        src="'.$live_event_uri_final.'"
                        type="application/x-mpegURL">
                    </video>';

                    print '<script type="text/javascript">
                                //<![CDATA[
                                    jQuery(document).ready(function(){
                                        wpstream_player_initialize("'.$now.'","'.$live_event_uri_final.'","'.$live_conect_views.'","'.$autoplay.'");';
                                    print'});
                                //]]>
                            </script>';
                print '</div>';   
               
               
                if(trim($live_event_uri_final) ==''){
                    // $show_wpstream_not_live_mess=''; 
                }else{
                       print '<script type="text/javascript">
                            //<![CDATA[
                                jQuery(document).ready(function(){
                                    var player_wrapper =   jQuery(".wpstream_live_player_wrapper");
                                    wpstream_read_websocket_info("'.$product_id.'","wpstream_live_player_wrapper'.$now.'", player_wrapper ,"'.$chatUrl.'", "'.$live_event_uri_final.'");
                                });
                            //]]>
                        </script>';
                }
               
               
               if($use_chat=="yes"){
                    $this->wpstream_connect_to_chat($product_id);
               }
               
               usleep (10000);

        }



    /**
    * Live Event Player
    *
    * @author cretu
    */
    
    function wpstream_live_event_player_low_latency($product_id,$poster_show='',$use_chat=''){
            $event_settings         =   $this->wpestream_return_event_settings($product_id);
            $live_event_uri         =   get_post_meta($product_id,'live_event_uri',true); 
            $api20_event_id         =   get_post_meta($product_id,'server_id',true);
            
            $thumb_id               =   get_post_thumbnail_id($product_id);
            $thumb                  =   wp_get_attachment_image_src($thumb_id,'small');

            $chatUrl=   get_post_meta($product_id,'statsUrl',true);
            
         
            //$live_event_uri_final   =   $this->wpstream_request_hls_player_dynamic('',$product_id);// buleala lu Gabi   
            
            
            $live_event_uri_final=   get_post_meta($product_id,'sldpPlaybackUrl',true);
            $now                    =   time().rand(0,10);
           
            $usernamestream         =   esc_html ( get_option('wpstream_api_username','') );
            
            
           
            
            echo '<div class="wpstream_live_player_wrapper function_wpstream_live_event_player_low_latency wpstream_low_latency" data-now="'.$now.'" data-me="'.esc_attr($usernamestream).'" data-product-id="'.$product_id.'" id="wpstream_live_player_wrapper'.$now.'" > ';
                    
                   
                    if( ( isset($event_settings['view_count'] ) && intval($event_settings['view_count'])==1 ) || !isset($event_settings['view_count']) ){
                        echo '<div id="wpestream_live_counting" class="wpestream_live_counting"></div>';
                    }
                  
                    $show_wpstream_not_live_mess=' style="display:none;" ';
                    if(trim($live_event_uri_final) ==''){
                        $show_wpstream_not_live_mess=''; 
                    }
            
                    
                    print '<div class="wpstream_not_live_mess " '.$show_wpstream_not_live_mess.' ><div class="wpstream_not_live_mess_back"></div><div class="wpstream_not_live_mess_mess">'.esc_html__('We are not live at this moment. Please check back later.','wpstream').'</div></div>';
                     
                    
                    $poster_data=' poster="'.$thumb[0].'" ';
                    if($poster_show=='no'){
                        $poster_data='';
                    }
                    
                 
                    $is_muted='';
                    if( isset($event_settings['mute']) && intval($event_settings['mute'])==1){
                        $is_muted=' muted ';
                    }
                    
                    
                    $autoplay='autoplay';
                    if(isset($event_settings['autoplay']) && intval($event_settings['autoplay'])==0){
                        $autoplay='no_autoplay';
                    }
                    
                    echo'
                    <div  iccd="player" id="wpstream-video'.$now.'"   '.$poster_data.' '.$is_muted.' class="" >
                    </div>';

                    print '<script type="text/javascript">
                                //<![CDATA[
                                    jQuery(document).ready(function(){
                                        var low_latencyid="wpstream-video'.$now.'";
                                        document.addEventListener("DOMContentLoaded", initPlayer(low_latencyid, "'.$live_event_uri_final.'","'.$is_muted.'","'.$autoplay.'" ) ); ';
                                    print'});
                                //]]>
                            </script>';
                 
               
               
                if(trim($live_event_uri_final) ==''){
                    // $show_wpstream_not_live_mess=''; 
                }else{
                       print '<script type="text/javascript">
                            //<![CDATA[
                                jQuery(document).ready(function(){
                                    var player_wrapper =   jQuery(".wpstream_live_player_wrapper");
                                    wpstream_read_websocket_info("'.$product_id.'","wpstream_live_player_wrapper'.$now.'", player_wrapper ,"'.$chatUrl.'", "'.$live_event_uri_final.'");
                                });
                            //]]>
                        </script>';
                }
               
               
               if($use_chat=="yes"){
                    $this->wpstream_connect_to_chat($product_id);
               }
               
               usleep (10000);

        }


        
        /**
        * HLS PLAYER
        *
        * @author cretu
        */
        public function wpstream_request_hls_player_dynamic($server_id,$product_id){
         
            $transient_name = 'hls_to_return_'.$product_id;
            $hls_to_return = get_transient( $transient_name );

            if ( false ===  $hls_to_return || $hls_to_return=='' ) { //ws || $hls_to_return==''
                      
                $token                      =   $this->main->wpstream_live_connection->wpstream_get_token();
                $values_array               =   array();
                $values_array['server_id']  =   $server_id;
                
                if($server_id==''){
                    $this->main->wpstream_live_connection->wpstream_check_event_status_front_player($product_id,$server_id);                  
                    $server_id                  =   get_post_meta($product_id,'server_id', true );     
                    $values_array['server_id']  =   $server_id;
                }

                
                if($server_id==''){
                    delete_transient($transient_name);
                     return '';exit();
                }
                
                $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/api20-livestrem/ap20_get_player_hls_dynamic/?access_token=".$token;


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
                $response       =   wp_remote_post($url,$arguments);
              
                $received_data  =   json_decode( wp_remote_retrieve_body($response) ,true);
                
           
           
                
                if( isset($response['response']['code']) && $response['response']['code']=='200'){
                    $hls_to_return =  trim($received_data);
                
                    
                    set_transient( $transient_name, $hls_to_return, 60 );
                    return $hls_to_return;
                }else{     
                    return 'failed connection';
                }
                exit();
            }else{
               return $hls_to_return;
            }

        }



        /**
        * HLS PLAYER
        *
        * @author cretu
        */
    

        public function wpstream_request_hls_player($product_id){
            
            $transient_name = 'hls_to_return_'.$product_id;
            $hls_to_return = get_transient( $transient_name );

            if ( false ===  $hls_to_return || $hls_to_return=='' ) {
       
                $show_id        =   intval($product_id);
                $token          =   $this->main->wpstream_live_connection->wpstream_get_token();
                $values_array   =   array();
                $values_array['show_id'] =   $show_id;

                 $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/ap20_get_player_hls/?access_token=".$token;


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
                $response       =   wp_remote_post($url,$arguments);
              
                $received_data  =   json_decode( wp_remote_retrieve_body($response) ,true);

                
                
                if( isset($response['response']['code']) && $response['response']['code']=='200'){
                    $hls_to_return =  trim($received_data);
                    set_transient( $transient_name, $hls_to_return, 60 );
                    return $hls_to_return =  trim($received_data);
                }else{     
                    return 'failed connection';
                }
                exit();
            }else{
               return $hls_to_return;
            }

        }



        /**
        * VODPlayer uri details
        *
        * @author cretu
        */
        public function wpstream_video_on_demand_player_uri_request($product_id){
           
                $wpstream_data_setup    =   '  data-setup="{}" ';
                
                /* free_video_type
                 * 1 - free live channel
                 * 2 - free video encrypted
                 * 3 - free video -not encrypted
                 */
                $free_video_type        =   intval( get_post_meta($product_id, 'wpstream_product_type', true));
                 

                if($free_video_type==2 || get_post_type($product_id)=='product' ){
                    
                    /* IF vide is encrypted-  readed from vod,streaner
                     */
                    
                    $video_type         =   'application/x-mpegURL';
                    $video_path         =   get_post_meta($product_id,'_movie_url',true); 
                    if(get_post_type($product_id)=='wpstream_product'){
                        $video_path =    esc_html(get_post_meta($product_id, 'wpstream_free_video', true));
                    }

                    $username           =   esc_html ( get_option('wpstream_api_username','') );
                    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                        $username = $this->wpstream_retrive_username();
                    }

                    if(strpos($username,'@')!=false){
                        $username_array= explode('@', $username);
                        $username=$username_array[0];
                    }

                    $video_path_final   =   'https://vod.streamer.wpstream.net/'.$username.'/'.$video_path.'/index.m3u8?'.get_site_url();
                    if(!is_ssl()){
                        $video_path_final   =   'http://vod.streamer.wpstream.net/'.$username.'/'.$video_path.'/index.m3u8?'.get_site_url(); 
                    }
                    
                }else if($free_video_type==3){
                    
                    /* Video is unecrypted - read from local or youtube / vimeo
                    */
                    
                    $video_type         =   'video/mp4';
                    $video_path_final=esc_html(get_post_meta($product_id, 'wpstream_free_video_external', true));

                    if (strpos($video_path_final, 'www.youtube') !== false) {
                        $wpstream_data_setup= '    data-setup=\'{ "techOrder": ["youtube"], "sources": [{ "type": "video/youtube", "src": "'.$video_path_final.'"}] }\'   '; 
                        $video_path_final='';
                    }
                    if (strpos($video_path_final, 'vimeo.com') !== false) {
                        $wpstream_data_setup= '   data-setup=\'{"techOrder": ["vimeo"], "sources": [{ "type": "video/vimeo",  "src": "'.$video_path_final.'"}], "vimeo": { "color": "#fbc51b"} }\'   '; 
                        $video_path_final='';
                    }

                }
                
            $return_array=array();
            $return_array['video_path_final']   =   $video_path_final;
            $return_array['wpstream_data_setup']=   $wpstream_data_setup;
            $return_array['video_type']         =   $video_type;
            return $return_array;
 }
     
 
 
         /**
        * VODPlayer url
        *
        * @author cretu
        */

        public function wpstream_video_on_demand_player($product_id){
            
                    $uri_details        =   $this->wpstream_video_on_demand_player_uri_request($product_id);
                    $video_path_final   =   $uri_details['video_path_final'];
                    $wpstream_data_setup =  $uri_details['wpstream_data_setup'];
                    $video_type          =  $uri_details['video_type'];
                    
                    $thumb_id               =   get_post_thumbnail_id($product_id);
                    $thumb                  =   wp_get_attachment_image_src($thumb_id,'small');
                    $usernamestream         =   esc_html ( get_option('wpstream_api_username','') );
                    
                    $pack = $this->main->wpstream_live_connection->wpstream_request_pack_data_per_user();
                
                    if(isset($pack['band']) && $pack['band']>0){
                        echo '<video id="wpstream-video'.time().'" class="video-js vjs-default-skin  vjs-16-9 kuk wpstream_video_on_demand vjs-wpstream"  data-me="'.esc_attr($usernamestream).'" data-product-id="'.$product_id.'"  controls preload="auto"
                                poster="'.$thumb[0].'" '.$wpstream_data_setup.'>

                                <source src="'.trim($video_path_final).'"  type="'.$video_type.'">
                                <p class="vjs-no-js">
                                  To view this video please enable JavaScript, and consider upgrading to a web browser that
                                  <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                                </p>
                            </video>';
                    }else{
                        print esc_html_e('Insufficient resources to stream this title','wpstream');
                    }

        }


        
        
        
        
        /**
        * Retreive username for vod path
        *
        * @author cretu
        */
        private function wpstream_retrive_username(){
                $token          =   $this->main->wpstream_live_connection->wpstream_get_token();

                $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/return_login/?access_token=".$token;
                $arguments = array(
                    'method' => 'GET',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'cookies' => array()
                );
                $response       =   wp_remote_post($url,$arguments);
                $received_data  =   json_decode( wp_remote_retrieve_body($response) ,true);
                if( isset($response['response']['code']) && $response['response']['code']=='200'){
                    return ($received_data); die(); 
                }else{     
                    print 'Failed to conbect media server';
                    die(); 
                }
        }
        
        
        
        
        
        /**
	 * check if the user bought the product and display the player - TO REDo
	 *
	 * @since     3.0.1
         * returns html of the player
	*/
        public function wpstream_user_logged_in_product_already_bought($from_sh_id='') {

            if ( is_user_logged_in() ) {
                global $product;
                $current_user   =       wp_get_current_user();
                $product_id     =       $product->get_id();


                if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id) || ( function_exists('wcs_user_has_subscription') && wcs_user_has_subscription( $current_user->ID, $product_id ,'active') ) ){


                    echo '<div class="wpstream_player_wrapper"><div class="wpstream_player_container">';

                    $is_subscription_live_event =   esc_html(get_post_meta($product_id,'_subscript_live_event',true));
                    $term_list                  =   wp_get_post_terms($product_id, 'product_type');
                   

                    if( $term_list[0]->name=='live_stream' || ($term_list[0]->name=='subscription' && $is_subscription_live_event=='yes' )  ){
                        $this->wpstream_live_event_player($product_id);
                    }else if( $term_list[0]->name=='video_on_demand'  || ($term_list[0]->name=='subscription' && $is_subscription_live_event=='no' ) ){
                        $this->wpstream_video_on_demand_player($product_id);
                    }
                    echo '</div></div>';
                }else{
                    if( get_post_type($product_id) == 'wpstream_product' ){
                        echo '<div class="wpstream_player_wrapper no_buy"><div class="wpstream_player_container">';
                        echo '<div class="wpstream_notice">'.__('You did not buy this product!','wpstream').'</div>';
                        echo '</div></div>';
                    }
                }

               
            }
        }
        
         /**
	 * check if the user bought the product and display the player - TO REDo
	 *
	 * @since     3.0.1
         * returns html of the player
	*/
        
        public function wpstream_chat_wrapper($product_id){
           require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/templates/wpstream_chat_template.php';
        }

        
        
    public  function wpstream_connect_to_chat($product_id){
        $current_user           =   wp_get_current_user();
        $userID                 =   $current_user->ID;
        $user_login             =   $current_user->user_login;

        $key='';

        $chatUrl                =   get_post_meta($product_id,'chatUrl',true);
               

        wp_enqueue_script( 'sockjs-0.3.min' );
        wp_enqueue_script( 'emojione.min.js' );
        wp_enqueue_script( "jquery-effects-core");
        wp_enqueue_script( 'jquery.linkify.min.js');
        wp_enqueue_script( 'ripples.min.js');
        wp_enqueue_script( 'material.min.js');
        wp_enqueue_script( 'chat.js');



        wp_enqueue_style( 'chat.css');
        wp_enqueue_style( 'ripples.css');
        wp_enqueue_style( 'emojione.min.css');


        
       
       if(!is_user_logged_in()){
           $user_login='';
           $chatUrl='';
       }

       
       print '<script type="text/javascript">
            //<![CDATA[
                jQuery(document).ready(function(){
                    username = "'.$user_login.'";
                    key="'.$key.'";
                   
                });
            //]]>
        </script>';
      
    }
}
