<?php
/*
	Requires userlevels.plugin.php
	
	Commands:
		.j    <#channel>
		.join <#channel>
		.p    <#channel>
		.part <#channel>
*/

$this->handlers['*']['joinChannel'] = '/^\.(j|join) #([A-Za-z0-9\._#+-]*)/';
$this->handlers['*']['partChannel'] = '/^\.(p|part) #([A-Za-z0-9\._#+-]*)( (.+))?/';

function joinChannel(&$irc,$msg,$channel,$matches,$who)
{
	if ($irc->user_levels->getLevel($who) >= USER_LEVEL_ADMIN) {
		$irc->sendCommand("JOIN #{$matches[2]}\n\r");
	}
}


function partChannel(&$irc,$msg,$channel,$matches,$who)
{
	if ($irc->user_levels->getLevel($who) >= USER_LEVEL_ADMIN) {
		$irc->sendCommand('PART #' . $matches[2] . (isset($matches[3]) ? ' :' . $matches[3] : '') . "\n\r");
	}
}
