
<?php
require_once(dirname(__FILE__).'/lib/prUtils.php');
require_once(dirname(__FILE__).'/lib/prPage.php');

function listRaces() {
	global $wpdb;
	$pr_qlist="SELECT raceid, postid, active_from, active_to, upload_untill, indexpath, name, maxphotoxuser, numphoto  FROM ".$wpdb->prefix."photoracer_admin";
	//echo $pr_qlist."<br>\n";
	$out = $wpdb->get_results($pr_qlist);
	$webout = "";
	if ($out)
	{
	//echo "<pre>".print_r($out)."</pre>";
		$webout .= "<table border=1>".
		"<tr><td>raceid</td><td>postid</td><td>from</td><td>to</td><td>upload untill</td><td>path</td><td>name</td><td>maxphotoxuser</td><td>numphoto</td><td></td><td></td></tr>";
		foreach ($out as $k => $v)
		{
			$webout .= "<tr><td>".$v->raceid.
				"</td><td><a href=\"".get_option('siteurl')."/?page_id=".$v->postid."\">".$v->postid."</a>".
				"</td><td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/changefrom.php?rid=".$v->raceid."\">".$v->active_from."</a>".
				"</td><td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/changeto.php?rid=".$v->raceid."\">".$v->active_to."</a>".
				"</td><td>".$v->upload_untill."</a>".
				"</td><td>".$v->indexpath.
				"</td><td>".$v->name.
				"</td><td>".$v->maxphotoxuser.
				"</td><td>".$v->numphoto.
				"</td><td><a href=\"".
					get_option('siteurl')."/wp-content/plugins/photoracer/refresh_lastpublished.php?prid=".
					$v->postid."\">Refresh pagina d'entrata</a></td>".
				"<td><a href=\"".
					get_option('siteurl')."/wp-content/plugins/photoracer/prbrowse.php?prid=".
					$v->postid."\">I contenuti</a></td></tr>";
		}
		$webout .= "</table>";
	}
	else
	{
		$webout = "Nessun concorso impostato attualmente.";
	}
	return $webout;
}


global $wpdb;
$photorace_new=null;
$photorace_init_day=null;
$photorace_end_day=null;
$photorace_name=null;
$photorace_headcontent=null;
$photorace_end_upload=null;
$photorace_maxphotoxuser=1;
if (isset($_REQUEST['photorace_new'])) $photorace_new = $_REQUEST['photorace_new'];
if (isset($_REQUEST['photorace_name'])) $photorace_name = $_REQUEST['photorace_name'];
if (isset($_REQUEST['photorace_init_day'])) $photorace_init_day = $_REQUEST['photorace_init_day'];
if (isset($_REQUEST['photorace_end_day'])) $photorace_end_day = $_REQUEST['photorace_end_day'];
if (isset($_REQUEST['photorace_end_upload'])) $photorace_end_upload = $_REQUEST['photorace_end_upload'];
if (isset($_REQUEST['photorace_headcontent'])) $photorace_headcontent = $_REQUEST['photorace_headcontent'];
if (isset($_REQUEST['photorace_maxphotoxuser'])) $photorace_maxphotoxuser = $_REQUEST['photorace_maxphotoxuser'];

//echo "<pre>".print_r($_REQUEST)."</pre>";
/*echo "photorace_new=$photorace_new, photorace_name=$photorace_name, photorace_init_day=$photorace_init_day, photorace_end_day=$photorace_end_day<br>";
echo "preg_match1: ".preg_match("/^Aggiungi$/", $photorace_new)."<br>";
echo "preg_match2: ".preg_match("/^\d{4}-\d{2}-\d{2}$/", $photorace_init_day)."<br>";*/
if (
	preg_match("/^Aggiungi$/", $photorace_new) &&
	preg_match("/^\d{4}-\d{2}-\d{2}$/", $photorace_init_day) &&
	preg_match("/^\d{4}-\d{2}-\d{2}$/",$photorace_end_day) &&
	preg_match("/^\d{4}-\d{2}-\d{2}$/",$photorace_end_upload) &&
	$photorace_name != null
)
{
	//Aggiungo il concorso
	$Utils = new prUtils();
	$photorace_index_path = $Utils->strNormalizeName($photorace_name);
	
	if (! $Utils->isRightDir($photorace_index_path)){
		echo "<font size=+1 color=\"red\">Metti un Nome diverso, con quello che hai scelto non posso andare avanti</font>";
		echo "<br><a href=".$_SERVER['REQUEST_URI'].">Riprova</a>";
		exit;
	}
	if (file_exists(dirname(__FILE__)."/$photorace_index_path"))
	{
		echo "<font size=+1 color=\"red\">Esiste gi&agrave; un concorso con questo nome, cambia nome</font>";
		echo "<br><a href=".$_SERVER['REQUEST_URI'].">Riprova</a>";
		exit;
	}
	
	
	//creo la dir degli upload foto.
	$dirphoto=dirname(__FILE__)."/$photorace_index_path";
	//echo "mkdir $dirphoto<br>";
	umask('000');
	mkdir($dirphoto);
	//creo la nuova pagina
	// create post object
	class wm_mypost {
	    var $post_title;
	    var $post_content;
	    var $post_status;
	    var $post_author;    /* author user id (optional) */
	    var $post_name;      /* slug (optional) */
	    var $post_type;      /* 'page' or 'post' (optional, defaults to 'post') */
	    var $comment_status; /* open or closed for commenting (optional) */
	}
	// initialize post object
	$wm_mypost = new wm_mypost();
	
	// fill object
	$wm_mypost->post_title = $photorace_name;
	$photoracePage = new prPage();
	$photoracePage->setInitHtml($photorace_headcontent);
	$wm_mypost->post_content = $photoracePage->getInitHtml();
	$wm_mypost->post_status = 'publish';
	$wm_mypost->post_author = 1;
	
	// Optional; uncomment as needed
	$wm_mypost->post_type = 'page';
	// $wm_mypost->comment_status = 'closed';
	
	// feed object to wp_insert_post
	$photoracePostID = wp_insert_post($wm_mypost);
	$pr_new_query = "INSERT INTO ".$wpdb->prefix."photoracer_admin".
				" (postid, active_from, active_to, indexpath, name, headcontent, upload_untill, maxphotoxuser) values ".
				"($photoracePostID, '$photorace_init_day', '$photorace_end_day', '$photorace_index_path', 
				'$photorace_name', '$photorace_headcontent', '$photorace_end_upload', $photorace_maxphotoxuser)";
	//echo "<br>pr_new_query=$pr_new_query<br>";
	$wpdb->query($pr_new_query);
	
	//ora aggiorno la pagina con il link per partecipare:
	$wm_mypost->post_content .= "<br><p><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/play.php?prid=$photoracePostID\">Partecipa al concorso</a></p>";
	
	$wm_mypost->ID=$photoracePostID;
	wp_update_post($wm_mypost);
	
}

echo '<div class="wrap">
	<h2>PhotoRacer Admin</h2>
	<h3>Lista dei Concorsi fotografici</h3>'.
 	listRaces();
?>

</div>
<script type="text/javascript" src="<?php echo get_option('siteurl')."/wp-content/plugins/photoracer/"; ?>calendar.js"></script>
<script type="text/javascript" src="<?php echo get_option('siteurl')."/wp-content/plugins/photoracer/"; ?>calendar-it.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo get_option('siteurl')."/wp-content/plugins/photoracer/"; ?>skins/aqua/theme.css" title="Aqua" />
<script type="text/javascript">
var oldLink = null;
function setActiveStyleSheet(link, title) {
  var i, a, main;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
      a.disabled = true;
      if(a.getAttribute("title") == title) a.disabled = false;
    }
  }
  if (oldLink) oldLink.style.fontWeight = 'normal';
  oldLink = link;
  link.style.fontWeight = 'bold';
  return false;
}

// This function gets called when the end-user clicks on some date.
function selected(cal, date) {
  cal.sel.value = date; // just update the date in the input field.
  if (cal.dateClicked && (cal.sel.id == "sel1" || cal.sel.id == "sel3"))
    cal.callCloseHandler();
}

function closeHandler(cal) {
  cal.hide();                        // hide the calendar
//  cal.destroy();
  _dynarch_popupCalendar = null;
}

function showCalendar(id, format, showsTime, showsOtherMonths) {
  var el = document.getElementById(id);
  if (_dynarch_popupCalendar != null) {
    // we already have some calendar created
    _dynarch_popupCalendar.hide();                 // so we hide it first.
  } else {
    // first-time call, create the calendar.
    var cal = new Calendar(1, null, selected, closeHandler);
    // uncomment the following line to hide the week numbers
    // cal.weekNumbers = false;
    if (typeof showsTime == "string") {
      cal.showsTime = true;
      cal.time24 = (showsTime == "24");
    }
    if (showsOtherMonths) {
      cal.showsOtherMonths = true;
    }
    _dynarch_popupCalendar = cal;                  // remember it in the global var
    cal.setRange(1900, 2070);        // min/max year allowed.
    cal.create();
  }
  _dynarch_popupCalendar.setDateFormat(format);    // set the specified date format
  _dynarch_popupCalendar.parseDate(el.value);      // try to parse the text in field
  _dynarch_popupCalendar.sel = el;                 // inform it what input field we use

  _dynarch_popupCalendar.showAtElement(el.nextSibling, "Br");        // show the calendar

  return false;
}

var MINUTE = 60 * 1000;
var HOUR = 60 * MINUTE;
var DAY = 24 * HOUR;
var WEEK = 7 * DAY;

function isDisabled(date) {
  var today = new Date();
  return (Math.abs(date.getTime() - today.getTime()) / DAY) > 10;
}

function flatSelected(cal, date) {
  var el = document.getElementById("preview");
  el.innerHTML = date;
}

function showFlatCalendar() {
  var parent = document.getElementById("display");

  var cal = new Calendar(0, null, flatSelected);

  cal.weekNumbers = false;

  cal.setDisabledHandler(isDisabled);
  cal.setDateFormat("%A, %B %e");

  cal.create(parent);

  cal.show();
}
</script>

 <div class="wrap">
	  <h2>Aggiungi un concorso</h2>
	  <form name="form1" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

		  <fieldset class="options">
			  <legend>Aggiungi un nuovo concorso Fotografico</legend>
			  <table width="100%" cellspacing="2" cellpadding="5" class="editform">
			  <tr valign="top">
				  <th width="33%" scope="row">Data di inizio:</th>
				  <td>
				  	<input type="text" name="photorace_init_day" id="photorace_init_day" size="30"><input type="reset" value=" ... " onclick="return showCalendar('photorace_init_day', '%Y-%m-%d');">
				  	<br />Iserisci qui la data in cui vuoi che inizi il concorso
				  </td>
				</tr>
			  	<tr valign="top">
				  <th with="33" scope="row">Data di fine:</th>
				  <td>
				  	<input type="text" name="photorace_end_day" id="photorace_end_day" size="30"><input type="reset" value=" ... " onclick="return showCalendar('photorace_end_day', '%Y-%m-%d');">
				  	<br />Inserisci la data di fine concorso
				  </td>
			  	</tr>
			  	<tr valign="top">
				  <th with="33" scope="row">Data fine upload</th>
				  <td>
				  	<input type="text" name="photorace_end_upload" id="photorace_end_upload" size="30"><input type="reset" value=" ... " onclick="return showCalendar('photorace_end_upload', '%Y-%m-%d');">
				  	<br />Inserisci la data ultima consentita per fare upload foto
				  </td>
			  	</tr>
				<tr valign="top">
				  <th with="33" scope="row">Foto per utente:</th>
				  <td>
				  	<input name="photorace_maxphotoxuser" type="text" width="10" value="">
				  	<br />Inserisci Il numero massimo foto che un utente pu&ograve; uploadare<br/>Se non metti niente verr&agrave; settato 1
				  </td>
			  	</tr>
			  	<tr valign="top">
				  <th with="33" scope="row">Nome del concorso:</th>
				  <td>
				  	<input name="photorace_name" type="text" width="100" value="">
				  	<br />Inserisci Il titolo che dai a questo concorso. (questo campo "normalizzato" sarà anche la path delle foto per il concorso).
				  </td>
			  	</tr>
			  	<tr valign="top">
				  <th with="33" scope="row">mini-presentazione:</th>
				  <td>
				  	<textarea rows="6" cols="40" name="photorace_headcontent">Inserisci il contenuto iniziale della pagina come introduzione del concorso .
In questo campo textarea devi inserire i tag html per poter formattare, quello che viene scritto qui nono è interpretato.
				  	No tag html, head, body, </textarea>
				  	<br />
				  </td>
			  	</tr>
			  </table>
		  </fieldset>
		  <p class="submit">
		    <input type="submit" name="photorace_new" value="Aggiungi" />
		  </p>
	  </form>
  
  </div>
