<?php
/*
	Commands:
		.whoishosting <domain>
*/
class whoishosting_plugin{

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'whoIsHosting', '/^\.whoishosting (.*)/');	
	}

	public function whoIsHosting(&$irc,$msg,$channel,$matches,$who) 
	{
		$ip = shell_exec('host ' . escapeshellarg($matches[1]));
		print_r($ip);
		//It has a valid IP address
		if(preg_match('@ has address (\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})@', $ip, $subpatterns)){
			//$subpatterns[1] has the ip address
			print_r($subpatterns);
			$whois = shell_exec('whois ' . $subpatterns[1]);
			print_r($whois);
			if(strpos($whois, 'Query rate limit exceeded') !== false){
				$irc->sayToChannel($who . ': Whois query rate limit exceeded, try again later.', $channel);
				
			}else if(preg_match('@owner\:\s+(.*?)$@mi', $whois, $owner)){
				print_r($owner);
				$irc->sayToChannel($who . ': ' . $matches[1] . ' is hosted by ' . $owner[1], $channel);
				
			}else{
				$irc->sayToChannel($who . ': I don\'t know where ' . $matches[1] . ' is being hosted', $channel);
			}
			
		//No address
		}else if(strpos($ip, 'not found: 3(NXDOMAIN)') !== false){
			//No resuelve
			$irc->sayToChannel($who . ': ' . trim($ip), $channel);
			
		//Probably $matches[1] was a ip address
		}else if(preg_match('@domain name pointer (.*)$@', $ip, $subpatterns)){
			$this->whoIsHosting($irc, $msg, $channel, $subpatterns, $who);
		}
	}
}

