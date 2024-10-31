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

$ok = 0;
if (is_user_logged_in()){
	$ok = 1;
	get_currentuserinfo();	
}
else 
{
	auth_redirect();
};


$photorace_change=null;
$photorace_end_day=null;
$rid=null;
if (isset($_REQUEST['photorace_change'])) $photorace_change = $_REQUEST['photorace_change'];
if (isset($_REQUEST['photorace_end_day'])) $photorace_end_day = $_REQUEST['photorace_end_day'];
if (isset($_REQUEST['rid'])) $rid = $_REQUEST['rid']; 

if (
	preg_match("/^Cambia$/", $photorace_change) &&
	preg_match("/^\d{4}-\d{2}-\d{2}$/", $photorace_end_day) && 
	$rid != null
)
{
	$qchange = "update ".$wpdb->prefix."photoracer_admin set active_to='".$photorace_end_day."' WHERE raceid=$rid";
	$wpdb->query($qchange);
}
?>
<div class="wrap">
<h2>PhotoRacer Admin</h2>

<?php
$prq="SELECT raceid, postid, active_from, active_to, indexpath, name, numphoto FROM ".$wpdb->prefix."photoracer_admin WHERE raceid=$rid";
//echo $pr_qlist."<br>\n";
$out = $wpdb->get_row($prq);
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
	  <h2>Cambia la data di fine del concorso fotografico</h2>
	  concorso:<br />
	  
	  	<pre><?php print_r($out);?></pre>
	  
	  <form name="form1" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		
		  <fieldset class="options">
			  <legend>Nuova data di fine del concorso Fotografico</legend>
			  <table width="100%" cellspacing="2" cellpadding="5" class="editform">
			  <tr valign="top">
				  <th width="33%" scope="row">Data di fine concorso:</th>
				  <td>
				  	<input type="text" name="photorace_end_day" id="photorace_end_day" size="30"><input type="reset" value=" ... " onclick="return showCalendar('photorace_end_day', '%Y-%m-%d');">
				  	<br />Iserisci qui la data in cui vuoi che finisca il concorso
				  </td>
				</tr>
			  </table>
		  </fieldset>
		  <p class="submit">
		  <input type="hidden" name="rid" value="<?php echo "$rid"; ?>" />
		    <input type="submit" name="photorace_change" value="Cambia" />
		  </p>
	  </form>
  	<p><a href="<?php echo get_option('siteurl'); ?>/wp-admin/options-general.php?page=photoracer.php">Torna all'interfaccia di amministrazione</a></p>
  </div>
