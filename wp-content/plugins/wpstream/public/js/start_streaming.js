/*global $, jQuery, wpstream_start_streaming_vars*/
var counters={};

jQuery(document).ready(function ($) {
    "use strict";
    
  
    wpstream_check_live_connections();
    
     jQuery('.copy_live_uri').click(function(){
        var value_uri = jQuery(this).parent().find('.wpstream_live_uri_text').text();
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(temp).val(value_uri).select();
        document.execCommand("copy");
        jQuery(temp).remove();
        
    });
    
    jQuery('.copy_live_key').click(function(){
        var value_uri = jQuery(this).parent().find('.wpstream_live_key_text').text();
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(temp).val(value_uri).select();
        document.execCommand("copy");
        jQuery(temp).remove();
    });
    
    
    $('.start_webcaster').on('click',function(){
        var caster_url = $(this).attr('data-webcaster-url');
        $(this).parent().find('.external_software_streaming').slideUp()
        window.open(caster_url, '_blank', 'location=yes,height=390,width=660,scrollbars=yes,status=yes');
    })
      
      
    $('.wpstream_show_settings').on('click',function(){
        $(this).parent().parent().find('.wpstream_event_streaming_local').slideToggle();
    });
    
    $('.start_external').on('click',function(){
        $(this).parent().parent().find('.external_software_streaming').slideToggle();
    });
    
    
    
    $('.wpstream_event_option_item').on('click',function(){
        
        var holder  =   jQuery(this).parents('.wpstream_event_streaming_local');
        var show_id =   jQuery(this).parents('.event_list_unit').find('.start_event').attr('data-show-id');
        var optionarray ={};
        holder.find('.wpstream_event_option_item').each(function(){
            optionarray[jQuery(this).attr('data-attr-ajaxname')]=jQuery(this).prop("checked") ? 1 : 0 ;
        });
        
        
      
        
       
        
      var myJSON = JSON.stringify(optionarray);
        console.log(myJSON);
        console.log(optionarray);
        
          jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            timeout: 300000,
         

            data: {
                'action'            :   'wpstream_update_local_event_settings',
                'show_id'           :   show_id,
                'option'            :  optionarray,
                             
            },
            success: function (data) {
               
         
            },
            error: function (jqXHR,textStatus,errorThrown) {
             
            }
            
        });
        
    });
    
    
    
    
    
    
    
    function wpstream_check_live_connections(){
     
        if(  $('.pending_streaming.pending_trigger').length>0 ){
            $('.pending_streaming.pending_trigger').each(function(){
                var server_url  =   $(this).attr('data-server-url');
                var counter     =   '';
                var acesta      =   $(this);

                var show_id     =   $(this).attr('data-show-id');
                var server_id  =   $(this).attr('data-server-id'); 
                
                wpstream_check_live_connections_from_database(acesta,show_id,server_id);
                var counter_long     =   '';
                counter_long =  setInterval( function (){ wpstream_check_live_connections_from_database(acesta,show_id,server_id)},60000);
                counters[show_id]=counter_long;

            });
        }
     
    }

    
    
    function wpstream_check_live_connections_step( acesta,server_url){

        var server_status = wpstream_check_server_status(server_url,
            function( server_status ){

                if(server_status ){
                    acesta.removeClass('show_stream_data').addClass('hide_stream_data');
                    acesta.parent().find('.wpstream_ready_to_stream').removeClass('hide_stream_data').addClass('show_stream_data');
                    acesta.parents('.event_list_unit').addClass('wpstream_show_started');
              
                    clearInterval( counters[server_url]);
                }
            });

    }
    
    
    
    
    
    function wpstream_check_live_connections_on_start( parent,show_id,server_id,data){
        
        // server_url is dns change
      
        wpstream_check_dns_avb_callback(show_id,server_id,
            function(server_status){
               
                if(server_status.status==='active' ){
                    clearInterval( counters[server_id]);
                    
                    parent.addClass('wpstream_show_started ');
                    console.log('added wpstream_show_started  ');
                    parent.find('.pending_streaming').removeClass('show_stream_data').addClass('hide_stream_data');
                    parent.find('.wpstream_ready_to_stream ').removeClass('hide_stream_data').addClass('show_stream_data');
                    parent.find('.external_software_streaming  ').removeClass('hide_stream_data').addClass('show_stream_data');
                    parent.find('.view_channel').addClass('show_stream_data');
                    
                    parent.find('.wpstream_ready_to_stream .start_webcaster').attr('data-webcaster-url',server_status.webCasterUrl);
                    parent.find('.wpstream_live_uri_text').text(server_status.obs_uri);
                    parent.find('.wpstream_live_key_text').text(server_status.obs_stream);
                    
              
                    parent.parent().find('.wpstream_live_data').attr('href',server_status.live_data_url);
                    
                 
                }else if(server_status.status==='error' ){
                    clearInterval( counters[server_id]);
                    var curent_content = parent.find('.wpstream_channel_status');
                    curent_content.html('<div class="wpstream_channel_status not_ready_to_stream"><span class="dashicons dashicons-dismiss"></span>Failed to start the channel. Please try again in a few minutes.</div>');
                }
        });

    }
    
    function wpstream_check_live_connections_from_database( acesta,show_id,server_id){
     
        var server_status = wpstream_check_dns_avb_callback(show_id,server_id,
            function(server_status){
               
                if(server_status.status==='active' ){
                 
                    acesta.removeClass('show_stream_data').addClass('hide_stream_data');
                    acesta.parent().find('.wpstream_ready_to_stream').removeClass('hide_stream_data').addClass('show_stream_data');
                    acesta.parent().find('.view_channel').addClass('show_stream_data');
                    
                    acesta.parent().find('.wpstream_ready_to_stream .start_webcaster').attr('data-webcaster-url',server_status.webCasterUrl);
                    acesta.parent().find('.wpstream_live_uri_text').text(server_status.obs_uri);
                    acesta.parent().find('.wpstream_live_key_text').text(server_status.obs_stream);
                    
                    acesta.parent().find('.wpstream_live_data').attr('href',server_status.live_data_url);
                    
                    
                    clearInterval( counters[show_id]);
                }else if(server_status.status==='error' ){
                  
                    clearInterval( counters[show_id]);
                    var curent_content = acesta.parent().find('.wpstream_channel_status ');
                    curent_content.html('<div class="wpstream_channel_status not_ready_to_stream"><span class="dashicons dashicons-dismiss"></span>Failed to start the channel. Please try again in a few minutes.</div>');
                }
            });

    }
    
  
    function wpstream_check_server_status(url_param,callback) {
    
        var url = url_param ;
        var status='';
        $.ajax({
            url: url,
            type: "get",
            cache: false,
            dataType: 'jsonp', // it is for supporting crossdomain
            crossDomain : true,
            asynchronous : false,
            timeout : 1500, // set a timeout in milliseconds
            callback:'',
            complete : function(xhr) {
       
                if(xhr.status == "200" || xhr.status == "400") {
                    callback(true);
                }
                else {
                    callback(false);
                }
            }
       });
    }
            
            
    $('.stop_server').click(function(event){
        event.preventDefault();
        var ajaxurl             =   wpstream_start_streaming_vars.admin_url + 'admin-ajax.php';
        var acesta              =   $(this);
        var show_id             =   parseFloat( $(this).attr('data-show-id') );
        var nonce               =   $('#wpstream_stop_event_nonce').val();
        var parent              =   $(this).parent().parent();
        
        acesta.text('turning off...');
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            timeout: 300000,

            data: {
                'action'            :   'wpstream_stop_server',
                'security'          :   nonce,
                'show_id'           :   show_id
                             
            },
            success: function (data) {
                acesta.text('Turn Off Channel');
                //wpstream_no_stream show_stream_data
                //pending_streaming hide_stream_data 
                //wpstream_ready_to_stream hide_stream_data
                //external_software_streaming hide_stream_data 
                parent.find('.wpstream_no_stream').addClass('show_stream_data').removeClass('hide_stream_data');
                parent.find('.pending_streaming').addClass('hide_stream_data').removeClass('show_stream_data');   
                parent.find('.wpstream_ready_to_stream').removeClass('show_stream_data').addClass('hide_stream_data');   
                parent.find('.external_software_streaming ').addClass('hide_stream_data');
                parent.find('.record_wrapper').show();
                parent.find('.start_event').show();
                parent.find('.close_event').show();
                parent.find('.server_notification').text('Broadcast Offline.');
         
            },
            error: function (jqXHR,textStatus,errorThrown) {
             
            }
            
        });
  
    });
            
            
            
            
            
            
            
     
    $('.start_event').click(function(event){
        event.preventDefault();
        var ajaxurl             =   wpstream_start_streaming_vars.admin_url + 'admin-ajax.php';
        var acesta              =   $(this);
        var notification_area   =   $(this).parent().find('.event_list_unit_notification');
        var curent_content      =   $(this).parent().find('.server_notification');
        var is_record           =   false;
        var is_encrypt          =   false;
        var show_id             =   parseFloat( $(this).attr('data-show-id') );
        var nonce               =   $('#wpstream_start_event_nonce').val();
        var parent              =   $(this).parent().parent();
        
        if( $(this).parent().find('.record_event').is(":checked") ){
            is_record   =   true;
        }
        
        if( $(this).parent().find('.encrypt_event').is(":checked") ){
            is_encrypt   =   true;
        }
        
        
        
        
        
        var is_fb               =   0;
        var is_youtube          =   0;
        var is_twich            =   0;
        var youtube_rtmp        =   $(this).parent().find('.wpstream_youtube_rtmp').val();
        var twich_rtmp          =   $(this).parent().find('.wpstream_twich_rtmp').val();
        
        if( $(this).parent().find('.wpstream_on_facebook').is(":checked") ){
            is_fb   =   1;
        }
        
        if( $(this).parent().find('.wpstream_on_youtube').is(":checked") ){
            is_youtube   =   1;
        }
        
        if( $(this).parent().find('.wpstream_on_twich').is(":checked") ){
            is_twich   =   1;
        }
        
        
       // $(this).unbind();
        $(this).hide();
        parent.find('.record_wrapper').hide();
        parent.find('.close_event ').hide();
        parent.find('.wpstream_event_streaming_local').hide();
        parent.find('.wpstream_show_settings').hide();
        
        curent_content.html('<div class="wpstream_channel_status not_ready_to_stream"><span class="dashicons dashicons-dismiss"></span>Your Event is starting. Please wait..<img class="" src="'+wpstream_start_streaming_vars.loading_url+'" alt="loading"></div>');
        parent.find('.multiple_warning_events').html('Starting an Event may take 1-2 minutes. Please be patient.');
    

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            timeout: 300000,

            data: {
                'action'            :   'wpstream_give_me_live_uri',
                'security'          :   nonce,
                'show_id'           :   show_id,
                'is_record'         :   is_record,
                'is_encrypt'        :   is_encrypt,
                'is_fb'             :   is_fb,
                'is_youtube'        :   is_youtube,
                'is_twich'          :   is_twich,
                'youtube_rtmp'      :   youtube_rtmp,
                'twich_rtmp'        :   twich_rtmp
                
            },
            success: function (data) {
              
                if(data.conected===true){
                   
                        if(data.event_data==='err1'){
                            curent_content.html('<div class="wpstream_channel_status not_ready_to_stream"><span class="dashicons dashicons-dismiss"></span>You don\'t have enough streaming bandwidth to start a new event!</div>');
                        }else if(data.event_data ==='failed event creation'){
                            curent_content.html('<div class="wpstream_channel_status not_ready_to_stream"><span class="dashicons dashicons-dismiss"></span>Failed to start the channel. Please try again in a few minutes.</div>');
                        }else{
                            curent_content.empty();
                            parent.find('.wpstream_no_stream').removeClass('show_stream_data').addClass('hide_stream_data');
                            parent.find('.pending_streaming').removeClass('hide_stream_data').addClass('show_stream_data');        
                           
                            var counter =  setInterval( function (){ wpstream_check_live_connections_on_start(parent,show_id,data.event_data,data)},10000);
                            counters[data.event_data]=counter;
                        }
            
                }else{
                    parent.find('.wpstream_no_stream').addClass('show_stream_data').empty().html('<div class="error_notice">'+data.error+'</div>');
                }
                
            },
            error: function (jqXHR,textStatus,errorThrown) {
             
            }
        });
        
    });
    
    
});


function wpstream_check_dns_avb_callback(show_id,server_id,callback){
       
       var ajaxurl             =   wpstream_start_streaming_vars.admin_url + 'admin-ajax.php';
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            timeout: 3000000,

            data: {
                'action'            :   'wpstream_check_event_status',
                'server_id'         :   server_id,
                'show_id'           :   show_id
             
            },
            success: function (data) {
                console.log(data);
               var obj = JSON.parse(data);
              
                if(obj.status=='active'){
                    callback(obj);
                }else if(obj.status=='error'){
                    callback(obj);
                }else{
                    callback(false); 
                }
                
            }, error: function (jqXHR,textStatus,errorThrown) {
              
            }
      });
}


function wpstream_enable_cliboard(parent){
    
    jQuery(parent).find('.copy_live_uri').click(function(){
        alert('click');
        var value_uri = jQuery(parent).find('.wpstream_live_uri_text').text();
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(temp).val(value_uri).select();
        document.execCommand("copy");
        jQuery(temp).remove();
        
    });
    
    jQuery(parent).find('.copy_live_key').click(function(){
        var value_uri = jQuery(parent).find('.wpstream_live_key_text').text();
        var temp = jQuery("<input>");
        jQuery("body").append(temp);
        jQuery(temp).val(value_uri).select();
        document.execCommand("copy");
        jQuery(temp).remove();
        
    });
}