<?php

function repeatOnChannel(&$irc,$msg,$channel,$matches,$who) 
{
	$irc->sayToChannel($matches[2],$matches[1]);
}

$this->handlers['*']['repeatOnChannel'] = '/^\.say (.*?) (.*)/s';
