<?php

ini_set('user_agent','IRC Bot for #getdeb');

#Get files correctly
function file_get_contents_utf8($fn,$cookie=null)
{
	if($cookie)
	{
		$opts = array('http' => array('header'=> 'Cookie: '.$cookie."\r\n"));
		$context = stream_context_create($opts);
		$content = @file_get_contents($fn,false,$context);
	}else{
		$content = @file_get_contents($fn);
	}
	if($content==false) return false;
	return mb_convert_encoding($content, 'UTF-8',mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

function array_flatten(&$a,$pref='') {
   $ret=array();
   foreach ($a as $i => $j)
       if (is_array($j))
           $ret=array_merge($ret,array_flatten($j,$pref.$i));
       else
           $ret[$pref.$i] = $j;
   return $ret;
}

function searchPackage(&$irc,$msg,$channel,$matches,$who)
{
	$irc->sayToChannel('Wait a second '.$who.', I\'ll check in GetDeb',$channel);
	//What to search
	$what = trim($matches[1]);
	$answered = false;
	$result = array();
	$broke = false;
	$arr = array('9' => 'Hardy 32','10' => 'Hardy 64','11' => 'Intrepid 32','12' => 'Intrepid 64', '13' => 'Jaunty 32','14' => 'Jaunty 64');
	foreach($arr as $distro => $text)
	{
		$html = file_get_contents_utf8('http://www.getdeb.net/search.php?keywords='.urlencode($what),'app_types=1,2,3,4; distro_id='.$distro);
		if($html==false)
		{
			$irc->sayToChannel($who.', I\'m sorry but GetDeb is offline atm. See ".status" for all the server\'s statuses',$channel);
			return false;
		}
		preg_match_all('#<TD width=540 BGCOLOR="\#EBECE2">.*<A HREF="/app/.*">(.*)</A>&nbsp;(.*)</B>.*<TD BGCOLOR="\#F6F6F6"><FONT FACE="Verdana,Arial" SIZE=2>(.*)</FONT></TD></TR>#Us',$html,$matches,PREG_SET_ORDER);
		foreach($matches as $match)
		{
			$result[$match[1]][$match[2]][] = $text;
			$answered = true;
			if(count($result)==5)
			{
				$broke = true;
				break;
			}
		}
	}	
	if($answered)
	{ 
		foreach($result as $app => $data)
		{
			$objTmp = $data;
			$objTmp = array_flatten($objTmp);
			//array_walk_recursive($data, create_function('&$v, $k, &$t', '$t[] = $v;'), $objTmp);
			//var_dump($objTmp);
			$irc->sayToChannel($app.' ('.implode(', ',array_keys($data)).') on '.implode(', ',array_unique(array_values($objTmp))),$channel);
			usleep(500000);
		}
		if($broke) $irc->sayToChannel($who.', there are still more results, but I won\'t show more than 5. Try refining your search terms',$channel);
	}else $irc->sayToChannel($who.', I couldn\'t find anything with those words :(',$channel);
}

$this->handlers['*']['searchPackage'] = '/^\.search (.*)/';
$this->handlers['*']['searchPackage'] = '/^\.getdeb (.*)/';



function ubuntuSearchPackage(&$irc,$msg,$channel,$matches,$who) 
{
	$irc->sayToChannel('Wait a second '.$who.', I\'ll check in packages.ubuntu.com',$channel);
	//What to search
	$what = trim($matches[1]);
	$answered = false;
	
	//Get page parts
	$page = file_get_contents('http://packages.ubuntu.com/search?keywords='.urlencode($what));
	if($page==false)
	{
		$irc->sayToChannel($who.', I\'m sorry but packages.ubuntu.com is offline atm. See ".status" for all the server\'s statuses',$channel);
		return false;
	}elseif(strpos($page,'no results')!==false){
		$irc->sayToChannel($who.', I couldn\'t find anything with those words :(',$channel);
	}
	$parts = explode('Package ',$page);
	unset($parts[0],$parts[1],$parts[2]);

	$i = 0;
	foreach($parts as $part)
	{
		if($i == 5)
		{
			$irc->sayToChannel($who.', there are still more results, but I won\'t show more than 5. Try refining your search terms',$channel);
			return true;
		}
		
		$name = current(explode('</h3>',$part));
		$n = preg_match_all('#<li class="(.*)"><a class="resultlink".*<br>(.*)(?: \[|:)#Usm',$part,$matches);
		$irc->sayToChannel($name.' ('.implode(', ',array_unique($matches[2])).') on '.implode(', ',$matches[1]),$channel);
		usleep(500000);
		$i++;	
	}
}

$this->handlers['*']['ubuntuSearchPackage'] = '/^\.ubuntu (.*)/';
