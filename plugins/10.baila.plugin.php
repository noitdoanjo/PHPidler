<?php
/*
	Commands:
		.baila
*/
class baila_plugin{

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'bailar', '/^\.baila/');	
	}
	
	public function pluginHelp(){
		return array('bailar', ' : Hace bailar al bot', true);
	}

	public function bailar(&$irc,$msg,$channel,$matches,$who)
	{
		$irc->actionInChannel('Baila! :D\-<', $channel);
		$irc->actionInChannel('Baila! :D/-<', $channel);
		$irc->actionInChannel('Baila! :D|-<', $channel);
		$irc->actionInChannel('Baila! :D<-<', $channel);
		$irc->actionInChannel('Baila! :D>-<', $channel);
	}
}
