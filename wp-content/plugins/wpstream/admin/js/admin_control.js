/*global $, jQuery, */
var counters={};
    
jQuery(document).ready(function ($) {
    "use strict";
    

    generate_download_link();
    generate_delete_link();
    social_media_toggle('wpstream_on_facebook');
    social_media_toggle('wpstream_on_youtube');
    social_media_toggle('wpstream_on_twich');
    
    
    function social_media_toggle(social_class){
        
        jQuery('.'+social_class).on('change',function(){

            if( $(this).prop('checked') ){
                jQuery($(this).parent().parent().find( '.'+social_class+'_container' )).slideDown('100');
            }else{
                jQuery($(this).parent().parent().find( '.'+social_class+'_container' )).slideUp('100');
            }  

        });
    }
    
    
    
    jQuery('.wpestate_notices .notice-dismiss').on('click',function(){
       
        var ajaxurl     = wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
        var notice_type = $(this).parent().attr('data-notice-type');
        var nonce       = $('#wpstream_notice_nonce').val();
        

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action'                    :   'wpstream_update_cache_notice',
                'notice_type'               :   notice_type,
                'security'                  :   nonce
            },
            success: function (data) {     

            
            },
            error: function (errorThrown) { 
              
            }
        });
    });
    
    
    
    
    function generate_delete_link(){
        $('.wpstream_delete_media').on('click',function(){
            
           
            var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
            var video_name          =   $(this).attr('data-filename').trim();
            var acesta              =   $(this);
            var parent              =   $(this).parent();

          
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,

                data: {
                    'action'            :   'wpstream_get_delete_file',
                    'video_name'        :   video_name

                },
                success: function (data) {
                    parent.remove();
                   

                },
                error: function (errorThrown) {
                }
            });
        });

    
    }
    
    function generate_download_link(){
            
        $('.wpstream_get_download_link').on('click',function(){
            var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
            var video_name          =   $(this).attr('data-filename');
            var acesta              =   $(this);
            var parent              =   $(this).parent();

            jQuery(this).remove();
            parent.find('.wpstream_download_link').show().text('please wait...');



            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,

                data: {
                    'action'            :   'wpstream_get_download_link',
                    'video_name'        :   video_name,

                },
                success: function (data) {

                    if(String(data)==='toolarge'){
                         parent.find('.wpstream_download_link').show().text(wpstream_admin_control_vars.no_band); 
                    }else{
                        parent.find('.wpstream_download_link').show().text(wpstream_admin_control_vars.download_mess);
                        parent.find('.wpstream_download_link').show().attr('href',data);

                    }

                },
                error: function (errorThrown) {
                }
            });
        });
    
    }

    
  
    
    
    $( '.inputfile' ).each( function(){
		var $input	 = $( this ),
			$label	 = $input.next( 'label' ),
			labelVal = $label.html();

		$input.on( 'change', function( e )
		{
			var fileName = '';

			if( this.files && this.files.length > 1 )
				fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
			else if( e.target.value )
				fileName = e.target.value.split( '\\' ).pop();

			if( fileName )
				$label.find( 'span' ).html( fileName );
			else
				$label.html( labelVal );
		});

		// Firefox bug fix
		$input
		.on( 'focus', function(){ $input.addClass( 'has-focus' ); })
		.on( 'blur', function(){ $input.removeClass( 'has-focus' ); });
    });
    
    var form = $('.direct-upload');
    var filesUploaded = [];
    var folders = [];

   // var new_file_name='';  
    
    form.fileupload({
        url: form.attr('action'),
        type: form.attr('method'),
        datatype: 'xml',
            add: function (event, data) {
               
               
               if( data.files[0].type!=='video/mp4' && data.files[0].type!=='video/quicktime'){
                    jQuery('#wpstream_uploaded_mes').empty().html(wpstream_admin_control_vars.not_accepted);
                   return;
               }
                    
          
             
               var file_size=(parseInt(data.files[0].size,10))/1000000;
               var user_storage = jQuery('#wpstream_storage').val();
               var user_band    = jQuery('#wpstream_band').val();
               
         
               
               if(file_size > user_storage || file_size>user_band){
                    jQuery('#wpstream_uploaded_mes').empty().html(wpstream_admin_control_vars.no_band_no_store);
                    return;
               }
               
               
                $('#wpstream_label_action').text(wpstream_admin_control_vars.uploading)
                
                jQuery('#wpstream_uploaded_mes').empty().html();
                // Show warning message if your leaving the page during an upload.
                window.onbeforeunload = function () {
                    return 'You have unsaved changes.';
                };

                var file = data.files[0];
                
                //console.log('file'+JSON.stringify(file));
                form.find('input[name="Content-Type"]').val(file.type);
                form.find('input[name="Content-Length"]').val(file.size);

                // Actually submit to form to S3.
                data.submit();

                // Show the progress bar
                // Uses the file size as a unique identifier
                var bar = $('<div class="progress" data-mod="'+file.size+'"><div class="bar"></div></div>');
                $('.progress-bar-area').append(bar);
                bar.slideDown('fast');
            },
            progress: function (e, data) {
                // This is what makes everything really cool, thanks to that callback
                // you can now update the progress bar based on the upload progress.
                var percent = Math.round((data.loaded / data.total) * 100);
                $('.progress[data-mod="'+data.files[0].size+'"] .bar').css('width', percent + '%').html(percent+'%');
            },
            
            fail: function (e, data) {
                // Remove the 'unsaved changes' message.
                window.onbeforeunload = null;
                $('.progress[data-mod="'+data.files[0].size+'"] .bar').css('width', '100%').addClass('red').html('');
            },
            done: function (event, data) {
               
                window.onbeforeunload = null;
                $('.bar').remove();
                $('#wpstream_uploaded_mes').empty().html(wpstream_admin_control_vars.upload_complete);
                $('#wpstream_label_action').text(wpstream_admin_control_vars.upload_complete2);

                var new_file_name=data.files[0].name;

                
                var new_file_name_array=  data.files[0].name.split(".");
                var temp_file_name= new_file_name_array[0].split(' ').join('_');
                temp_file_name= temp_file_name.replace(/\W/g, '');           
                new_file_name=temp_file_name+'.'+new_file_name_array[new_file_name_array.length-1];
                
                
                
                
                var onclick_string=' Are you sure you wish to delete '+new_file_name+' ? ';

                var to_insert='<div class="wpstream_video_wrapper"><div class="wpstream_video_title"><div class="wpstream_video_notice"></div></div><div class="wpstream_video_title">';
                to_insert=to_insert+'<strong class="storage_file_name">File Name :</strong><span class="storage_file_name_real">'+new_file_name+' </span></div>';
                to_insert=to_insert+'<div class="wpstream_delete_media"  '; 
                to_insert=to_insert+' onclick=" return confirm('+onclick_string+') "';
                to_insert=to_insert+' data-filename="'+new_file_name+'"  >delete file</div>';
                to_insert=to_insert+'<div class="wpstream_get_download_link" data-filename="'+new_file_name+'">get download link</div> ';
                to_insert=to_insert+'<a href="" class="wpstream_download_link">Click to download! The url will work for the next 20 minutes!</a></div>';
                
                $('#video_management_title').after(to_insert);

                $('.wpstream_get_download_link').unbind('click');
                $('.wpstream_delete_media').unbind('click');

                generate_download_link();
                generate_delete_link();

               
            }
    });
            
    
  

    
   
    
    
    jQuery('#product-type').on('change',function(){
        
        var product_type= jQuery('#product-type').val();
        if(product_type==='live_stream' || product_type==='video_on_demand'){
            jQuery('._sold_individually_field').show();
        }
        
    });
    
    if(wpstream_findGetParameter('new_video_name')!=='' && wpstream_findGetParameter('new_video_name')!=null ){
        jQuery('#product-type').val('video_on_demand').trigger('change');
    }
    
    if(wpstream_findGetParameter('new_stream')!=='' && wpstream_findGetParameter('new_stream')!=null ){
        jQuery('#product-type').val('live_stream').trigger('change');
    }
    
    var product_type=  jQuery('#product-type').val();
  
    if ( product_type === 'video_on_demand' ) {
        jQuery('.show_if_video_on_demand' ).show();
       
    } 
    else  if ( product_type === 'live_stream' ) {
        $( '.show_if_live_stream' ).show();
        
    }
            
  

  
    
    function wpstream_findGetParameter(parameterName) {
        var result = null,
            tmp = [];
        location.search
            .substr(1)
            .split("&")
            .forEach(function (item) {
              tmp = item.split("=");
              if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
            });
        return result;
    }


    jQuery('#_subscript_live_event').change(function(){
        //alert('move it'+product_type);
        if ( product_type === 'video_on_demand' || product_type === 'live_stream' ) {
        
        }else{
            var value= jQuery(this).val();     
            if(value==="no"){
                jQuery("._movie_url_field").parent().removeClass("hide_if_subscription").show();
            }else{
                jQuery("._movie_url_field").parent().addClass("hide_if_subscription").hide();
            }
        }
    });

    jQuery('#_subscript_live_event').trigger('change');
   

    $('#wpstream_product_type').change(function(){
        $('.video_free').hide();
        $('.video_free_external').hide();
  
        if( $('#wpstream_product_type').val()=== "2"){
            $('.video_free').show();
        }
        if( $('#wpstream_product_type').val()=== "3"){
            $('.video_free_external').show();
        }
    });
    $('#wpstream_product_type').trigger('change');
    
 

    $('#wpstream_free_video_external_button').on( 'click', function(event) {
        var formfield = $('#wpstream_free_video_external').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var pathArray = html.match(/<media>(.*)<\/media>/);
            var mediaUrl = pathArray != null && typeof pathArray[1] != 'undefined' ? pathArray[1] : '';
          
            if(mediaUrl===''){
               mediaUrl = jQuery(html).attr("href");
            }
            jQuery('#wpstream_free_video_external').val(mediaUrl);
            tb_remove();
        };
        return false;
    });
    
    $('.close_event').click(function(event){
        event.preventDefault();
        var ajaxurl             =   wpstream_admin_control_vars.admin_url + 'admin-ajax.php';
        var acesta              =   $(this);
        var parent              =   $(this).parent().parent();
        var notification_area   =   $(this).parent().find('.event_list_unit_notificationx');
        var show_id             =   parseFloat( $(this).attr('data-show-id') );
        var nonce               =   $('#wpstream_start_event_nonce').val();
        //$(this).unbind();
        notification_area.text('Closing Event');
    
        
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action'            :   'wpstream_close_event',
                'security'          :   nonce,
                'show_id'           :   show_id
            },
            success: function (data) {       
                parent.remove();            
            },
            error: function (errorThrown) {
              
            }
        });
        
    });
    
  
    
    
 
    
    $('.category_featured_image_button').on( 'click', function() {
        var parent = $(this).parent();
        var formfield  = parent.find('#category_featured_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var imgurl = $('img', html).attr('src');
            parent.find('#category_featured_image').val(imgurl);
            var theid = $('img', html).attr('class');
            var thenum = theid.match(/\d+$/)[0];
            parent.find('#category_attach_id').val(thenum);
            tb_remove();
        };
        return false;
    });
  

});

  

