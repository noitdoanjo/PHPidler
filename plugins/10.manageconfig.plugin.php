<?php
/*
	Commands:
		.config get <property>
		.config set <property> <value>
		.config list
		.config load <file>
		.config reload
*/

class manageconfig_plugin{

	public function __construct(&$irc)
	{
		$irc->addActionHandler($this, 'get', '/^\.config get (.+)/');
		$irc->addActionHandler($this, 'set', '/^\.config set (\S+) (.+)/');
		$irc->addActionHandler($this, 'showList', '/^\.config( list)?/');
		$irc->addActionHandler($this, 'load', '/^\.config load (.+)/');
		$irc->addActionHandler($this, 'reload', '/^\.config reload/');
	}
	
	
	private function get(&$irc,$msg,$channel,$matches,$who)
	{
	}
	
	public function set(&$irc,$msg,$channel,$matches,$who) 
	{
	}
	
	private function showList(&$irc,$msg,$channel,$matches,$who)
	{
	}
	
	private function reload(&$irc,$msg,$channel,$matches,$who)
	{
	}
	
	private function load(&$irc,$msg,$channel,$matches,$who)
	{
	}
}