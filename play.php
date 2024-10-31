<?php
define('WP_USE_THEMES', true);
require_once(dirname(__FILE__).'/lib/thumbnail.class.php');
require_once(dirname(__FILE__).'/lib/prUtils.php');
require_once(dirname(__FILE__).'/lib/prPage.php');

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


function myauth_redirect($postid) {
	// Checks if a user is logged in, if not redirects them to the photoracer Login page.
	// this permit to customize the login page for the Photo Race
	if ( (!empty($_COOKIE[AUTH_COOKIE]) &&
				!wp_validate_auth_cookie($_COOKIE[AUTH_COOKIE])) ||
			(empty($_COOKIE[AUTH_COOKIE])) ) {
		nocache_headers();

		wp_redirect(get_option('siteurl') . 
			'/wp-content/plugins/photoracer/prwp-login.php?postid='.$postid.'&redirect_to=' . 
			urlencode(get_option('siteurl').'/wp-content/plugins/photoracer/play.php?prid='.$_REQUEST['prid'].'&postid='.$postid));
		exit();
	}
}


function letsgo ($post, $files, $current_user, $UPLOAD_DIR, $postid, $indexpath, $raceid) {
	global $wpdb;
	// startif : he can upload
	$util = new prUtils();
	$info="";			
	
	if ($files['imagefile']["size"] == 0) {
		$info = '<p>'.
					'<b>ATTENZIONE</b></p>'.
					'<br><p>Il file sembra essere vuoto</p>'.
				  	'<img src="'.get_option('siteurl').'/wp-content/plugins/photoracer/'.$indexpath.'/med_'.$destnamefile.'">'.
					'<br />'.
					'<p>Se non &egrave; tua <a href="'.$_SERVER['PHP_SELF'].'?prid='.$postid.'"><b>Riprova a fare l&amp;upload</b></a></p>';
		 
			return $info;
	}
		
	// the upload is done
	$source=$files["imagefile"]["tmp_name"];
	$aname = explode(".",$files["imagefile"]["name"]);
	//PI: possible improvment : "what happens if namefile contains also char dot '.' ?"
	$aname[0]=$util->strNormalizeName($aname[0]);
	$destnamefile=$current_user->data->user_login."_".$aname[0].".".$aname[1];
	$dest=$UPLOAD_DIR."/".$destnamefile;

	if (file_exists($dest) && filesize($dest)>0){
		// file alredy exist
		$info = '<p>'.
				'<b>ATTENZIONE</b></p>'.
				'<br><p>Una foto con lo stesso nome &egrave; gi&agrave; presente eccola:</p>'.
			  	'<img src="'.get_option('siteurl').'/wp-content/plugins/photoracer/'.$indexpath.'/med_'.$destnamefile.'">'.
				'<br />'.
				'<p>Se non &egrave; tua <a href="'.$_SERVER['PHP_SELF'].'?prid='.$postid.'"><b>fai un altro upload</b></a> cambiando nome al file</p>';
	 
		return $info;
	} 

	$thumbnail = new thumbnail();
	// create medium and large thumbnails
	$lrgImage = $thumbnail->generate($source,$destnamefile, $UPLOAD_DIR, 'lrg_'.$current_user->data->user_login.'_'.$aname[0], 540); // large file
	$medImage = $thumbnail->generate($source,$destnamefile, $UPLOAD_DIR, 'med_'.$current_user->data->user_login.'_'.$aname[0], 200); // medium file
	//keep the original
	if ($mvrv = move_uploaded_file($source, $dest))
	{
		// If all files are ok
		if ($medImage && $lrgImage) 
		{
			//startif : create thumbs ok
			$info = '<p>'.
	  				'<b>File trasmesso con successo</b>'. 
	  				'<br /><img src="'.get_option('siteurl').'/wp-content/plugins/photoracer/'.$indexpath.'/'.'/med_'.$destnamefile.'">'.
					'</p>';
			
			$imgcomment = $post['desc'];
			//insert nel db
			$qimage = "INSERT INTO ".$wpdb->prefix.
					"photoracer (raceid, wpuid, imgpath, imgname, imgcomment, tinsert) values (".
						$raceid.", ".
						$current_user->data->ID.", ".
						"'/wp-content/plugins/photoracer/".$indexpath."/".$current_user->data->user_login."_".$aname[0].".".$aname[1]."', ".
						"'".$current_user->data->user_login."_".$aname[0].".".$aname[1]."', ".
						"'".wptexturize($imgcomment)."', '".
						date('Y-m-d H:i:s')."')";
			$wpdb->query($qimage);
			//update total count images
			$qupdatecount="update ".$wpdb->prefix."photoracer_admin set numphoto=numphoto+1;";
			$wpdb->query($qupdatecount);
			
			$prp = new prPage();
			$prp->refreshpage($postid);
			
			return $info;
		}
		else
		{
			//something goes wrong in thumbnails create
			$info = '<p>'.
					'<b>Problemi nella trasmissione del file</b></p>'. 
					'<br><p><a href="'.$_SERVER['PHP_SELF'].'?prid='.$postid.'"><b>Riprova a fare l&amp;upload</b></a></p>';
			return $info;
		}
	} else {
		//	something goes wrong in move_uploaded_files
		$info = '<p>'.
		  		'<b>Problemi nella trasmissione del file</b></p>'. 
		  		'<br><p><a href="'.$_SERVER['PHP_SELF'].'?prid='.$postRaceID.'"><b>Riprova a fare l&amp;upload</b></a></p>';
		return $info;
	}
}

$ok = 0;
$UPLOAD_DIR='';
$postRaceID = $_REQUEST['prid'];
if ($postRaceID != null)
	$q1 = "SELECT raceid, postid, active_from, active_to, upload_untill, maxphotoxuser, indexpath, name, numphoto FROM ".$wpdb->prefix."photoracer_admin WHERE postid='$postRaceID'";
else
	$ok = 0;
	
$out = $wpdb->get_row($q1);
$UPLOAD_DIR=dirname(__FILE__)."/".$out->indexpath;
//echo "Settato UPLOAD_DIR a :$UPLOAD_DIR <br/>";

if (is_user_logged_in()){
	$ok = 1;
	get_currentuserinfo();	
}
else 
{
	myauth_redirect(isset($_REQUEST['prid'])?$_REQUEST['prid']:$out->postid);
};

ini_set("memory_limit", "80M");
ini_set("upload_max_filesize", "80M");
ini_set("upload_tmp_dir", dirname(__FILE__));


get_header();
//echo "<pre>".print_r($current_user)."</pre>";
//echo "<pre>".$current_user->data->user_login."</pre>";


$topmenu = " <a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/play.php?prid=".$out->postid."\">pubblica foto</a> ".
		"| <a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/crono.php?prid=".$out->postid."\">ultime pubblicate</a> ".
		"| <a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/mostviewed.php?prid=".$out->postid."\">le pi&ugrave; viste</a> ".
		"| <a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/mostvoted.php?prid=".$out->postid."\">le pi&ugrave; votate</a> ".
		"| <a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/prwp-login.php?prid=".$out->postid."&action=logout&redirect_to=".urlencode($_SERVER['PHP_SELF'])."\">logout</a> ";
 
?>
	
<?php 
//print_r($_POST);
//print_r($_FILES);

$today = date('Y-m-d');
$q_check = "SELECT count(imgid) as quante from ".$wpdb->prefix."photoracer WHERE wpuid=".$current_user->ID." and visibile=1 and raceid=".$out->raceid;
$o_check = $wpdb->get_results($q_check);
//print "<br><pre>";
//print_r($out);
//print $q_check;
//print_r($o_check);
//print_r($current_user);
//print "</pre>";
if ($o_check[0]->quante >= $out->maxphotoxuser){
?>
<div class="titolo_pag"><h1><?php echo $out->name?></h1></div>
	<div class="pagina">
		<div class="pagina-int">
			<div id="content" class="narrowcolumn">
			<p><?php echo $topmenu; ?></p>
			<h3>Mi spiace hai gi&agrave; caricato <?php echo $o_check[0]->quante;?> foto. 
			<br />Si pu&ograve; partecipare con <?php echo $out->maxphotoxuser; ?> foto.</h3>
			</div>
		</div>
	</div>
<?php
}
elseif( $today < substr($out->active_from, 0, 10))
{
?>
<div class="titolo_pag"><h1><?php echo $out->name?></h1></div>
	<div class="pagina">
		<div class="pagina-int">
			<div id="content" class="narrowcolumn">
			<p><?php echo $topmenu; ?></p>
			<h3>Mi spiace, non si possono caricare foto. Il Concorso comincia il 
			<?php echo substr($out->active_from, 0, 10); ?> ed oggi &egrave solo il <?php echo $today?></h3>
			</div>
		</div>
	</div>
<?php
}
elseif ($today > substr($out->upload_untill, 0, 10))
{
?>
<div class="titolo_pag"><h1><?php echo $out->name?></h1></div>
	<div class="pagina">
		<div class="pagina-int">
			<div id="content" class="narrowcolumn">
			<p><?php echo $topmenu; ?></p>
			<h3>Mi spiace, non si possono caricare foto.</h3>
			<p>Ultima data consentita per caricare le foto: <?php echo $out->upload_untill; ?></p>
			<p>Ultima data consentita per votare le foto: <?php echo $out->active_to; ?></p>
			</div>
		</div>
	</div>
<?php
} else {
	if(!empty($_POST) && $_FILES['imagefile']['tmp_name']) 
	{
		
		$first='<div class="titolo_pag"><h1>'.$out->name.'</h1></div>'.
				'<div class="pagina">'.
					'<div class="pagina-int">'.
						'<div id="content" class="narrowcolumn">'.
						'<p>'.$topmenu.'</p>'.
						'Ciao, '.$current_user->data->user_login.", ".
						'<a href="prwp-login.php?action=logout&postid='.$postid.'">logout</a>';
		
		$mybody = letsgo(
				$_POST, 
				$_FILES,
				$current_user,
				$UPLOAD_DIR,
				isset($_REQUEST['prid'])?$_REQUEST['prid']:$out->postid,
				$out->indexpath,
				$out->raceid);
		
		$last = '</div></div></div>';
		
		echo $first . $mybody . $last;
	}
	else
	{
?>
	<div class="titolo_pag"><h1><?php echo $out->name?></h1></div>
	<div class="pagina">
		<div class="pagina-int">
			<div id="content" class="narrowcolumn">
<p><?php echo $topmenu; ?></p>
Ciao, <?php echo $current_user->data->user_login ?>, 
<a href="prwp-login.php?action=logout&postid=<?php echo isset($_REQUEST['prid'])?$_REQUEST['prid']:$out->postid; ?>">logout</a><br />
			 <form action="<?php echo $_SERVER['PHP_SELF'];?>?prid=<?php echo $postRaceID; ?>" method="post" enctype="multipart/form-data">
			 <table>
				<tr valign="top">
					<td><strong>upload foto</strong></td>
					<td></td>
				</tr>
				<tr>
					<td align="right">file * :</td>
					<td><input type="file" name="imagefile" id="imagefile"  value="" /></td>
				</tr>
				<tr valign="top">
					<td align="right">titolo: </td>
					<td><input type="text" name="desc" id="desc"  value="" /></td>
				</tr>
				<tr>
					<td colspan="3" align="center"><input type="submit" name="s" id="s"  value="Submit" /></td>
					<td></td>
				</tr>
				</table>
				</form>
			</div>
		</div>
	</div>
	<?php  
	}
}


	

get_sidebar(); 
get_footer(); 
?>
