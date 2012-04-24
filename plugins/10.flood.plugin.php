<?php
/*
	Commands:
		.flood 10 who what
		.floodctcp 10 who what
*/
class flood_plugin{

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'flood', '/^\.(flood|floodctcp) (\d+) (\S+?) (.+)/');
	}
	
	public function pluginHelp(){
		return array(
			     array('flood', ' <number> <who> <what>: Says <what> to <who> <number> times.', true),
			     array('floodctcp', ' <number> <who> <what>: Says <what> to <who> <number> times via ctcp messages.', true),
			     );
	}

	public function flood(&$irc,$msg,$channel,$matches,$who)
	{
		if (($irc->userLevels->getLevel($who) >= USER_LEVEL_OWNER) or
			(($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN) and (strpos(strtolower($matches[3]), 'serv') === false))) {
				//So, the user is an admin not talking to a service or the user is a owner
                if ($matches[1] == 'floodctcp') {
                    $matches[4] = chr(1) . $matches[4] . chr(1);
                }
                for ($i = 0; $i < $matches[2]; $i++){
    				$irc->sayToChannel($matches[4],$matches[3], ($matches[1] == 'floodctcp'));
                    usleep(500000);
                }            
		}
	}
}
