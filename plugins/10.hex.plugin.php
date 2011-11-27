<?php
/*
	Commands:
		.hex string
*/

class hex_plugin{
	
	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'hexToChannel', '/^\.hex (.*)/');
	}
	
	/**
	 * Ascii to hex
	 */
	public function hexToChannel(&$irc,$msg,$channel,$matches,$who) 
	{
		$irc->sayToChannel($who . ': 0x' . bin2hex($matches[1]), $channel);
	}
}
