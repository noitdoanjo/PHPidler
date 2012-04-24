<?php
/*
	Commands:
		.autokick add <regexp>
		.autokick list
		.autokick del <id>
*/
class autokick_plugin{

	private $regexps = array();
	private $handlerId = null;
	private $irc;
	
	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'add', '/^\.autokick add (.+)$/');
		$irc->addActionHandler($this, 'showList', '/^\.autokick list/');
		$irc->addActionHandler($this, 'delete', '/^\.autokick del ([0-9]+)$/');
		$this->irc = $irc;
	}
	
	public function pluginHelp(){
		return array(
			     array('autokick', ' <action>: Manages autokick lists. <action> can be add, list or del.', true),
			     array('autokick add', ' <pcre>: Adds an autokick rule for all the messages matching <pcre>.'),
			     array('autokick list', ': Shows a list of active autokick rules.'),
			     array('autokick del', ' <number>: Deletes the autokick rule <number>.'),
			     );
	}
	
	/*
	 * Checks if the user talking has to be kicked
	 */
	public function checkForKick(&$irc,$msg,$channel,$matches,$who){
		if (isset($this->regexps[$channel]) and (strpos($msg, '.autokick')!==0)) {
			foreach ($this->regexps[$channel] as $regexp) {
				if (preg_match('/' . $regexp . '/', $msg)) {
					$irc->kick($channel, $who, 'autokick');
				}
			}
		}
	}
	
	/*
	 * ReIndexes $this->regexps to have continuous keys and adds/removes the hook when needed
	 */
	private function reIndex(){
		foreach ($this->regexps as $channel => $array){
			$this->regexps[$channel] = array_values($array);
		}
		if (($this->handlerId === null) and (sizeof($this->regexps) > 0)) {
			$this->handlerId = $this->irc->addActionHandler($this, 'checkForKick', '/./');
		}elseif (($this->handlerId !== null) and (sizeof($this->regexps) == 0)) {
			$this->irc->removeActionHandler($this->handlerId);
			$this->handlerId = null;
		}
	}
	
	/*
	 * escapes / to \/
	 * @param string $regexp the regular expression to escape
	 * @return string
	 */
/*      this isn't that easy, what happens with "\\\/"?

 	private function escape($regexp){
		$regexp = str_split($regexp);
		for ($i = 0; $i<sizeof($regexp); $i++) {
			//escape a "/" and "\\/" but not "\/"
			if( ($regexp[$i] == '/') or ( ($i>0) and ($regexp[$i-1] == '\\') and  ( ($i<2) or $regexp[$i]-2 != '\\') ) ){
				$regexp[$i] = '\\' . $regexp[$i];
			}
		}
		return implode('', $regexp);
	}
*/

	public function add(&$irc,$msg,$channel,$matches,$who){
		if (($irc->userLevels->getLevel($who) >= USER_LEVEL_OWNER) or ($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN)){
			$this->regexps[$channel][] = $file = str_replace(chr(0), '', $matches[1]);
		}
		$this->reIndex();
	}
	
	public function showList(&$irc,$msg,$channel,$matches,$who){
		if(isset($this->regexps[$channel])){
			$irc->sayToChannel('Autokick list:', $channel);
			foreach ($this->regexps[$channel] as $key => $regexp){
				$irc->sayToChannel($key . ': ' . $regexp, $channel);
			}
		}else{
			$irc->sayToChannel('Autokick list is empty for this channel', $channel);
		}
	}
	
	public function delete(&$irc,$msg,$channel,$matches,$who){
		if (($irc->userLevels->getLevel($who) >= USER_LEVEL_OWNER) or ($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN)){
			unset($this->regexps[$channel][$matches[1]]);
			if(sizeof($this->regexps[$channel]) == 0){
				unset($this->regexps[$channel]);
			}
		}
		$this->reIndex();
	}
}
