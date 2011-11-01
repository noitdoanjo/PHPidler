<?php
/*
	Commands:
		.say <#channel> <something>
		.say <somebody> <something>
*/

function repeatOnChannel(&$irc,$msg,$channel,$matches,$who) 
{
	if (($irc->user_levels->getLevel($who) >= USER_LEVEL_OWNER) or
		(($irc->user_levels->getLevel($who) >= USER_LEVEL_ADMIN) and (strpos(strtolower($matches[1]), 'serv') === false))) {
			//So, the user is an admin not talking to a service or the user is a owner
			$irc->sayToChannel($matches[2],$matches[1]);
	}
}

$this->handlers['*']['repeatOnChannel'] = '/^\.say (.*?) (.*)/s';
