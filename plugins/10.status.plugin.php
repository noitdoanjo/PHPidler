<?php

function ping($hostname) {
	exec('ping -c 1 -w 1 '.$hostname,$list);
  	if (strpos($list[4],'1 received')>0) {
    	return true;
  	}
  	return false;
}

function checkStatus(&$irc,$msg,$channel,$matches,$who)
{
	if($matches[1]!='') $hostnames = array(trim($matches[1]));
	else $hostnames = array('getdeb.net', 'abs.getdeb.net', 'archive.getdeb.net', 'wiki.getdeb.net', 'playdeb.net');
	$online = array();
	$offline = array();
	foreach($hostnames as $hostname)
	{
		if(ping($hostname))
		{
			$online[] = $hostname;
		}else{
			$offline[] = $hostname;
		}
	}
	if(count($online)>0)	$irc->sayToChannel('Online: '.implode(', ',$online),$channel);
	if(count($offline)>0)	$irc->sayToChannel('Offline: '.implode(', ',$offline),$channel);
}

$this->handlers['*']['checkStatus'] = '/^\.status(?: (.+))?/';
