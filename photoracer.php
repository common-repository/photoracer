<?php
/*
Plugin Name: PhotoRacer
Plugin URI: http://palmonaz.altervista.org/z/photoracer
Description: This plugin create photo race where subscribed users could upload pictures and everybody can vote (cookie+capthca voting).
Author: Paolo Palmonari
Version: 1.0
Author URI: http://palmonaz.altervista.org/
*/

function photoracer_install() 
{
    global $wpdb;
    
    $table1 = $wpdb->prefix."photoracer_admin";
    $table2 = $wpdb->prefix."photoracer";
    $table3 = $wpdb->prefix."photoracer_votes";
    $structure1 = "CREATE TABLE IF NOT EXISTS $table1 (".
    "raceid int(10) unsigned NOT NULL auto_increment, ".
    "postid bigint unsigned not null, ".
    "active_from datetime NOT NULL, ".
    "active_to datetime NOT NULL, ".
    "upload_untill datetime NOT NULL, ".
    "indexpath varchar(255) NOT NULL, ".
    "name varchar(255),".
    "headcontent text, ".
    "numphoto int unsigned default 0,".
    "maxphotoxuser int unsigned default 1,".
    "PRIMARY KEY  (raceid),".
    "UNIQUE KEY (active_from,active_to,indexpath)".
    ");";
    $structure2 = "CREATE TABLE IF NOT EXISTS $table2 (".
    "raceid int(10) unsigned default NULL, ".
    "wpuid bigint(20) unsigned default NULL, ".
    "imgid bigint(20) unsigned NOT NULL auto_increment, ".
    "imgpath varchar(255) NOT NULL, ".
    "imgname varchar(255) default NULL, ".
    "imgcomment varchar(500) default NULL, ".
    "sumvotes int unsigned default '0', ".
    "imgcountview bigint(20) unsigned default '0', ".
    "tinsert datetime default NULL, ".
    "visibile tinyint default 1, ".
    "UNIQUE KEY (raceid,wpuid,imgid), ".
    "PRIMARY KEY imgid (imgid)".
    ");";
    $structure3 = "CREATE TABLE IF NOT EXISTS $table3 (".
    "imgid bigint(20) unsigned NOT NULL, ".
    "voter varchar(32) NOT NULL, ".
    "tvote timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, ".
    "vote smallint unsigned CHECK (vote <= 10 and vote >= 0), ".
    "captcha_text varchar(5) NOT NULL, ".
    "UNIQUE KEY (imgid, voter)".
    ");";
	$wpdb->query($structure1);
	$wpdb->query($structure2);
	$wpdb->query($structure3);
}




function photoracer_create_race()
{

}



//this is now deprecated.
//add_action(’activate_bot/bots.php’, ‘bot_install’);
register_activation_hook(__FILE__, 'photoracer_install');
//add_action ( 'hook_name', 'your_function_name', [priority], [accepted_args] );
//add_action(’wp_footer’, ‘bot’);

// layout for admin page
function photoracer_menu()
{
    global $wpdb;
    include dirname(__FILE__).'/photoracer-admin.php';
}

// the container
function photoracer_admin_actions()
{
	add_options_page(__('"Photo Racer"'), __('Photo Racer'), 5, basename(__FILE__), 'photoracer_menu');
}

add_action('admin_menu', 'photoracer_admin_actions');

?>
