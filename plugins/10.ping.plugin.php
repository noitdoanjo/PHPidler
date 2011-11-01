<?php
/*
	Commands:
		.ping <host>
*/

/**
 * Pings a host
 *
 * @param string hostname
 * @return array
 */
function ping($hostname){
	exec('ping -c 3 -w 3 -n -q ' . escapeshellarg($hostname), $list);
	return(array($list[2], $list[3], $list[4]));
}

/**
 * Checks if a ipv4 address is private
 *
 * @return boolean True if the ip is private, false otherwise
 * @param string $ip
 */
function ip_is_private($ip){
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
function ip_is_loopback($ip){
	$ip = sprintf('%u', ip2long($ip));
	return ( ($ip >= sprintf('%u', ip2long('127.0.0.0'))) and ($ip <= sprintf('%u', ip2long('127.255.255.255'))) );
}


function checkStatus(&$irc,$msg,$channel,$matches,$who)
{
	$ip = gethostbyname($matches[1]);
	
	if ( (!preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip)) and ($ip == $matches[1]) ) {
		// It isn't a ip address and doesn't resolve as a domain name
		$irc->sayToChannel('Unknown host ' . $ip, $channel);
	}else if (! (ip_is_private($ip) or ip_is_loopback($ip)) ) {
		$ping = ping($ip);
		foreach ($ping as $thisline) {
			$irc->sayToChannel($thisline, $channel);
		}
	}else{
		if($irc->debug) echo $ip . " is a local address!\n";
	}
}

$this->handlers['*']['checkStatus'] = '/^\.ping (\S+)/';
