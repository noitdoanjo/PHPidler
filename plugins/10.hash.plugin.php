<?php
/*
	Commands:
		.hash <algorithm> <string>
*/

function hashToChannel(&$irc,$msg,$channel,$matches,$who) 
{
    if ($matches[1] == 'help') {
       	$irc->sayToChannel($who . ': Usage: .hash <algorithm> <string>', $channel);
       	$irc->sayToChannel('Valid algos: ' . implode(' ', hash_algos()), $channel);
    }else{
      	$irc->sayToChannel($who . ': ' . hash($matches[1], $matches[2]), $channel);
    }
}

$algos = hash_algos();
$algos = array_map('preg_quote', $algos);
$this->handlers['*']['hashToChannel'] = '/^\.hash (?:(help)|(' . implode('|', $algos) . ') (.*))/';
