<?php
/*
	Commands:
		.fortune list
		.fortune <file>
		
	Needs fortune
*/
class fortune_plugin{

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'listFortunes', '/^\.fortune list/');	
		$irc->addActionHandler($this, 'showFortune', '/^\.fortune(?: (.+))?/');	
	}

	public function listFortunes(&$irc,$msg,$channel,$matches,$who)
	{
		foreach (glob($irc->pluginConfig['fortune_dir'] . '/' . '*.dat') as $filename) {
			$irc->sayToChannel(substr($filename, strlen($irc->pluginConfig['fortune_dir'] . '/'), -4), $channel);
		}
	}
	
	public function showFortune(&$irc,$msg,$channel,$matches,$who) 
	{
		if(isset($matches[1])){
			//this is to avoid executing fortune on ".fortune list". To do: make a better regex
			if ($matches[1] == 'list') {			
				if($irc->debug) echo "No, just show the list\n";
				return;
			}
			exec('fortune -a ' . escapeshellarg($irc->pluginConfig['fortune_dir'] . '/' . basename(realpath($irc->pluginConfig['fortune_dir'] . '/' . $matches[1]))), $fortune);
		}else{
			exec('fortune -a ' . escapeshellarg($irc->pluginConfig['fortune_dir'] . '/'), $fortune);
		}
		
		foreach ($fortune as $thisfortune) {
			if ($thisfortune) {
				$irc->sayToChannel($thisfortune, $channel);
			}
		}
	}
}
