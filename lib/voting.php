<?php
class voting {

	var $db;

	 function voting() {
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
	
	 function createCookieVote () {
		static $randCalled = FALSE;
	    if (!$randCalled)
	    {
	        srand((double) microtime() * 1000000);
	        $randCalled = TRUE;
	    }
	    $rNum = rand(1, 100000);
	    
	    $ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
    	if (strpos($ip, '::') === 0) {
        	$ip = substr($ip, strrpos($ip, ':')+1);
    	}
    	$host = ip2long($ip);
	    $prcookie = $host.date('YmdHis').sprintf("%05d", $rNum);
	    return $prcookie;
	}
	
}
?>
