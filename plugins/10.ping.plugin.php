<?php
/*
	Commands:
		.ping <host>
*/

class ping_plugin{

	public function __construct(&$irc){	
		$irc->addHandler($this, 'pingHost', '/^\.ping (\S+)/');
	}

	/**
	 * Pings a host
	 *
	 * @param string hostname
	 * @return array
	 */
	private function ping($hostname){
		exec('ping -c 3 -w 3 -n -q ' . escapeshellarg($hostname), $list, $returnVar);
		if($returnVar === 0){
			return(array($list[2], $list[3], $list[4]));
		}else{
			return false;
		}
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
	
		
	/**
	 * Pings a host and shows the latency in a channel
	 */
	public function pingHost(&$irc,$msg,$channel,$matches,$who)
	{
		$ip = gethostbyname($matches[1]);
		
		if ( (!preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip)) and (($ip == $matches[1]) or ($ip === false)) ) {
			// It isn't a ip address and doesn't resolve as a domain name
			$irc->sayToChannel('Unknown host ' . $matches[1], $channel);
		}else if (! ($this->ipIsPrivate($ip) or $this->ipIsLoopback($ip)) ) {
			$ping = $this->ping($ip);
			if($ping){
				$ping[0] = $who . ': ' . $ping[0];
				foreach ($ping as $thisline) {
					$irc->sayToChannel($thisline, $channel);
				}
			}
		}else{
			if($irc->debug) echo $ip . " is a local address!\n";
		}
	}
}