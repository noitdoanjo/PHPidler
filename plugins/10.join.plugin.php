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
		$irc->addHandler($this, 'joinChannel', '/^\.(j|join) #([A-Za-z0-9\._#+-]*)/');
		$irc->addHandler($this, 'partChannel', '/^\.(p|part) #([A-Za-z0-9\._#+-]*)( (.+))?/');
	}
	
	public function joinChannel(&$irc,$msg,$channel,$matches,$who)
	{
		if ($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN) {
			$irc->sendCommand("JOIN #{$matches[2]}\n\r");
		}
	}
	
	public function partChannel(&$irc,$msg,$channel,$matches,$who)
	{
		if ($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN) {
			$irc->sendCommand('PART #' . $matches[2] . (isset($matches[3]) ? ' :' . $matches[3] : '') . "\n\r");
		}
	}
}