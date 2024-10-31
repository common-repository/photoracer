<?php
class prUtils {
	var $N_charToRemove;
	var $N_charToReplace;
	var $N_charReplacement;

	function prUtils() {
		$this->N_charToRemove = array('[', ']', '"', '(', ')', '?', '!', '\'');
		$this->N_charToReplace =  array(  ' ',':','\.', ',','&','%','£','=','à','è','é','ì','ò','ù','À','Á','Â','Ä','Å','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','à','á','â','ã','ä','å','è','é','ê','ë','ì','í','î','ï','ò','ó','ô','õ','ö','ù','ú','û','ü');
		$this->N_charReplacement =  array('_', '',  '',  '','_','_','_','_','a','e','e','i','o','u','a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','a','a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u');
		$this->avoidDirName = array('lib', 'calendar.js', 'calendar-it.js', 'photoracer.php', 'photoracer-admin.php', 'skins');

	}

	 function strNormalizeName ($name) {
		$result=$name;
		// first we remove special chars
		foreach ($this->N_charToRemove as $key => $value)
		{
			$result = mb_ereg_replace( preg_quote($this->N_charToRemove[$key]), ''  ,$result);
		}
		//than we replace avoid chars for a filename.
		foreach ($this->N_charToReplace as $key => $value)
		{
			$result = mb_ereg_replace( $this->N_charToReplace[$key], $this->N_charReplacement[$key]  ,$result);
		}
		return $result;
	}
	
	 function isRightDir ($name) {
		foreach ($this->avoidDirName as $k => $v)
		{
			if (preg_match("/^".$this->avoidDirName[$k]."$/", $name))
				return 0;
		}
		return 1;
	}
}
?>
