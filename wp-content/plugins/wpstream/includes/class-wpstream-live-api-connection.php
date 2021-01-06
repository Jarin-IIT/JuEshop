<?php

class Wpstream_Live_Api_Connection  {

	

    
    public function __construct() {
        add_action( 'wp_ajax_wpstream_give_me_live_uri', array($this,'wpstream_give_me_live_uri') );  
        add_action('wp_ajax_wpstream_stop_server', array($this,'wpstream_stop_server'));
        add_action('wp_ajax_wpstream_update_local_event_settings',array($this,'wpstream_update_local_event_settings'));
        
        
        add_action( 'wp_ajax_wpstream_check_dns_sync', array($this,'wpstream_check_dns_sync') );
        add_action( 'wp_ajax_wpstream_check_event_status', array($this,'wpstream_check_event_status') );
        
        add_action( 'wp_ajax_wpstream_check_server_against_db', array($this,'wpstream_check_server_against_db') );  
        add_action( 'wp_ajax_wpstream_close_event', array($this,'wpstream_close_event') );
        add_action( 'wp_ajax_wpstream_get_download_link', array($this,'wpstream_get_download_link') );  
        add_action( 'wp_ajax_wpstream_get_delete_file', array($this,'wpstream_get_delete_file') ); 
    }
    
    /**
     * Check live stream in db
     *
     * @since    3.0.1
     * returns live url
    */
    public function wpstream_check_server_against_db(){
        $show_id    =   intval($_POST['show_id']);
        $is_live    =   false;

        $live_event_for_user    =   $this->api20_wpstream_get_live_event_for_user($show_id);
        if(isset($live_event_for_user[$show_id])) {
           $is_live=true;   
        }

        echo json_encode( array('islive' =>$is_live) );
        exit();
    }






    /**
     * retreive server id based on show id
     *
     * @since    3.0.1
     * returns live url
    */
    
    function retrive_server_id_based_on_show_id($show_id){
        
            $transient_name = 'server_id_to_return_'.$show_id;
            $server_id_to_return = get_transient( $transient_name );
            
            if ( false ===  $server_id_to_return  ) {
                $token  = $this->wpstream_get_token();
                $values_array=array(
                    "show_id"           =>  intval($show_id),
                );

            
                $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/api20-livestrem/server_id_by_show_id/?access_token=".$token;
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

                $received_data  =  wp_remote_retrieve_body($response);




                if( isset($response['response']['code']) && $response['response']['code']=='200'){
                    $server_id_to_return= set_transient( $transient_name, json_decode($received_data), 30 );
                    return json_decode($received_data);
                    die();
                }else{     
                    print 'failed connection';
                }
            }else{
                return $server_id_to_return;
            }
            
            die();
    }
    
    
    
    /**
     * check event status
     *
     * @since    3.0.1
     * returns live url
    */
    
    public  function wpstream_check_event_status(){
            $token      =   $this->wpstream_get_token();
            $show_id    =   intval($_POST['show_id']);
            $server_id  =   esc_html(trim($_POST['server_id']));
            
            // serever id may be blank if the user decide to close browser before receiving it
        
            if($server_id==''){
                update_post_meta($show_id,'server_id', '' );
                $server_id = $this->retrive_server_id_based_on_show_id($show_id);

            }
            
            
            update_post_meta($show_id,'server_id', $server_id );
            
            $values_array=array(
                "server_id"           =>  esc_html($server_id),
                'from_where'          =>  'wpstream_check_event_status'
            );
            
          
            if($server_id==''){
                print 'failed connection';
                die();
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
                    $this->api20_wpstream_update_event($received_data_encoded,$show_id,$server_id);
                    
                    if( isset($received_data_encoded['broadcastUrl']) && isset($received_data_encoded['status']) && $received_data_encoded['status']==='active'  ){
                        $to_split=explode('wpstream/',$received_data_encoded['broadcastUrl']);

                        $obs_uri = $to_split[0].'wpstream/';
                        $obs_stream = $to_split[1];

                        $received_data_encoded['obs_uri']       =   $obs_uri;
                        $received_data_encoded['obs_stream']    =   $obs_stream;
                        $received_data_encoded['live_data_url'] =   'https://qos.live.streamer.wpstream.net/liveqos/?datasource='.$server_id.'.live.streamer.wpstream.net&key='.$received_data_encoded['streamKey'];
                
                        update_post_meta($show_id,'obs_uri',$obs_uri);
                        update_post_meta($show_id,'obs_stream',$obs_stream);
                          
                        $received_data=json_encode($received_data_encoded);
                    }
                    print $received_data;
                    die();
                }
                
            }else{     
                print 'failed connection';
            }
            die();
    }
    
    
    /**
     * check event status for front end player
     *
     * @since    3.0.1
     * returns live url
    */
    
    public  function wpstream_check_event_status_front_player($show_id,$server_id){
            $token      =   $this->wpstream_get_token();
            // serever id may be blank if the user decide to close browser before receiving it

            if($server_id==''){
                update_post_meta($show_id,'server_id', '' );
                $server_id = $this->retrive_server_id_based_on_show_id($show_id);
            }
            
         
                       
            update_post_meta($show_id,'server_id', $server_id );
            $values_array=array(
                "server_id"           =>  esc_html($server_id),
                'from_where'          =>  'wpstream_check_event_status_front_player'
            );
            
            if($server_id==''){
                $this->wpstream_reset_event_data($show_id);
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
                    $this->api20_wpstream_update_event($received_data_encoded,$show_id,$server_id);
                    
                    if( isset($received_data_encoded['broadcastUrl']) && isset($received_data_encoded['status']) && $received_data_encoded['status']==='active'  ){
                        $to_split=explode('wpstream/',$received_data_encoded['broadcastUrl']);

                        $obs_uri = $to_split[0].'wpstream/';
                        $obs_stream = $to_split[1];

                        $received_data_encoded['obs_uri']=$obs_uri;
                        $received_data_encoded['obs_stream']=$obs_stream;
                        
                        update_post_meta($show_id,'obs_uri',$obs_uri);
                        update_post_meta($show_id,'obs_stream',$obs_stream);
                          
                        $received_data=json_encode($received_data_encoded);
                    }
                   
                }else{
                    $this->wpstream_reset_event_data($event_id);
                }
                
            }else{    
                $this->wpstream_reset_event_data($event_id);
                print 'failed connection';
            }
           
    }
      
   
    public function wpstream_reset_event_data($event_id){
        update_post_meta($event_id,'statsUrl','');
        update_post_meta($event_id,'hlsPlaybackUrl','');
        update_post_meta($event_id,'server_id', '' );
    }
    
    /**
     * update event metadata
     *
     * @since    3.0.1
     * returns live url
    */
    
    
      
    function api20_wpstream_update_event($received_data,$show_id,$server_id=''){

        if( is_array($received_data) )  {
            $event_data_for_transient               =   array();
            $event_data_for_transient['server_id']  =   $server_id;
            $transient_name                         =   'event_data_to_return_'.$show_id;
               
            foreach($received_data as $key=>$value){
                update_post_meta($show_id,$key,$value);
                $event_data_for_transient[$key]=$value;
            }
            set_transient($transient_name,$event_data_for_transient,45);
            return $event_data_for_transient;
        }else{
            return false;
        }
        

    }
            
    /**
     * Update event settings
     *
     * @since    3.0.1
     * returns live url
    */
    
    
    public function wpstream_update_local_event_settings(){
       
        if(!is_user_logged_in()){
            exit('not logged in');
        }
        if( !current_user_can('administrator') ){
            exit('not admin');
        }
        
        $show_id        =   intval($_POST['show_id']);
        $option_array   =   $_POST['option'];
        
        $to_save_option=array();
        foreach($option_array as $key=>$value){
            $to_save_option[sanitize_key($key)]=sanitize_text_field($value);
        }
        
        print_r($to_save_option);
        print $show_id;
        
        update_post_meta ($show_id,'local_event_options',$to_save_option);
        exit();
    }
    
    
            
            
     /**
     * Stop Server
     *
     * @since    3.0.1
     * returns live url
     */
    public function wpstream_stop_server(){
 
            
        check_ajax_referer( 'wpstream_stop_event_nonce', 'security' );
        $current_user       =   wp_get_current_user();
        $allowded_html      =   array();
        $userID             =   $current_user->ID;
        $return_uri         =   '';
        
        if( !current_user_can('administrator') ){
            exit('not admin');
        }
        
        $show_id    = intval($_POST['show_id']);
        $token      = $this->wpstream_get_token();
          
            
        $values_array=array(
            "show_id"           =>  $show_id,
            "request_by_userid" =>  $userID,
        );

        $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/api20-livestrem/stop-event/?access_token=".$token;

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
            print_r($received_data);
            print  'closed';
        }else{     
             print 'Failed to connect to WpStream. Please try again later.';
        }

        die();
    }
            
        



    
    
    /**
     * Request live url
     *
     * @since    3.0.1
     * returns live url
     */
    public function wpstream_give_me_live_uri(){
        
            //check_ajax_referer( 'wpstream_start_event_nonce', 'security' );
            $current_user       =   wp_get_current_user();
            $allowded_html      =   array();
            $userID             =   $current_user->ID;
            $return_uri         =   '';


            global $wpstream_plugin;
            if( !$wpstream_plugin->main->wpstream_check_user_can_stream() ){
                exit('not allowed to stream 376');
            }
        
           
            if( ( trim( get_option('wpstream_api_username') )=='' ||  trim( get_option('wpstream_api_password')== '')) || !$this->wpstream_client_check_api_status()  ){
               
                    echo json_encode(   array(
                        'conected'      =>  false,
                        'event_data'    =>  '',
                        'error'         =>  esc_html__('You are not connected to wpstream.net! Please check your WpStream credentials! ','wpstream')
                    
                    ));
                    die();
                
                
            }
            

            $show_id            =   intval  ( $_POST['show_id'] );
            update_post_meta($show_id,'server_id', '' );
            
            $local_event_options =   get_post_meta($show_id,'local_event_options',true);
            if(!is_array($local_event_options)){
                $local_event_options =   get_option('wpstream_user_streaming_global_channel_options') ;
            }

            // set encrypt option
            $is_encrypt="false";
            if( intval( $local_event_options['encrypt']) ==1 ){
                $is_encrypt="true";
            }
            
            // set record option
            $is_record="false";
            if( intval( $local_event_options['record']) ==1 ){
                $is_record="true";
            }
            
            $corsorigin='';
            if( isset($local_event_options['domain_lock']) && intval( $local_event_options['domain_lock']) ==0 ){
                $corsorigin='*';
            }
            
            
           $event_data         =   $this->wpstream_request_live_stream_uri($show_id,$is_record,$is_encrypt,$userID,$corsorigin);
          
            
            
            
            if($event_data){
                echo json_encode(   array(
                    '$is_record'=>$is_record,
                                    'conected'      =>  true,
                                    'event_data'    =>  $event_data,
                                   
                    ));
            }else{
                echo json_encode(   array(
                     '$is_record'=>$is_record,
                        'conected'      =>  false,
                        'event_data'    =>  $event_data,
                        'error'         =>  'Error 4176'// no aws server dns propagation,
                    
                    ));
            }
               die();
    }





    public function wpstream_request_live_stream_uri($show_id,$is_record,$is_encrypt,$request_by_userid,$corsorigin){    
            $token  = $this->wpstream_get_token();
            $domain = parse_url ( get_site_url() );

            $home_url       =   get_home_url();
            $domain_scheme  =   'http';
            $home_url       =   str_replace('https://','http://',$home_url);
            
            if(is_ssl()){
                $domain_scheme='https';
                $home_url = str_replace('http://','https://',$home_url);
            }
            
            $domain_ip= esc_html( $_SERVER['SERVER_ADDR'] );
            if($domain_ip==''){
                $domain_ip="0.0.0.0/0";
            }
            
            if($corsorigin!='*'){
                $corsorigin=$domain_scheme.'://'.$domain['host'];
            }
            
            $values_array=array(
                "show_id"           =>  $show_id,
                "scheme"            =>  $domain_scheme,
                "domain"            =>  $domain['host'],
                "domain_ip"         =>  $domain_ip,
                "is_record"         =>  $is_record,
                "is_encrypt"        =>  $is_encrypt,
                "location"          =>  $home_url,
                "request_by_userid" =>  $request_by_userid,
                "pluginVersion"     =>  WPSTREAM_PLUGIN_VERSION,
                "corsOrigin"        =>  $corsorigin,
            );

            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/api20-livestrem/new/?access_token=".$token;

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
               return ($received_data);
            }else{     
                if($received_data['data']['status']=='401'){
                    return('You are not connected to wpstream.net! Please check your WpStream credentials! ');
      
                }else{
                     return 'Failed to connect to WpStream. Please try again later.';
                }
               
            }

    }























    /**
     * Retrive auth token from tranzient
     *
     * @since    3.0.1
     * returns token
     */
    public function wpstream_get_token(){
        $token =  get_transient('wpstream_token_request');
        if ( false === $token || $token==='' ) {
            $token = $this->wpstream_club_get_token();
            set_transient( 'wpstream_token_request', $token ,600);
        }

        return $token;

    }

	
    
     /**
     * Request auth token from wpstream.net
     *
     * @since    3.0.1
     * returns token fron wpstream
     */
    protected function wpstream_club_get_token(){

        $client_id      = esc_html ( get_option('wpstream_api_key','') );
        $client_secret  = esc_html ( get_option('wpstream_api_secret_key','') );
        $username       = esc_html ( get_option('wpstream_api_username','') );
        $password       = esc_html ( get_option('wpstream_api_password','') );

        if ( $username=='' || $password==''){
            return;
        }
        $curl = curl_init();
        
        $json = array(
                'grant_type'=>'password',
                'username'  =>$username,
                'password'  =>$password,
                'client_id'=>'qxZ6fCoOMj4cNK8SXRHa5nug6vnswlFWSF37hsW3',
                'client_secret'=>'L1fzLosJf9TlwnCCTZ5pkKmdqqkHShKEi0d4oFNE'
            );

        curl_setopt_array($curl, array(
        CURLOPT_URL => WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/?oauth=token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
    //    CURLOPT_POSTFIELDS => "grant_type=password&username=".$username."&password=".$password."&client_id=qxZ6fCoOMj4cNK8SXRHa5nug6vnswlFWSF37hsW3&client_secret=L1fzLosJf9TlwnCCTZ5pkKmdqqkHShKEi0d4oFNE",
        CURLOPT_POSTFIELDS=> json_encode($json),
        CURLOPT_HTTPHEADER => array(
         
            "cache-control: no-cache",
            "content-type: application/json",

            ),
        ));

        $response = curl_exec($curl);
        
        if(!$response){
            print '<div class="api_not_conected" style="margin:15px;">We could not connect to WpStream.net. Make sure you have the php Curl library enabled and your hosting allows  outgoing HTTP Connection. </div>';
        }
        
        $err = curl_error($curl);
        
  

        
        
        curl_close($curl);
        $response= json_decode($response);

        if(isset($response->access_token)){
            return $response->access_token;
        }else{
            return;
        }
    }
    
 
    
    
    /**
    * Return admin package data
    *
    * @since    3.0.1
    * returns pack data 
    */
    
    public function wpstream_request_pack_data_per_user(){

       
        $token          =   $this->wpstream_get_token();
        $values_array   =   array();
        $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/status/packdetails/?access_token=".$token;


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



        if( !is_wp_error($response) && isset($response['response']['code']) && $response['response']['code']=='200'){
           return ($received_data);
        }else{     
            return 'failed connection';
        }

    }
    
    /**
    * Check Api Status
    *
    * @since    3.0.1
    * returns true or false
    */
    
    function wpstream_client_check_api_status(){

            $token= $this->wpstream_get_token();

            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/status/?access_token=".$token;
            $arguments = array(
                'method' => 'GET',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'cookies' => array()
            );
            $response   =   wp_remote_post($url,$arguments);
            $body       =   wp_remote_retrieve_body($response);

            if ( $body === true || $body ==='true'){
                return true;
            }else{
                return false;
            } 
    }
    
     /**
    * Start Get live events for users
    *
    * @since    3.0.1
    * returns true or false
    */
    public function wpstream_get_live_event_for_user(){
        $current_user       =   wp_get_current_user();
        $allowded_html      =   array();
        $userID             =   $current_user->ID;
        $return_uri         =   '';

        global $wpstream_plugin;
        if( !$wpstream_plugin->main->wpstream_check_user_can_stream() ){
            exit('not allowed to stream 678');
        }


        $event_data         =   $this->wpstream_request_live_stream_for_user($userID);
        return $event_data;
    }
    
    
    
    /**
    * Start Get live events for users and $show_id
    *
    * @since    3.0.1
    * returns true or false
    */
    public function api20_wpstream_get_live_event_for_user(){
       
        global $wpstream_plugin;
        if( !$wpstream_plugin->main->wpstream_check_user_can_stream() ){
            exit('not allowed to stream 698');
        }


        $event_data         =   $this->api20_wpstream_request_live_stream_for_user();
        return $event_data;
    }
    
    
    
    
    
    
    
    /**
    * Start Get live events for users
    *
    * @since    3.0.1
    * returns true or false
    */
    public function wpstream_request_live_stream_for_user($user_id){

        global $wpstream_plugin;
        if( !$wpstream_plugin->main->wpstream_check_user_can_stream() ){
            exit('not allowed to stream 737');
        }


        $domain = parse_url ( get_site_url() );
        $token= $this->wpstream_get_token();

        $values_array=array(
            "show_id"           =>  $user_id,
            "scheme"            =>  $domain['scheme'],
            "domain"            =>  $domain['host'],
            "domain_ip"         =>  $_SERVER['SERVER_ADDR']
        );



        $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/livestrem/api20_peruser/?access_token=".$token;


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

        if(is_wp_error($response)){
            return 'failed connection';
        }
        if( isset($response['response']['code']) && $response['response']['code']=='200'){
           return ($received_data);
        }else{     
            return 'failed connection';
        }

    }

    public function api20_wpstream_request_live_stream_for_user(){
        global $wpstream_plugin;
        if( !$wpstream_plugin->main->wpstream_check_user_can_stream() ){
            return;
        }else{
        

            $token          =   $this->wpstream_get_token();
            $values_array   =   array();



            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/livestrem/api20_peruser/?access_token=".$token;


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

            if(is_wp_error($response)){
                return 'failed connection';
            }
            if( isset($response['response']['code']) && $response['response']['code']=='200'){
               return ($received_data);
            }else{     
                return 'failed connection';
            }
        }

    }


    
    
    /**
    * Delete event
    *
    * @since    3.0.1
    * returns noda
    */
    
    public function wpstream_close_event(){
            check_ajax_referer( 'wpstream_start_event_nonce', 'security' );
            $current_user       =   wp_get_current_user();
            $allowded_html      =   array();
            $userID             =   $current_user->ID;
            $return_uri         =   '';
            if( !current_user_can('administrator') ){
               exit('okko');
            }

            $show_id            =   intval($_POST['show_id']);
            update_post_meta ($show_id,'event_passed',1);
            die();
    }


    /**
    * Get signed upload form data
    *
    * @since    3.0.1
    * returns aws form
    */
    public function wpstream_get_signed_form_upload_data(){
            if( !current_user_can('administrator') ){
                exit('okko');
            }

            $token          =   $this->wpstream_get_token();
            $values_array   =   array();
            $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_upload_form/?access_token=".$token;


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
                return $received_data;
            }else{     
                return 'failed connection';
            }
    }
    
    /**
    * Get video from storage- clear data for front end use
    *
    * @since    3.0.1
    * returns aws data
    */
    public function wpstream_get_videos(){
            if( !current_user_can('administrator') ){
                exit('okko');
            }
            $token          =   $this->wpstream_get_token();
            $values_array   =   array();
            $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_list_row/?access_token=".$token;


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
                $video_options=array();
                foreach ($received_data as $key=>$videos){
                   $video_options[$videos['video_name_storage']]=$videos['video_name_storage'].'';
                }

                return $video_options;
            }else{     
                return 'failed connection';
            }


       
        
    }
    
    
    
    /**
    * Get video from storage- raw data
    *
    * @since    3.0.1
    * returns aws data
    */
    public function wpstream_get_videos_from_storage_raw_data( ){

            if( !current_user_can('administrator') ){
                exit('okko');
            }
            $token          =   $this->wpstream_get_token();
            $values_array   =   array();
            $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_list_row/?access_token=".$token;
          

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
                return $received_data;            
            }else{     
                return 'failed connection';
            }

    }

    
    
    /**
    * Get download link from aws
    *
    * @since    3.0.1
    * returns aws data
    */
    
    function wpstream_get_download_link(){
            if( !current_user_can('administrator') ){
                exit('okko get_download_link');
            }

            $video_name                 =   sanitize_text_field($_POST['video_name']);
            $token                      =   $this->wpstream_get_token();
            $values_array               =   array();
            $values_array['video_name'] =   $video_name;
            $url                        =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_download_link/?access_token=".$token;


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
                print trim($received_data);
            }else{     
                return 'failed connection';
            }
            exit();
    }

    
     /**
    * Delete file from storage
    *
    * @since    3.0.1
    * 
    */
    public function wpstream_get_delete_file(){
        if( !current_user_can('administrator') ){
            exit('okko get_delete_file');
        }

        $video_name                 =   esc_html($_POST['video_name']);
        $token                      =   $this->wpstream_get_token();
        $values_array               =   array();
        $values_array['video_name'] =   $video_name;
        $url                        =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/videos/get_delete_file/?access_token=".$token;


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
        $response           =   wp_remote_post($url,$arguments);
        $received_data      =   json_decode( wp_remote_retrieve_body($response) ,true);


        if( isset($response['response']['code']) && $response['response']['code']=='200'){
            print $received_data;
        }else{     
            return 'failed connection';
        }
        exit();

    
    }

     /**
    * check if stream is live
    *
    * @since    3.0.1
    * 
    */
    public function wpstream_is_is_live($product_id){
    
            $token          =       $this->wpstream_get_token();

            $values_array=array(
                "show_id"           =>  $product_id,
            );
            $url=WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/livestrem/checklive/?access_token=".$token;


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

            if(is_wp_error($response)){
                return 'failed connection';
            }
            if( isset($response['response']['code']) && $response['response']['code']=='200'){
               return ($received_data);
            }else{     
                return 'failed connection';
            }
   
    }

    /**
    * get server ip for live streaming
    *
    * @since    3.0.1
    * 
    */
    public function  wpstream_get_live_stream_server($current_user,$streamname){

            $token          =       $this->wpstream_get_token();
            $values_array   =       array();
            $values_array['new_stream']     =   $streamname;

            $url            =   WPSTREAM_CLUBLINKSSL."://www.".WPSTREAM_CLUBLINK."/wp-json/rcapi/v1/livestrem/get_server_ip/?access_token=".$token;


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
                return trim($received_data);
            }else{     
                return 'failed connection';
            }
            exit();

        }
    
}// end class