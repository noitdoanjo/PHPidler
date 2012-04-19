<?php
/*
	Requires userlevels.plugin.php
	
	Commands:
		.j    <#channel>
		.join <#channel>
		.p    <#channel>
		.part <#channel>
*/
class join_plugin{
	
	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'joinChannel', '/^\.(j|join) #(\S+)/');
		$irc->addActionHandler($this, 'partChannel', '/^\.(p|part) #(\S+)( (.+))?/');
	}
	
	public function joinChannel(&$irc,$msg,$channel,$matches,$who)
	{
		if ($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN) {
			$irc->sendCommand('JOIN #' . $matches[2]);
		}
	}
	
	public function partChannel(&$irc,$msg,$channel,$matches,$who)
	{
		if ($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN) {
			$irc->sendCommand('PART #' . $matches[2] . (isset($matches[3]) ? ' :' . $matches[3] : ''));
		}
	}
}