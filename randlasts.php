<?php
global $wpdb;
if ( defined('ABSPATH') )
{
	require_once( ABSPATH . 'wp-config.php');
}
else
{
    require_once('../../../wp-config.php');
    require_once('../../../wp-includes/wp-db.php');
}

$q2 = "select raceid,imgid,imgpath,imgname from ".$wpdb->prefix."photoracer where visibile=1 order by imgid desc";
//echo "$q2<br />";
$o2 = $wpdb->get_results($q2);
$who = rand(0,(count($o2) - 1));
$q3 = "select indexpath,postid from ".$wpdb->prefix."photoracer_admin where raceid=".$o2[$who]->raceid;
//echo "$q3<br />";
$o3 = $wpdb->get_row($q3);


$result = '<a href="'.get_option('siteurl').'/?page_id='.$o3->postid.'">'.
		'<img src="'.get_option('siteurl').'/wp-content/plugins/photoracer/'.$o3->indexpath.'/med_'.$o2[$who]->imgname.'">'.
		'</a>';

echo $result;
?>