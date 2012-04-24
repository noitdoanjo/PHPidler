<?php
/*
	Commands:
		.help [command]
*/
class help_plugin{
	
	private $helpText = array();
	private $commandList = array();

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'showHelp', '/^\.help(?: \.?(.+))?/s');
		
		//iterate over all the plugins and try to get the get help from $plugin->pluginHelp
		foreach ($irc->getLoadedPlugins() as $plugin){
			if(method_exists($irc->getPlugin($plugin), 'pluginHelp')){
				$this->parseHelpArray($irc->getPlugin($plugin)->pluginHelp());
			}
		}
		//$this isn't in $irc->plugins yet, so if $this->pluginHelp existed it wasn't going to be called
		$this->setHelp('help', ' [command]: Shows help about [command]. When [command] isn\'nt present, show a list of available commands.', true);
	}
	
	/*
	 * Adds information from an array to $helpText
	 *
	 * @param array $array can be an array with data to be added to $this->helpText or an array of arrays
	 */
	private function parseHelpArray($array){
		assert('is_array($array)');
		assert('isset($array[0])');
		
		if(is_array($array[0])){
			foreach ($array as $subArray){
				$this->parseHelpArray($subArray);
			}
		}else{
			$this->setHelp($array[0], $array[1], (isset($array[2]) ? $array[2] : false));
		}
	}

	/*
	 * Adds information to $helpText
	 *
	 * @param string $command
	 * @param string $helpText
	 * @param boolean $showInList
	 */
	public function setHelp($command, $helpText, $showInList = false){	
		$this->helpText[$command] = $helpText;
		if($showInList){
			$this->commandList[] = '.' . $command;
		}
	}
	
	public function showHelp(&$irc,$msg,$channel,$matches,$who) 
	{
		if(isset($matches[1])){
			if(isset($this->helpText[$matches[1]])){
				$irc->sayToChannel($matches[1] . $this->helpText[$matches[1]] ,$channel);
			}else{
				$irc->sayToChannel('No help found for ' . $matches[1] ,$channel);
			}
		}else{
			$irc->sayToChannel('Available commands: ' . implode(' ',$this->commandList) ,$channel);
		}
	}
}