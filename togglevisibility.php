<?php
define('WP_USE_THEMES', true);
require_once(dirname(__FILE__).'/lib/thumbnail.class.php');
require_once dirname(__FILE__).'/lib/prPage.php';
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
$postRaceID = $_REQUEST['prid'];
$ok = 0;
$UPLOAD_DIR='';
if (is_user_logged_in()){
	$ok = 1;
	$usermeta = get_usermeta($current_user->ID,'wp_capabilities');
	//echo "<pre>";print_r($usermeta); echo "</pre>";
	if ($usermeta['administrator'] != 1) 
	{
?><!-- solo l'amministratore può fare questo task -->
<h2>Errore!</h2>
<div id="auth-error">Solo l'amministratore può eseguire questo task.</div>
<?php
	exit;
	}
}
else 
{
	auth_redirect();
};
$imgid = $_REQUEST['id'];
$v = $_REQUEST['v'];
$q = "UPDATE ".$wpdb->prefix."photoracer set visibile=$v where imgid=$imgid";
$wpdb->query($q);
$q2="select raceid from ".$wpdb->prefix."photoracer where imgid=$imgid";
$r2 = $wpdb->get_row($q2);
if ($r2->raceid) {
	$q3 = "update ".$wpdb->prefix."photoracer_admin set numphoto=numphoto".
			(intval($v) == 1 ? "+" : "-").
			"1 where raceid=".$r2->raceid;
	$wpdb->query($q3);
}


if (isset($_REQUEST['postid'])) {
	$prp = new prPage();
	$browse=$prp->browse($_REQUEST['postid']);
}
?>
<p>
<a href="<?php echo get_option('site_url').'/wp-admin/options-general.php?page=photoracer.php'?>">
torna alla pagina di amministrazione</a>
</p>
<p>Contenuti.</p>
<hr>
<p><?php echo $browse ?></p>
<hr>
