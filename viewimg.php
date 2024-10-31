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
if (isset($_REQUEST['id']))
	$imgid = $_REQUEST['id'];
$q1 = "select raceid, wpuid, imgid, imgpath, imgname, imgcomment, sumvotes, imgcountview, tinsert from ".
	$wpdb->prefix."photoracer where imgid=$imgid";	

$out = $wpdb->get_row($q1);

$q2 = "SELECT raceid, postid, active_from, active_to, upload_untill, indexpath, name, headcontent, numphoto FROM ".
		$wpdb->prefix."photoracer_admin where raceid=".$out->raceid;
		
$out2 = $wpdb->get_row($q2);

$raceid = $out2->raceid;
$headcontent = $out2->headcontent;
$numphoto = $out2->numphoto;
//default is 10 photo per page 
$n_photo_x_page = 10;

/*require_once('lib/prPage.php');
$prp = new prPage();
$npages = $prp->calcpages($numphoto, $n_photo_x_page);
$content = $prp->cronoview($raceid, $postid, $p,  $npages, $numphoto, $n_photo_x_page);*/


require_once(dirname(__FILENAME_).'/lib/prPage.php');
//require_once('lib/ascii_captcha.class.php');
require_once(dirname(__FILENAME_).'/lib/php_captcha.class.php');
require_once(dirname(__FILENAME_).'/lib/voting.php');
$prcookie=null;
//echo $_COOKIE['photoracerVote'] . "<br />";
if (!isset($_COOKIE['photoracerVote']))
{
	$prcookie = voting::createCookieVote();
	$prexpire = mktime() + 86400 * 50;
	setcookie('photoracerVote',$prcookie,$prexpire,'/');
	
} else {
	$prcookie = $_COOKIE['photoracerVote'];
}

//echo $_COOKIE['photoracerVote']."<br />";
//echo $prcookie . "<br />";

//imgid + voter sono chiave unica, quindi se il record esiste già nel db, è il db a non duplicare restituendo errore
$q3a = "select vote from ".$wpdb->prefix."photoracer_votes where imgid=$imgid and voter='$prcookie'";
$r3a = $wpdb->query($q3a);
$captcha=$capthcadata=$captchatext=null;
$today = date('Y-m-d');
if ($r3a && $r3a->vote == 0 && !isset($_REQUEST['voto']) && !isset($_REQUEST['invia']) && !isset($_REQUEST['captcha'])){
	/*$captcha = new ASCII_Captcha();
	$captcha->randomizeFonts();
	$captcha->create($captchatext);
	$capthcadata=$captcha->resultOnOneLine();*/
	if( $today <= substr($out2->active_to, 0, 10))
	{
		$captcha = new phpCaptcha();
		$capthcadata=$captcha->create($out2->indexpath);
		$captchatext=$captcha->getResultStr();
		$q3b = "update ".$wpdb->prefix."photoracer_votes set captcha_text='$captchatext' where voter='$prcookie' and imgid=$imgid";
		$wpdb->query($q3b);
	}
}
else if ($r3a && $r3a->vote >= 0){}
else {
	/*$captcha = new ASCII_Captcha();
	$captcha->randomizeFonts();
	$captcha->create($captchatext);
	$capthcadata=$captcha->resultOnOneLine();*/
	if( $today <= substr($out2->active_to, 0, 10))
	{
		$captcha = new phpCaptcha();
		$capthcadata=$captcha->create($out2->indexpath);
		$captchatext=$captcha->getResultStr();
		$q3b = "insert into ".$wpdb->prefix."photoracer_votes (imgid, voter, vote, captcha_text) values ($imgid, '$prcookie', 0, '$captchatext')";
		$wpdb->query($q3b);
		$q3c = "update ".$wpdb->prefix."photoracer set imgcountview=imgcountview+1 where imgid=$imgid";
		$wpdb->query($q3c);
	}
}

$voto=null;
$string4voto=null;
if (isset($_REQUEST['voto']) && isset($_REQUEST['invia']) && isset($_REQUEST['captcha'])) 
{
	
	$q4 = "select vote,	captcha_text from ".$wpdb->prefix."photoracer_votes where imgid=$imgid and voter='$prcookie'";
	//echo "$q4<br>";
	$r4 = $wpdb->get_row($q4);
	//print_r($r4);
	if ($r4->vote > 0 && $r4->vote <= 10)
		$voto = $r4->vote;
	else {
		if ($r4->captcha_text != $_REQUEST['captcha'])
		{
			$string4voto="Errore: i caratteri offuscati non sono stati inseriti correttamente, ".
			"era ".$r4->captcha_text." mentre tu hai digitato ".$_REQUEST['captcha'].", ".
			"<a href=\"".$_SERVER['PHP_SELF']."?id=$imgid\">riprova</a>";
		}
		else 
		{
			$voto = $_REQUEST['voto'];
			if ($voto <= 10 && $voto >= 1){
				$q5 = "update ".$wpdb->prefix."photoracer_votes set vote=$voto where imgid=$imgid and voter='$prcookie'";
				//echo "$q5<br />";
				$wpdb->query($q5);
				/*$q11="SELECT count(vote) as howmanyvote FROM ".$wpdb->prefix."photoracer_votes where vote > 0";
				$r11 = $wpdb->get_row($q11);*/
				$q12 = "SELECT sum(vote) as sumvotes FROM ".$wpdb->prefix."photoracer_votes where imgid=$imgid";
				$r12 = $wpdb->get_row($q12);
				//$rank = sprintf("%2.1f", ($r12->sumvotes / $r11->howmanyvote));
				//echo "rank = $rank<br />";
				$q13 = "UPDATE ".$wpdb->prefix."photoracer set sumvotes=".$r12->sumvotes." where imgid = $imgid";
				$wpdb->query($q13);
				$string4voto="Grazie per aver partecipato alla votazione di questa foto. Il tuo voto : <b>$voto</b>";
			} else {
				$string4voto = "Errore: Il voto deve essere un numero compreso tra 1 e 10";
			}
		}
	}
}
else
{
	$q4 = "select vote,tvote from ".$wpdb->prefix."photoracer_votes where imgid=$imgid and voter='$prcookie'";
	//echo "$q4<br />";
	$r4 = $wpdb->get_row($q4);
	//echo gettype($r4) . "<br />";
	if ($r4->vote > 0 && $r4->vote <= 10)
		$voto = $r4->vote;
	
	//echo $r4->vote . " [$voto]<br />";
}

//echo "voto=$voto<br />";

$large_thumb = preg_replace("/".$out->imgname."/", "lrg_".$out->imgname, $out->imgpath);
$content =
"<div class=\"titolo_pag\"><h1> ". $out2->name." </h1></div>".
"<div class=\"pagina\">".
		"<div class=\"pagina-int\">".
			"<div id=\"content\" class=\"narrowcolumn\">".
				prPage::fixed_menu(get_option("siteurl"), $out2->postid).
				"<p>postato il ". $out->tinsert ."<br/>".
					"da :".get_author_name($out->wpuid)."<br/>".
					$out->imgcomment."<br/>".  
					//"coockiehash=[".$prcookie."]<br/>".
				"</p>". 
				"<img src=\"".get_option("siteurl").$large_thumb."\">".
				($voto > 0 ? 
				"<br />Il tuo voto: <b>$voto</b>". 
					($r4->tvote ? " <br />effettuato il ".array_shift(split(" ",$r4->tvote))." alle ".array_pop(split(" ", $r4->tvote)) : "" ):
				"<br /><div id=\"vote\">".
					(($string4voto != null || $today > substr($out2->active_to, 0, 10))? 
						$string4voto : (
						"<fieldset><legend>Vota questa foto</legend>".
						"<form action=\"".$_SERVER['PHP_SELF']."?id=$imgid&a=vote\" method=\"get\">".
						"inserisci la stringa visualizzata ed il tuo voto <br />(maiuscole e minuscole fa differenza. Se non si riesce a leggere fai reload)<br />".
						"<img src=\"$capthcadata\"> <input type=\"text\" name=\"captcha\" size=\"5\">".
						"<br />".
						"<select name=\"voto\">".
							"<option label=\"1\">1</option>".
							"<option label=\"2\">2</option>".
							"<option label=\"3\">3</option>".
							"<option label=\"4\">4</option>".
							"<option label=\"5\">5</option>".
							"<option label=\"6\">6</option>".
							"<option label=\"7\">7</option>".
							"<option label=\"8\">8</option>".
							"<option label=\"9\">9</option>".
							"<option label=\"10\">10</option>".
						"</select>".
						"<input type=\"hidden\" name=\"id\" value=\"$imgid\">".
						"<input type=\"submit\" name=\"invia\" value=\"invia\">".
						"</form></fieldset>")).
				"</div>").
			"</div>".
		"</div>".
"</div>";
get_header();
echo $content;
get_sidebar();
get_footer();

function textrand($lenght){
	for ($i=0; $i<$lenght; $i++) {
        $randtext .= sprintf("%c",randNum(48,90));
	}
}
function initRand ()
{
    static $randCalled = FALSE;
    if (!$randCalled)
    {
        srand((double) microtime() * 1000000);
        $randCalled = TRUE;
    }
}
function randNum ($low, $high)
{
    initRand();
    $rNum = rand($low, $high);
    return $rNum;
}


?>