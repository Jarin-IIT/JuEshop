/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * player,'.$live_event_uri_final.','.$live_conect_views.'
 */

window.WebSocket = window.WebSocket || window.MozWebSocket;
if (!window.WebSocket) {
  console.log("Sorry, but your browser does not support WebSockets");
}

                  
var is_player_play="no";
var counters_storage=[];
var players_track=[];
var players_start_timer=[];
var timeQueue;
var chat_conected='';


/*
 * 
 * Initialize player
 * 
 * 
 * 
 */

function wpstream_player_initialize(now,live_event_uri_final,live_conect_views,autoplay){
    var is_autoplay=true;
    if(autoplay !== 'autoplay'){
        is_autoplay=false;
    }
        
     console.log("is_autoplay "+is_autoplay);   
    var player = videojs('wpstream-video'+now,{
            html5: {
            hls: {
                bandwidth: 500000,
                useBandwidthFromLocalStorage: true,
                overrideNative: !videojs.browser.IS_SAFARI,
                smoothQualityChange: true
                }
            },
            errorDisplay: false,
            autoplay:is_autoplay,
            preload:"auto"
    });
    
    players_track[now]=player;
}


/*
 * 
 * Player start to play
 * 
 * 
 * 
 */
function wpstream_player_play(now,live_event_uri_final,live_conect_views){
    
    var check_autoplay=jQuery('#wpstream-video'+now).attr('data-autoplay');
    if(check_autoplay==='no_autoplay'){
        console.log('auto play return');
        return;
    }
    
    var player = players_track[now];
    wpstream_player_load(player,live_event_uri_final,live_conect_views);
    jQuery("#wpestream_live_counting").appendTo(jQuery('#wpstream-video'+now));
    
    
    console.log('player play ---');
    
    
    timeQueue = [];
    var player_start_interval=  setInterval(function(){
        timeQueue.push(player.currentTime());
        if (timeQueue.length > 30){
            timeQueue.shift();
            if (timeQueue[0] === timeQueue[timeQueue.length -1]){

                if (!player.paused() || player.currentTime() === 0){
                    timeQueue = [];
                    try{
                        player.currentTime(0);
                    }catch(err){

                    }
                    wpstream_player_load(player,live_event_uri_final,live_conect_views);
                }

            }
        }
    }, 1000);
    
    players_start_timer[now]=player_start_interval;
}



/*
 * 
 * Initial player load
 * 
 * 
 * 
 */


function wpstream_player_load(player,live_event_uri_final,live_conect_views){
    player.src({
        src:  live_event_uri_final,
        type: "application/x-mpegURL"
    });
    player.play();
    is_player_play="yes";
  
}




/*
 * 
 * Start Wbesocket conection
 * 
 * 
 * 
 */

var first_start=0;
var socket_connection;
function wpstream_read_websocket_info(event_id,player, player_wrapper, socket_wss_live_conect_views_uri, event_uri){
  
    // test if 
    if( typeof(socket_connection) === "undefined"  && typeof( socket_wss_live_conect_views_uri ) !== 'undefined' && socket_wss_live_conect_views_uri!=='' ){
        socket_connection = new WebSocket(socket_wss_live_conect_views_uri);
        counters_storage[player]=socket_connection;
    }else{
        return;
    }
   

    // of soccket conection fail - return
    if(typeof(socket_connection) === "undefined"){
        return;
    }  
      
    var now         =   player_wrapper.attr('data-now');
    // log the succesful connection
    socket_connection.onopen = function () {
        console.log("connected.");
        socket_connection.send(`{"type":"register","data":"${now}"}`);
    };
    
    
    
    
    

    // on the connection close
    socket_connection.onclose = function(){
        console.log("closed. reconnecting...");
        // do a timeout to check socket conection status
        setTimeout(function(){ 
            wpstream_read_websocket_info(player,socket_wss_live_conect_views_uri ); 
        }, 5 * 1000);
    };


    // on sockect error - log error
    socket_connection.onerror = function (error) {
        console.log("onerror: ", error);
    };


    // on socket message
    socket_connection.onmessage = function (message) {
        try {
            var json = JSON.parse(message.data);
        } catch (e) {
            console.log("Invalid JSON: ", message.data);
            return;
        }
       
        
        
        if (json.type === "viewers" ) { 
            // here we count viewers

            count = json.data;
            console.log("viewers: " + count);
            var view_box=jQuery("#"+player+" .wpestream_live_counting");
            view_box.css("background-color","rgb(174 69 69 / 90%)");
            view_box.html( count + " Viewers");

        } else  if (json.type === "onair") { 
            
            // here we check if data on stream
         
            if(json.data){
                // we have data we should play
              
                wpstream_player_play(now,event_uri,socket_wss_live_conect_views_uri);
                player_wrapper.find('.wpstream_not_live_mess').hide();
                first_start=1;
                      
            }else{
                // channel is live but without data
            
                player_wrapper.find('.wpstream_not_live_mess_mess').text(wpstream_player_vars.server_up);
                player_wrapper.find('.wpstream_not_live_mess').show();
                
                // test to see if we started already      
                if(first_start===1){
                    wpstream_player_stop(now,player,event_id);
                }
            }

        } else {
            console.log("Unknown type:", json);
        }
        
    };
}



/*
 * 
 * Check player status
 * 
 * 
 * 
 */

function wpstream_check_player_status_ticker(player_wrapper,event_id){
    wpstream_interval_code(player_wrapper,event_id);
   
    setInterval(function(){
        wpstream_interval_code(player_wrapper,event_id);
    
    }, 60000);
    
   
}


/*
 * 
 * Check player status - Interval function
 * 
 * 
 * 
 */
function wpstream_interval_code(player_wrapper,event_id){

            var ajaxurl     = wpstream_player_vars.admin_url + 'admin-ajax.php';
        
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action'                    :   'wpstream_player_check_status',
                    'event_id'                  :   event_id
                },
                success: function (data) {     

                    var now         =   player_wrapper.attr('data-now');
                    var player_id   =   player_wrapper.attr('id');
                            
                    if(typeof(connect)==='function' ){               
                        connect(data.chat_url);
                    }
                                
                    
                    if(data.started === 'yes'){
                        chat_conected='yes';
                    
                        if(is_player_play === "no"){
                                player_wrapper.find('.wpstream_not_live_mess').hide();
                             
                                is_player_play="yes";
                                jQuery("#wpestream_live_counting").show();
                               
                        }
                        wpstream_read_websocket_info (event_id,player_id,player_wrapper ,data.live_conect_views,data.event_uri);
                       
                    }else if(data.started === 'no'){
                        if(is_player_play === "yes"){
                           
                              
                            player_wrapper.find('.wpstream_not_live_mess').show();
                            is_player_play  =   "no";
                            wpstream_player_stop(now,player_id,event_id);
                            
                            if( typeof(showChat) === 'function' && chat_conected === 'yes' ){
                                showChat('info', null, wpstream_player_vars.chat_not_connected);
                                chat_conected='no';
                            }                           
                        }
                    }
                    

                },
                error: function (errorThrown) { 

                }
            });
}




/*
 * 
 * Player Stop
 * 
 * 
 * 
 */

function wpstream_player_stop(now,player_id,event_id){
 
    var player = players_track[now];

    player.autoplay=false;
    player.pause();
    
    clearInterval( players_start_timer[now]);
    arrayRemove(players_start_timer,now);
    delete  players_start_timer[now];

     
}


function wpstream_plater_close_all_socket_connection(){
    counters_storage.forEach(function(item){
       item.close();
    });
}


function arrayRemove(arr, value) { return arr.filter(function(ele){ return ele != value; });}



/*
 * 
 * Ready Event 
 * 
 * 
 * 
 */

jQuery(document).ready(function ($) {
    
    var event_id;  
    var player_wrapper;
    jQuery('.wpstream_live_player_wrapper').each(function(){
        if($(this).hasClass('wpstream_low_latency')){
            return;
        }
        event_id          =   jQuery(this).attr('data-product-id');
        player_wrapper    =   jQuery(this);

        wpstream_check_player_status_ticker(player_wrapper,event_id);
    });
    
});


function wpstream_force_clear_transient(event_id){
    var ajaxurl     = wpstream_player_vars.admin_url + 'admin-ajax.php';
            
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
   
        data: {
            'action'                    :   'wpstream_force_clear_transient',
            'event_id'                  :   event_id,
        },
        success: function (data) {  
        }, error: function (errorThrown) { 

        }
    });
                
}



    function initPlayer(playerID,low_latency_uri,muted,autoplay){
        var is_muted    =   false;
        var is_autoplay =   true;
        if(muted === 'muted'){
            is_muted=true;
        }
        
        if(autoplay !== 'autoplay'){
            is_autoplay=false;
        }
        
        console.log('is_muted '+is_muted + '/ '+is_autoplay);
        
        sldpPlayer = SLDP.init({
            container:          playerID,
            stream_url:         low_latency_uri,
            buffering:          500,
            autoplay:           is_autoplay,
            height:             "parent",
            width:              "parent",
            muted:              is_muted,
        });

    };

    function removePlayer(){
      sldpPlayer.destroy();
    }