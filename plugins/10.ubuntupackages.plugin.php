<?php
/*
	Commands:
		.ubuntu <package>
*/

class ubuntupackages_plugin{

	public function __construct(&$irc){	
		$irc->addHandler($this, 'ubuntuSearchPackage', '/^\.ubuntu (.*)/');
	}
	
	public function ubuntuSearchPackage(&$irc,$msg,$channel,$matches,$who) 
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
}