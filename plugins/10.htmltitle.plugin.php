<?php
/*
	Commands:
		http://<something>
		https://<something>
*/
//ToDo: check if the url is loopback or lan
class htmltitle_plugin{

	public function __construct(&$irc){	
		$irc->addHandler($this, 'titleToChannel', '@(https?://\S*)@');	
	}
	
	/**
	 * Checks if a ipv4 address is private
	 *
	 * @return boolean True if the ip is private, false otherwise
	 * @param string $ip
	 */
	private function ipIsPrivate($ip){
		$ip = sprintf('%u', ip2long($ip));
		return ( ($ip >= sprintf('%u', ip2long('10.0.0.0')))    and ($ip <= sprintf('%u', ip2long('10.255.255.255'))) ) or
			   ( ($ip >= sprintf('%u', ip2long('172.16.0.0')))  and ($ip <= sprintf('%u', ip2long('172.31.255.255'))) ) or
			   ( ($ip >= sprintf('%u', ip2long('192.168.0.0'))) and ($ip <= sprintf('%u', ip2long('192.168.255.255'))) );
	}
	
	/**
	 * Checks if a ipv4 address is loopback
	 *
	 * @return boolean True if the ip is loopback, false otherwise
	 * @param string $ip
	 */
	private function ipIsLoopback($ip){
		$ip = sprintf('%u', ip2long($ip));
		return ( ($ip >= sprintf('%u', ip2long('127.0.0.0'))) and ($ip <= sprintf('%u', ip2long('127.255.255.255'))) );
	}
	
	public function titleToChannel(&$irc,$msg,$channel,$matches,$who) 
	{
		$host = parse_url($matches[1], PHP_URL_HOST);
		$ip = gethostbyname($host);
		
		if ( (!preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip)) and (($ip == $matches[1]) or ($ip === false)) ) {
			// It isn't a ip address and doesn't resolve as a domain name
			$irc->sayToChannel('Unknown host ' . $host, $channel);
		}else if (! ($this->ipIsPrivate($ip) or $this->ipIsLoopback($ip)) ) {
			if ($file = file_get_contents($matches[1])) {
				if (preg_match('@<title>([^<]{1,256}).*?</title>@', $file, $matches)) {
					if (strlen($matches[1]) == 256) {
						$matches[1].='...';
					}
					$irc->sayToChannel(html_entity_decode($matches[1]), $channel);
				}
			}
		}else{
			if($irc->debug) echo $ip . " is a local address!\n";
		}

	}
}