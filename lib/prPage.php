<?php
class prPage {
	
	var $css;
	var  $initHtml;
	var $db;
		
	function __construct()
 	{
 		$this->init();
 	}

	 function init() 
 	{
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
		$this->db = $wpdb;
	}

	 function prPage() {
 		return $this->__construct();
	}
	
	
	 function setInitHtml ($content)
	{
		$this->initHtml = $content;
			
	}
	
	 function getInitHtml ()
	{
		return $this->initHtml;
	}
	
	 function browse($postid, $p=0)
	{
		
		$qnum = "select count(*) as quante from ".
			$this->db->prefix."photoracer pr join ".
			$this->db->prefix."photoracer_admin pra on (pr.raceid = pra.raceid) where pra.postid=$postid";
		$onum = $this->db->get_row($qnum);
		
		$npages = $this->calcpages($onum->quante, 10);
		$scrolling = $this->getscroll($postid, $onum->quante, $p, $npages);
		
		//$result="<table><tr><td>preview</td><td>userid</td><td>userlogin</td><td>imgid</td><td>path</td><td>imgname</td><td>insert time</td></tr>";
		$result='<table border=1>
			<tr>
				<td>imgid</td>
				<td>imgname</td>
				<td>preview</td>
				<td>userlogin (userid)</td>
				<td>insert time</td>
				<td>Visibile (cambia visibilit&agrave;)</td>
			</tr>';
		$q0 = "select raceid from ".$this->db->prefix."photoracer_admin where postid=$postid";
		$r0 = $this->db->get_row($q0);
		$q1 = "SELECT pr.wpuid as userid, wpu.user_login as userlogin, pr.imgid as imgid, ".
		"pr.imgpath as imgpath, pr.imgname as imgname, pr.tinsert as tinsert, pr.visibile as visibile ".
		"FROM ".$this->db->prefix."photoracer as pr join ".$this->db->prefix."users as wpu on (pr.wpuid = wpu.ID) ".
		" and pr.raceid=".$r0->raceid." order by pr.wpuid, pr.tinsert limit ".
		($p * 10).",".(($p + 1) * 10);
		//echo $q1."<br />";
		$out = $this->db->get_results($q1);
		
		foreach ($out as $k=>$v)
		{
			$userid = $v->userid;
			$userlogin = $v->userlogin;
			$imgid = $v->imgid;
			$imgpath = $v->imgpath;
			$imgname = $v->imgname;
			$tinsert = $v->tinsert;
			$visibile = $v->visibile;
			$med_thumb = preg_replace("/$imgname/", "med_".$imgname, $imgpath);

			$result .=
			"<tr>\n".
				"<td>$imgid</td>".
				"<td>$imgname</td>".
				"<td><a href=\"".get_option('siteurl')."$imgpath\">".
					"<img src=\"".get_option('siteurl').$med_thumb."\"></a></td>".
				"<td><a href=\"".get_option('siteurl')."/wp-admin/user-edit.php?user_id=$userid&wp_http_referer=%2Fwp251%2Fwp-admin%2Fusers.php\">".
					"$userlogin ($userid)</a></td>".
				/*"<td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/browsedir.php?d=$imgpath\">".
					"$imgpath</a></td>".*/
				"<td>$tinsert</td>".
				"<td><b>".($visibile==1?"SI ":"<font color=\"red\">NO </font>") .
				"</b> (<a href=\"".
					get_option('siteurl')."/wp-content/plugins/photoracer/togglevisibility.php?postid=$postid&id=$imgid&v=".
					($visibile==1?"0":"1")."\">".($visibile==1?"no":"si").
					"</a>)</td>\n".
			"</tr>\n";
		}
		$result .= "</table>";
		
		return "<center>$scrolling</center><br />$result.<br /><center>$scrolling</center>";
	}
	
	 function refreshpage($postid)
	{
		
//		$q11="SELECT count(vote) as howmanyvote FROM ".$this->db->prefix."photoracer_votes where vote > 0 ";
//		$r11 = $this->db->get_row($q11);
//		$hm = $r11->howmanyvote;
		$q1 = "SELECT raceid, active_from, active_to, indexpath, name, headcontent, numphoto FROM ".
			$this->db->prefix."photoracer_admin WHERE postid='$postid'";
		//echo "$q1<br>";
		$out = $this->db->get_row($q1);
		//print_r($out1);
		$raceid = $out->raceid;
		$headcontent = $out->headcontent;
		
		$q2 = "select wpuid, imgid, imgpath, imgname, imgcomment, sumvotes, imgcountview, tinsert from wp_photoracer where visibile=1 and raceid=$raceid order by tinsert desc limit 5";
		//echo "$q2<br />";
		$out2 = $this->db->get_results($q2);
		//print_r($out2);
		$lastposts='';
		foreach ($out2 as $k2=>$v2)
		{
			$imgpath = $v2->imgpath;
			//echo "imgpath=$imgpath<br>";
			$imgname = $v2->imgname;
			//echo "imgname=$imgname<br>";
			$imgid = $v2->imgid;
			$med_thumb = preg_replace("/$imgname/", "med_".$imgname, $imgpath);

			$wpu = get_userdata($v2->wpuid);
			//print_r($wpu);
			
			$lastposts .=
			"<tr>\n".
				"<td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/viewimg.php?id=$v2->imgid\"><img src=\"".get_option('siteurl').$med_thumb."\"></a></td>".
				"<td valign=left>$v2->tinsert<br>\n".
					"<b>utente</b>: $wpu->user_login<br>\n".
					($v2->imgcomment ? "<b>commento</b>: $v2->imgcomment <br>\n":"").
					/*"<li>somma voti: ". $v2->sumvotes .
					"</li><li>visite: $v2->imgcountview </li></ul></td>".*/
			"</tr>\n";
		}
		$table_lastpost="<h3>Ultimi pubblicati</h3>".
						"<table border=0>$lastposts</table><p>".
						"<a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/crono.php?pid=$postid\">guarda in ordine cronologico</a>\n";
		
		$numphoto = $this->db->get_row("select numphoto from wp_photoracer_admin where postid=$postid");
		$newcontent=
			$this->fixed_menu(get_option('siteurl'),$postid).
			"<br><i>Sono presenti ".$numphoto->numphoto." foto in gara</i><br>".
			"<br>$headcontent<br>".
			$table_lastpost;
			
		$pd = get_post($postid);
		//print_r($pd);	
		$pd->post_content = $newcontent; 
		wp_update_post($pd);
		return $newcontent;
		
	}
	
	function fixed_menu($siteurl, $postid)
	{
		return 
		"| <a href=\"$siteurl/wp-content/plugins/photoracer/play.php?prid=$postid\">pubblica foto</a> ".
		"| <a href=\"$siteurl/wp-content/plugins/photoracer/crono.php?prid=$postid\">ultime pubblicate</a> ".
		"| <a href=\"$siteurl/wp-content/plugins/photoracer/mostviewed.php?prid=$postid\">le pi&ugrave; viste</a> ".
		"| <a href=\"$siteurl/wp-content/plugins/photoracer/mostvoted.php?prid=$postid\">le pi&ugrave; votate</a> ";
	}
	
	function mostvoted($title, $raceid, $postid, $p=0, $npages, $numphoto, $n_photo_x_page)
	{
//		$q11="SELECT count(vote) as howmanyvote FROM ".$this->db->prefix."photoracer_votes where vote > 0";
//		$r11 = $this->db->get_row($q11);
//		$hm = $r11->howmanyvote;
		//echo "$raceid, $postid, $p, $npages, $numphoto, $n_photo_x_page<br />";
		$q2="SELECT v.imgid as iid, pr.imgpath as ipath, pr.imgname as iname, ".
		"tinsert, imgcomment, imgcountview, sumvotes, wpuid ".
		"from ".$this->db->prefix."photoracer_votes as v join ".$this->db->prefix."photoracer as pr on (v.imgid = pr.imgid) ".
		"where pr.raceid=$raceid  and pr.visibile = 1 group by iid order by sumvotes desc limit ".
		($p * $n_photo_x_page).",".(($p + 1) * $n_photo_x_page);
		//echo "$q2<br />";
		$out2 = $this->db->get_results($q2);
		$contenttable='';
		$cols=0;
		foreach ($out2 as $k2=>$v2)
		{
			//print "<pre>";
			//print_r($v2);
			//print "</pre>";
			$imgpath = $v2->ipath;
			//echo "imgpath=$imgpath<br>";
			$imgname = $v2->iname;
			//echo "imgname=$imgname<br>";
			$imgid = $v2->iid;
			$med_thumb = preg_replace("/$imgname/", "med_".$imgname, $imgpath);

			$wpu = get_userdata($v2->wpuid);
		if ($cols == 0)
			{
				$contenttable .=
				"<tr>\n".
					"<td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/viewimg.php?id=$imgid\">".
						"<img src=\"".get_option('siteurl').$med_thumb."\"></a>".
					"<ul class=\"prdidascalia\"><li>foto numero $imgid </li><li>pubblicata il ".$this->getHumanWhen($v2->tinsert)."</li>\n".
					"<li>autore: $wpu->user_login</li>\n".
					($v2->imgcomment ? "<li>titolo: $v2->imgcomment </li>\n":"").
					/*"<li>media voti: ". sprintf("%2.2f", 
											($v2->sumvotes / ($hm ? $hm : 1))).
											"</li><li>visite: $v2->imgcountview </li></ul></td>";*/
					"<li>somma voti: ". $v2->sumvotes .
					"</li><li>visite: $v2->imgcountview </li></ul></td>";
					
			}
			else
			{
				$contenttable .=
					"<td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/viewimg.php?id=$imgid\">".
						"<img src=\"".get_option('siteurl').$med_thumb."\"></a>".
					"<ul class=\"prdidascalia\"><li>foto numero $imgid </li><li>pubblicata il ".$this->getHumanWhen($v2->tinsert)."</li>\n".
					"<li>autore: $wpu->user_login</li>\n".
					($v2->imgcomment ? "<li>titolo: $v2->imgcomment </li>\n":"").
					/*"<li>media voti: ". sprintf("%2.2f", 
											($v2->sumvotes / ($hm ? $hm : 1))).
											"</li><li>visite: $v2->imgcountview </li></ul></td>";*/
					"<li>somma voti: ". $v2->sumvotes .
					"</li><li>visite: $v2->imgcountview </li></ul></td>".
				"</tr>\n";
				
			}
			$cols = ($cols + 1) % 2; 
		}
		if ($cols == 0) $contenttable .= '</tr>';
		$scrolling = $this->getscroll($postid, $numphoto, $p, $npages);
		$mostvotedpage=
		"<div class=\"titolo_pag\"><h1> $title </h1></div>".
		"<div class=\"pagina\">".
		"<div class=\"pagina-int\">".
			"<div id=\"content\" class=\"narrowcolumn\">".
			"<h3>Foto pi&ugrave; votate</h3>".
			"<p>".$this->fixed_menu(get_option('siteurl'),$postid)."</p>".
			"<center>$scrolling</center><br>".
			"<table border=0>$contenttable</table><br>".
			"<center>$scrolling</center><br>".
			"</div>".
		"</div>".
		"</div>";
	
		return $mostvotedpage;
	}
	
	function mostview($title, $raceid, $postid, $p=0, $npages, $numphoto, $n_photo_x_page)
	{
//		$q11="SELECT count(vote) as howmanyvote FROM ".$this->db->prefix."photoracer_votes where vote > 0";
//		$r11 = $this->db->get_row($q11);
//		$hm = $r11->howmanyvote; 
		//echo "$raceid, $postid, $p, $npages, $numphoto, $n_photo_x_page<br />";
		$q2="SELECT v.imgid as iid, pr.imgpath as ipath, pr.imgname as iname, ".
		"tinsert, imgcomment, imgcountview, sumvotes, wpuid ".
		"from ".$this->db->prefix."photoracer_votes as v join ".$this->db->prefix."photoracer as pr on (v.imgid = pr.imgid) ".
		"where pr.raceid=$raceid and pr.visibile = 1 group by iid order by imgcountview desc limit ".
		($p * $n_photo_x_page).",".(($p + 1) * $n_photo_x_page);
		//echo "$q2<br />";
		$out2 = $this->db->get_results($q2);
		$contenttable='';
		$cols=0;
		foreach ($out2 as $k2=>$v2)
		{
			//print "<pre>";
			//print_r($v2);
			//print "</pre>";
			$imgpath = $v2->ipath;
			//echo "imgpath=$imgpath<br>";
			$imgname = $v2->iname;
			//echo "imgname=$imgname<br>";
			$imgid = $v2->iid;
			$med_thumb = preg_replace("/$imgname/", "med_".$imgname, $imgpath);

			$wpu = get_userdata($v2->wpuid);
			if ($cols == 0)
			{
				$contenttable .=
				"<tr>\n".
					"<td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/viewimg.php?id=$imgid\">".
						"<img src=\"".get_option('siteurl').$med_thumb."\"></a>".
					"<ul class=\"prdidascalia\"><li>foto numero $imgid </li><li>pubblicata il ".$this->getHumanWhen($v2->tinsert)."</li>\n".
					"<li>autore: $wpu->user_login</li>\n".
					($v2->imgcomment ? "<li>titolo: $v2->imgcomment </li>\n":"").
					/*"<li>media voti: ". sprintf("%2.2f", 
											($v2->sumvotes / ($hm ? $hm : 1))).
											"</li><li>visite: $v2->imgcountview </li></ul></td>";*/
					"<li>somma voti: ". $v2->sumvotes .
					"</li><li>visite: $v2->imgcountview </li></ul></td>";
					
			}
			else
			{
				$contenttable .=
					"<td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/viewimg.php?id=$imgid\">".
						"<img src=\"".get_option('siteurl').$med_thumb."\"></a>".
					"<ul class=\"prdidascalia\"><li>foto numero $imgid </li><li>pubblicata il ".$this->getHumanWhen($v2->tinsert)."</li>\n".
					"<li>autore: $wpu->user_login</li>\n".
					($v2->imgcomment ? "<li>titolo: $v2->imgcomment </li>\n":"").
					/*"<li>media voti: ". sprintf("%2.2f", 
											($v2->sumvotes / ($hm ? $hm : 1))).
											"</li><li>visite: $v2->imgcountview </li></ul></td>";*/
					"<li>somma voti: ". $v2->sumvotes .
					"</li><li>visite: $v2->imgcountview </li></ul></td>".
				"</tr>\n";
				
			}
			$cols = ($cols + 1) % 2; 
		}
		if ($cols == 0) $contenttable .= '</tr>';
		$scrolling = $this->getscroll($postid, $numphoto, $p, $npages);
		$mostviewpage=
		"<div class=\"titolo_pag\"><h1> $title </h1></div>".
		"<div class=\"pagina\">".
		"<div class=\"pagina-int\">".
			"<div id=\"content\" class=\"narrowcolumn\">".
			"<h3>Foto pi&ugrave; viste</h3>".
			"<p>".$this->fixed_menu(get_option('siteurl'),$postid)."</p>".
			"<center>$scrolling</center><br>".
			"<table class=\"prtable\" border=0>$contenttable</table><br>".
			"<center>$scrolling</center><br>".
			"</div>".
		"</div>".
		"</div>";
	
		return $mostviewpage;
	}
	
	function cronoview($title, $raceid, $postid, $p=0, $npages, $numphoto, $n_photo_x_page)
	{
//		$q11="SELECT count(vote) as howmanyvote FROM ".$this->db->prefix."photoracer_votes where vote > 0";
//		$r11 = $this->db->get_row($q11);
//		$hm = $r11->howmanyvote; 
		$q2 = "select wpuid, imgid, imgpath, imgname, imgcomment, sumvotes, imgcountview, tinsert ".
			"from ".$this->db->prefix."photoracer where raceid=$raceid and visibile = 1". 
			" order by tinsert desc limit ".
			($p * $n_photo_x_page).",".(($p + 1) * $n_photo_x_page); 
			
			//"select wpuid, imgid, imgpath, imgname, imgcomment, imgrank, imgcountview, tinsert ".
			//"from ".$this->db->prefix."photoracer where raceid=$raceid and  and pr.visibile = 1 imgid < ".
			//(($npages - $p)* 10).
			//" order by tinsert desc limit $n_photo_x_page";
		//echo $q2."<br />";	
		$out2 = $this->db->get_results($q2);
		$contenttable='';
		$cols=0;
		foreach ($out2 as $k2=>$v2)
		{
			
			$imgpath = $v2->imgpath;
			//echo "imgpath=$imgpath<br>";
			$imgname = $v2->imgname;
			//echo "imgname=$imgname<br>";
			$imgid = $v2->imgid;
			$med_thumb = preg_replace("/$imgname/", "med_".$imgname, $imgpath);

			$wpu = get_userdata($v2->wpuid);
		if ($cols == 0)
			{
				$contenttable .=
				"<tr>\n".
					"<td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/viewimg.php?id=$imgid\">".
						"<img src=\"".get_option('siteurl').$med_thumb."\"></a>".
					"<ul class=\"prdidascalia\"><li>foto numero $imgid </li><li>pubblicata il ".$this->getHumanWhen($v2->tinsert)."</li>\n".
					"<li>autore: $wpu->user_login</li>\n".
					($v2->imgcomment ? "<li>titolo: $v2->imgcomment </li>\n":"").
					/*"<li>media voti: ". sprintf("%2.2f", 
											($v2->sumvotes / ($hm ? $hm : 1))).
											"</li><li>visite: $v2->imgcountview </li></ul></td>";*/
					"<li>somma voti: ". $v2->sumvotes .
					"</li><li>visite: $v2->imgcountview </li></ul></td>";
					
			}
			else
			{
				$contenttable .=
					"<td><a href=\"".get_option('siteurl')."/wp-content/plugins/photoracer/viewimg.php?id=$imgid\">".
						"<img src=\"".get_option('siteurl').$med_thumb."\"></a>".
					"<ul class=\"prdidascalia\"><li>foto numero $imgid </li><li>pubblicata il ".$this->getHumanWhen($v2->tinsert)."</li>\n".
					"<li>autore: $wpu->user_login</li>\n".
					($v2->imgcomment ? "<li>titolo: $v2->imgcomment </li>\n":"").
					/*"<li>media voti: ". sprintf("%2.2f", 
											($v2->sumvotes / ($hm ? $hm : 1))).
											"</li><li>visite: $v2->imgcountview </li></ul></td>";*/
					"<li>somma voti: ". $v2->sumvotes .
					"</li><li>visite: $v2->imgcountview </li></ul></td>".
				"</tr>\n";
				
			}
			$cols = ($cols + 1) % 2; 
		}
		if ($cols == 0) $contenttable .= '</tr>';
		$scrolling = $this->getscroll($postid, $numphoto, $p, $npages);
		$cronopage=
		"<div class=\"titolo_pag\"><h1> $title </h1></div>".
		"<div class=\"pagina\">".
		"<div class=\"pagina-int\">".
			"<div id=\"content\" class=\"narrowcolumn\">".
			"<h3>Foto in ordine cronologico</h3>".
			"<p>".$this->fixed_menu(get_option('siteurl'),$postid)."</p>".
			"<i>Sono presenti $numphoto foto</i><br>	".
			"<center>$scrolling</center><br>".
			"<table border=0>$contenttable</table><br>".
			"<center>$scrolling</center><br>".
			"</div>".
		"</div>".
		"</div>";
	
		return $cronopage;
	}
	
	function calcpages($numphoto, $n_photo_x_page)
	{
		return (($numphoto / $n_photo_x_page) < 1 ? 1 : ($numphoto / $n_photo_x_page));
	}
	
	function getscroll ($postid, $numphoto, $lastpage, $npages)
	{
		//echo "$postid, $numphoto, $lastpage, $npages<br>";
		$scroll='';
		if ($lastpage >= 1) $scroll ='<a href="?pid='.$postid.'&p='.($lastpage - 1).'">&lt;--</a>';
		for ($i = 0; $i < $npages ; $i ++)
		{
			if ($i < $lastpage) $scroll .= " <a href=\"?pid=$postid&p=".$i."\">".($i + 1)."</a> ";
			
			elseif ($i == $lastpage) $scroll .= " ".($i + 1)." ";
			
			elseif ($i > $lastpage) $scroll .= " <a href=\"?pid=$postid&p=$i\">".($i + 1)."</a>";
		}
		if ($lastpage < ($npages - 1)) $scroll .= " <a href=\"?pid=$postid&p=".($lastpage + 1)."\">--&gt;</a>";
		return $scroll;
	}
	
	function getHumanWhen($when) {
		$month = array ("gennaio", "febbraio", "marzo", "aprile", "maggio", "giugno", "luglio", "agosto", "settembre", "ottobre", "novembre", "dicembre");
		
		$ad = array();
		$ad['year'] = substr($when,0,4);
		$ad['month'] = substr($when,5,2);
		$ad['day'] = substr($when,8,2);
		$ad['hour'] = substr($when,11,2);
		$ad['minute'] = substr($when, 14, 2);
		
		// $when = '2008-08-23 16:00:32
		/*$ad = date_parse($when);*/
		return sprintf("%02d %s %04d alle %02d:%02d",
						$ad['day'], 
						$month[$ad['month'] - 1],
						$ad['year'],
						$ad['hour'],
						$ad['minute']);
			
		
	}
}
?>
