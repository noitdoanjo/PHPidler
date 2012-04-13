<?php
/*
	Commands:
		.paste [private|public|cancel|send|lang <py|md|php|rb|(...)>]
*/
class paste_plugin{

	private $currentPastes = array();
	private $handlerId = null;
	
	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'handlePaste', '/^\.paste(?: (\S+))(?: (\S+))?/s');	
	}

	public function handlePaste(&$irc,$msg,$channel,$matches,$who) 
	{
		if(!isset($this->currentPastes[$who])){
			$this->currentPastes[$who] = array('text' => '',
							   'public' => false,
							   'lang' => '');
			
			$irc->sayToChannel($who . ': you can write your paste now or use .paste [send|private|public|cancel|lang <py|md|php|rb|(...)>]', $channel);
		}
		
		if(isset($matches[1])){
			switch ($matches[1]) {
				case 'send':
					$gist = new gist;
					$gist->setFileName('PHPIdler' . (isset($this->currentPastes[$who]['lang']) ? '.' . $this->currentPastes[$who]['lang'] : ''));
					$gist->setPublic($this->currentPastes[$who]['public']);
					$gist->paste($this->currentPastes[$who]['text']);
					$irc->sayToChannel($who . ': ' . ($gist->getResult('html_url') ? $gist->getResult('html_url') : 'couldn\'t create a new gist'), $channel);
					unset($this->currentPastes[$who]);
					break;
				
				case 'private':
					$this->currentPastes[$who]['public'] = false;
					$irc->sayToChannel($who . ': your paste will be private', $channel);
					break;
				
				case 'public':
					$this->currentPastes[$who]['public'] = true;
					$irc->sayToChannel($who . ': your paste will be public', $channel);
					break;
				
				case 'cancel':
					unset($this->currentPastes[$who]);
					$irc->sayToChannel($who . ': paste cancelled', $channel);
					break;
				
				case 'lang':
					if (isset($matches[2])) {
						$this->currentPastes[$who]['lang'] = $matches[2];
						$irc->sayToChannel($who . ': your paste will be highlighted as ' . $matches[2], $channel);
						break;
					}else{
						$irc->sayToChannel($who . ': try .paste lang rb/php/py/etc... ' . $matches[2], $channel);
					}
					break;
				
				default:
					$irc->sayToChannel($who . ': usage: .paste [send|private|public|cancel|lang <py|md|php|rb|(...)>]', $channel);
					break;
			}
		}
		
		if ((sizeof($this->currentPastes) === 0) and ($this->handlerId != null)) {
			$irc->removeActionHandler($this->handlerId);
			$this->handlerId = null;
		}elseif ((sizeof($this->currentPastes) !== 0) and ($this->handlerId === null)) {
			$this->handlerId = $irc->addActionHandler($this, 'addText', '/.*/');
		}
	}
	
	public function addText(&$irc,$msg,$channel,$matches,$who){
		if ((isset($this->currentPastes[$who])) and (strpos($msg, '.paste') !== 0)) {
			$this->currentPastes[$who]['text'] = $this->currentPastes[$who]['text'] . $msg 	. "\n";
		}
	}
}