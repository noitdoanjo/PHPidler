<?php
/*
	Commands:
		.md5 <hash>
*/

class hashsearch_plugin{
	
	public function __construct(&$irc){
		$irc->addActionHandler($this, 'searchMd5', '/^\.md5 ([0-9a-fA-F]{32})/');
	}
	
	/**
	 * Searches for the plaintext of a given md5 hash
	 */
	public function searchMd5(&$irc,$msg,$channel,$matches,$who) 
	{		
		$matches[1] = strtolower($matches[1]);
		if ($html = file_get_contents('http://www.tobtu.com/md5.php?h=' . $matches[1])) {
			if (preg_match('@' . $matches[1] . '\:([^\:]+)[\:|\n](.*)$@m', $html, $password)){
				if(strpos($password[1], '&lt;notfound&gt;') === false){
					$irc->sayToChannel($who . ': ' . $matches[1] . ' -> ' . html_entity_decode($password[2]), $channel);
				}else{
					$irc->sayToChannel($who . ': ' . $matches[1] . ' -> not found', $channel);
					if($irc->debug) echo $html; 
				}
			}else{
				$irc->sayToChannel($who . ': rate limit exceeded at tobtu.com', $channel);
				if($irc->debug) echo $html; 
			}
		}else{
			if($irc->debug) echo "Problems contacting tobtu.com\n"; 
		}
	}
}