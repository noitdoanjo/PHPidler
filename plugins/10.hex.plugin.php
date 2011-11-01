<?php
/*
	Commands:
		.hex string
*/

function hexToChannel(&$irc,$msg,$channel,$matches,$who) 
{
   	$irc->sayToChannel($who . ': 0x' . bin2hex($matches[1]), $channel);
}

$this->handlers['*']['hexToChannel'] = '/^\.hex (.*)/';
