<?php
/*
	Commands:
		.say <#channel> <something>
		.say <somebody> <something>
*/
class say_plugin{

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'sayToChannel', '/^\.say (.*?) (.*)/s');	
	}

	public function sayToChannel(&$irc,$msg,$channel,$matches,$who) 
	{
		if (($irc->userLevels->getLevel($who) >= USER_LEVEL_OWNER) or
			(($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN) and (strpos(strtolower($matches[1]), 'serv') === false))) {
				//So, the user is an admin not talking to a service or the user is a owner
				$irc->sayToChannel($matches[2],$matches[1]);
		}
	}
}
