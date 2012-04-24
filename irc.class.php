<?php
class IRC{
	public $serverHost = "irc.freenode.net";
	public $serverChannels = "#randomchannel";
	public $serverPort = 6667;
	public $serverSsl = false;
	public $nick = 'bot';
	public $master = 'admin';
	public $nickservPassword = '';
	public $pluginDir = './plugins/';
	public $debug = false;
	public $usingPlugins = false;
	public $reconnect = true;
	public $pluginConfig = array();
	private $plugins = array();
	private $actionHandlers = array();
	private $timeHandlers = array();
	private $handlersId = array();
	private $server;
	
	public function __construct($data)
	{
		//load the configuration
		foreach($data as $key => $value)
		{
			if(isset($this->$key))
			{
				$this->$key = $value;
			}
		}
		
		if (!is_array($this->serverChannels)) {
			$this->serverChannels = array($this->serverChannels);
		}
		
		ini_set('user_agent', 'PHPIdler ircbot https://github.com/seth--/PHPidler');
	}
	
	/*
	 * Add a channel to the channel array
	 *
	 * @param mixed $chan A string or array of strings with the name(s) of the joined channel(s) 
	 */
	private function addChannels($chan){
		array_push($this->serverChannels, $chan);
	}
	
	/*
	 * Remove a channel from the channel array
	 *
	 * @param mixed $chan A string or array of strings with the name(s) of the parted channel(s) 
	 */
	private function removeChannels($chan){
		if (!is_array($chan)) {
			foreach ($this->serverChannels as $key => $this_channel){
				if($this_channel == $chan){
					unset($this->serverChannels[$key]);
				}
			}
		}else{
			foreach ($this->serverChannels as $key => $this_channel){
				foreach ($chan as $this_chan){
					if($this_channel == $this_chan){
						unset($this->serverChannels[$key]);
					}
				}
			}
		}
	}
	
	
	
	/*
	 * Sends raw text to the server
	 *
	 * @param string $cmd the text to send
	 */
	public function sendRaw($text){
		fwrite($this->server['SOCKET'], $text, strlen($text));
	}
	
	/*
	 * Sends raw text to the server followed by \n
	 *
	 * @param string $cmd the command to send
	 */
	public function sendCommand($cmd){
		$this->sendRaw($cmd . "\r\n");
	}
	
	//kick someone
	public function kick($channel, $who, $reason = null){
		$channel = str_replace(' ', '', $channel);
		if($reason === null){
			$this->sendCommand('KICK ' . $channel . ' ' . $who);
		}else{
			$this->sendCommand('KICK ' . $channel . ' ' . $who . ' ' . $reason);
		}
	}
	
	//Send messages to users/channels
	public function sayToChannel($msg, $channel, $allowSpecial = false)
	{
		if($allowSpecial == false){
			$msg = str_replace(chr(1), '', $msg);
		}
		if (strpos($msg, "\n")  !== false) {
			$msg = explode("\n", $msg);
			foreach ($msg as $thisMsg) {
				$this->sayToChannel($thisMsg, $channel, true);
				usleep(1000000);
			}
		}else{
			if(strlen($msg)>400)
			{
				$len = 399;	
				$char = substr($msg, $len ,1);
				while($char != ' ')
				{
					$len--;
					$char = substr($msg, $len ,1);
				}
				$msg2 = substr($msg, $len+1);
				$msg  = substr($msg, 0, $len);
			}
			
			$this->sendCommand('PRIVMSG '.$channel.' :'.$msg);
			if (isset($msg2)) {
				return $this->sayToChannel($msg2,$channel, true);
			}
		}
	}
	
	

	public function loadPlugins()
	{
		if(file_exists($this->pluginDir))
		{
			//Batch loading!
			$dir = scandir($this->pluginDir);
			foreach($dir as $file)
			{
				if(is_file($this->pluginDir . '/' . $file) and preg_match('/\.(.*?)\.plugin\.php$/',$file, $pluginName))
				{
					//A plugin. Let's load it!
					$thisfile = $this->pluginDir . '/' . basename($file);
					$syntaxcheck = shell_exec('php -l '.escapeshellarg($thisfile));
					if(strpos($syntaxcheck,'No syntax errors detected')!==false)
					{
						//It's OK, will not disturb us 
						include($thisfile);
						echo 'OK Loading:    '.$file."\n";
						
						//Instantiate the plugin's class and add it to the array
						$pluginName = $pluginName[1];
						$className = $pluginName . '_plugin';
						if (class_exists($className)) {
							$this->plugins[$pluginName] = new $className($this);
						}
					}else{
						//Fuckin' coder! You wanted to kill me!
						echo 'Error Loading: '.$file.' (syntax error)'."\n";
					}
				}
			}
			//Enable plugins now
			$this->usingPlugins = true;
		}else{
			echo 'Not using Plugin System, the bot will just connect.'."\n";
			$this->usingPlugins = false;
		}
	}
	
	/*
	 * @return array A numeric array with the names of the loaded plugins
	 */
	public function getLoadedPlugins(){
		return array_keys($this->plugins);
	}
	
	/*
	 * @param $name The name of the plugin
	 * @return object An instance of the class {$name}_plugin or false if it doesn't exists
	 */
	public function getPlugin($name){
		return isset($this->plugins[$name]) ? $this->plugins[$name] : false;

	}

	public function connect(){
		set_time_limit(0);
		
		while($this->reconnect){
			$this->server = array(); //we will use an array to store all the server data.
			//Open the socket connection to the IRC server
			echo "Connecting to {$this->serverHost}:{$this->serverPort}\n";
			$this->server['SOCKET'] = fsockopen(($this->serverSsl ? 'ssl://' : '') . $this->serverHost, $this->serverPort, $errno, $errstr, 2);
			
			if($this->server['SOCKET'])
			{
				socket_set_blocking($this->server['SOCKET'], false);
				//Ok, we have connected to the server, now we have to send the login commands.
				$this->sendCommand('PASS NOPASS'); //Sends the password not needed for most servers
				$this->sendCommand('NICK ' . $this->nick); //sends the nickname
				$this->sendCommand('USER ' . $this->nick . ' USING PHP BOT'); //sends the user must have 4 paramters
				while(!@feof($this->server['SOCKET'])) //while we are connected to the server
				{
					//If we are using plugins, run the time handlers
					if ($this->usingPlugins == true ) {
						$this->runTimeHandlers();
					}
					
					//get a line of data from server
					$this->server['READ_BUFFER'] = fgets($this->server['SOCKET'], 1024); 
					if(empty($this->server['READ_BUFFER'])){
						if($this->debug) echo "Received no data\n";
						sleep(1); //To avoid 100% cpu use
						continue;
					}					
					//display the recived data from the server
					if($this->debug) echo "[RECIVE] ".$this->server['READ_BUFFER']; 
					
					//Get the command number (RFC 1459, chapter 6)
					preg_match('@^(?:\:.*? )?(.*?) @', $this->server['READ_BUFFER'], $matches);
					$this->server['command'] = $matches[1];
					
					switch ($this->server['command']){
						
						case '376':
						//376: End of motd
							//Identify with nickserv
							if($this->nickservPassword){
								$this->sayToChannel('identify ' . $this->nickservPassword, 'nickserv');
							}
							
							//Join the channels
							foreach ($this->serverChannels as $chan){
								$this->sendCommand('JOIN ' . $chan); 
							}
							break;
						
						case '477':
						//477: You need a registered nick to join that channel.
						//If a channel has +r and we try to join before nickserv accepts our password, try again
							preg_match('@ (#.*?) :Cannot @', $this->server['READ_BUFFER'], $matches);
							$this->sendCommand('JOIN ' . $matches[1]); 
							break;
						
						case 'PING':
						//Reply with pong
							$this->sendRaw('PONG :' . substr($this->server['READ_BUFFER'], 6)); 
							//As you can see i dont have it reply with just "PONG"
							//It sends PONG and the data recived after the "PING" text on that recived line
							//Reason being is some irc servers have a "No Spoof" feature that sends a key after the PING
							//command that must be replied with PONG and the same key sent.
							break;
						
						case 'JOIN':
						//Handle own joins
							if (preg_match('@^:'.preg_quote($this->nick, '@').'!.+ JOIN (.+)$@', $this->server['READ_BUFFER'], $matches))
							{
								//This is a join. Add the channel to the list
								$this->addChannels($matches[1]);
								if ($this->debug) {
									echo 'Joining '.$matches[1]."\n";
								}
								
							}
							break;
						
						case 'PART':
						//Handle own parts
							if (preg_match('@^:'.preg_quote($this->nick, '@').'!.+ PART (.+)$@', $this->server['READ_BUFFER'], $matches))
							{
								//This is a part. Remove the channel from the list
								$this->removeChannels($matches[1]);
								if ($this->debug) {
									echo 'Parting '.$matches[1]."\n";
								}
							}
							break;
					}
					
					
					//If we are using plugins and somebody say something, we want to run the action handlers
					if (($this->usingPlugins == true) and ($this->server['command'] == 'PRIVMSG'))
					{
						//Someone said something!
						$msg = explode('PRIVMSG ',$this->server['READ_BUFFER'],2);
						preg_match('/:(.*)!/',$msg[0],$matches);
						$who = $matches[1];
						list($channel,$msg) = explode(' :',$msg[1],2);
						
						//debug?
						if($this->debug){
							echo '['.$who.' on '.$channel.']: '.$msg;
						}
						//remove the \n in the end
						$msg = substr($msg, 0, strlen($msg)-2);
						
						$this->runActionHandlers($msg,$channel,$who);
					}
					
					if(strrpos($this->server['READ_BUFFER'],'Closing Link')!==false)
					{
						@fclose($this->server['SOCKET']);
						unset($this->server['SOCKET']);
						//Just continue running the bot but no more loop!
						break;
					}
					//This flushes the output buffer forcing the text in the while loop
					// to be displayed "On demand"
					flush(); 
				}
				echo 'Disconnected from server';	
			}else{
				echo 'Could not connect to server. Error #'.$errno.' ('.$errstr.')';
				sleep(1);
			}
		}
	}
	
	
	
	/*
	 * @return mixed The last key in $array
	 */
	private function lastArrayKey($array){
		end($array);
		return key($array);
	}
	
	/*
	 * Adds a hook that runs when something is said
	 * 
	 * @return integer The id of the hook, needed to delete it
	 */
	public function addActionHandler(&$object, $function, $regex){
		if ($this->debug) echo 'Adding handler ' . $regex . ' for ' . $function . "()\n";
		$this->actionHandlers[] = array('object' => $object,
			          		'function'  => $function,
						'regex' => $regex);
		
		return $this->lastArrayKey($this->actionHandlers);
	}
	
	/*
	 * Adds a hook that runs periodically
	 * 
	 * @return integer The id of the hook, needed to delete it
	 */
	public function addTimeHandler(&$object, $function, $seconds){
		if ($this->debug) echo 'Adding handler that runs each' . $seconds . ' seconds for ' . $function . "()\n";
		$this->timeHandlers[] = array('object' => $object,
					      'function' => $function,
					      'seconds' => $seconds,
					      'lastRun'	=> time());
		
		return $this->lastArrayKey($this->timeHandlers);
	}
	
	public function removeActionHandler($id){
		unset($this->actionHandlers[$id]);
	}
	
	public function removeTimeHandler($id){
		unset($this->timeHandlers[$id]);
	}
	
	private function runActionHandlers($msg, $channel, $who)
	{
		if($this->debug) echo "Running action handlers\n";
		
		if($channel == $this->nick)
		{
			$channel = $who;
		}
		
		//Run the handlers
		foreach($this->actionHandlers as $handler)
		{
			if (preg_match($handler['regex'], $msg, $matches))
			{
				//Call it!
				echo 'Running '.get_class($handler['object']).' -> '.$handler['function']."\n";
				$handler['object']->$handler['function']($this,$msg,$channel,$matches,$who);
			}
		}
	}
	
	private function runTimeHandlers()
	{
		if($this->debug) echo "Running time handlers\n";
		
		//Run the handlers
		foreach($this->timeHandlers as $key => $handler)
		{
			if (time() >= ($handler['lastRun'] + $handler['seconds']))
			{
				$this->timeHandlers[$key]['lastRun'] = time();
				
				echo 'Running '.get_class($handler['object']).' -> '.$handler['function']."\n";
				$handler['object']->$handler['function']($this);
			}
		}
	}
}

