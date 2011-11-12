<?php
/*
	Commands:
		.say <#channel> <something>
		.say <somebody> <something>
*/
class say_plugin{
	public function __construct(&$irc){	
		$irc->addHandler($this, 'repeatOnChannel', '/^\.say (.*?) (.*)/s');	
	}

	public function repeatOnChannel(&$irc,$msg,$channel,$matches,$who) 
	{
		if (($irc->user_levels->getLevel($who) >= USER_LEVEL_OWNER) or
			(($irc->user_levels->getLevel($who) >= USER_LEVEL_ADMIN) and (strpos(strtolower($matches[1]), 'serv') === false))) {
				//So, the user is an admin not talking to a service or the user is a owner
				$irc->sayToChannel($matches[2],$matches[1]);
		}
	}
}
