<?php
define('WP_USE_THEMES', true);
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
$postid=0;
$p=0;
if (isset($_REQUEST['pid']) || isset($_REQUEST['prid']))
	$postid = $_REQUEST['pid'] ? $_REQUEST['pid'] : $_REQUEST['prid'];
if (isset($_REQUEST['p']))
	$p = $_REQUEST['p'];
if ($postid == 0) exit;
$q1 = "SELECT raceid, active_from, active_to, indexpath, name, headcontent, numphoto FROM ".
		$wpdb->prefix."photoracer_admin where postid=$postid";
$out = $wpdb->get_row($q1);
$raceid = $out->raceid;
$headcontent = $out->headcontent;
$numphoto = $out->numphoto;
//check real num photo:
$qnumphoto = "select count(*) as quante from ".$wpdb->prefix."photoracer where visibile = 1 and raceid=$raceid";
//echo "$qnumphoto<br />";
$outnumphoto = $wpdb->get_row($qnumphoto);

//default is 10 photo per page 
$n_photo_x_page = 10;

require_once('lib/prPage.php');
$prp = new prPage();
$npages = $prp->calcpages($outnumphoto->quante, $n_photo_x_page);
$content = $prp->cronoview($out->name, $raceid, $postid, $p,  $npages, $outnumphoto->quante, $n_photo_x_page);
get_header();
echo $content;
get_sidebar();
get_footer();
?>