<?php
define('WP_USE_THEMES', true);
require_once(dirname(__FILE__).'/lib/thumbnail.class.php');
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

require_once 'lib/prPage.php';
$prp = new prPage();
$browse=$prp->browse(
			$postRaceID,
			(isset($_REQUEST['p']) && is_numeric($_REQUEST['p'])) ? $_REQUEST['p'] : 0
		);
?>
<p>
<a href="<?php echo get_option('siteurl').'/wp-admin/options-general.php?page=photoracer.php'?>">
torna alla pagina di amministrazione</a>
</p>
<p>Contenuti.</p>
<hr>
<p><?php echo $browse ?></p>
<hr>
