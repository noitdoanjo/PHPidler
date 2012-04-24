<?php
/*
	Commands:
		.hex <text>
*/

class hex_plugin{
	
	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'hexToChannel', '/^\.hex (.*)/');
	}
	
	public function pluginHelp(){
		return array('hex', ' <text>: Transforms <text> to hexa.', true);
	}
	
	/**
	 * Ascii to hex
	 */
	public function hexToChannel(&$irc,$msg,$channel,$matches,$who) 
	{
		$irc->sayToChannel($who . ': 0x' . bin2hex($matches[1]), $channel);
	}
}
