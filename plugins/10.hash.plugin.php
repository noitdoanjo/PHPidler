<?php
/*
	Commands:
		.hash <algorithm> <string>
*/

class hash_plugin{
	
	public function __construct(&$irc){
		$algos = hash_algos();
		$algos = array_map('preg_quote', $algos);
		$irc->addActionHandler($this, 'hashToChannel', '/^\.hash (?:(help)|(' . implode('|', $algos) . ') (.*))/');
	}
	
	/**
	 * Outputs the hash of a string using the specified algorithm. Shows help if the algorithm is invalid
	 */
	public function hashToChannel(&$irc,$msg,$channel,$matches,$who) 
	{
		if ($matches[1] == 'help') {
			$irc->sayToChannel($who . ': Usage: .hash <algorithm> <string>', $channel);
			$irc->sayToChannel('Valid algos: ' . implode(' ', hash_algos()), $channel);
		}else{
			$irc->sayToChannel($who . ': ' . hash($matches[1], $matches[2]), $channel);
		}
	}
}
