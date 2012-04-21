<?php
class webgrep{
	protected $web;
	
	public function __construct(&$irc){
		$irc->addActionHandler($this, 'grep', '/^' . preg_quote($this->web['command']) . ' (.+)/');
	}
	
	public function grep(&$irc,$msg,$channel,$matches,$who)
	{
		$page = file_get_contents($this->web['url'] .  $matches[1]);
		preg_match_all($this->web['regex'], $page, $subpatterns, PREG_SET_ORDER);
		if ($irc->debug) print_r($subpatterns);
		
		if (isset($this->web['maxMatchs']) and ($this->web['maxMatchs'] <= sizeof($subpatterns))) {
			$max = $this->web['maxMatchs'];
		}else{
			$max = sizeof($subpatterns);
		}
		
		for ($i=0; $i<$max; $i++){
			$say = '';
			unset($subpatterns[$i][0]);
			foreach ($subpatterns[$i] as $match) {
				$say.= $match . ' ';
			}
			
			if (isset($this->web['stripTags']) and $this->web['stripTags']) {
				$say = strip_tags($say);
			}
			
			if (isset($this->web['stripNewLines']) and $this->web['stripNewLines']) {
				$say = str_replace("\n", '', $say);
			}
			
			if (isset($this->web['htmlDecode']) and $this->web['htmlDecode']) {
				$say = html_entity_decode($say, ENT_QUOTES);
			}
			
			if (isset($this->web['stripMultipleBlanks']) and $this->web['stripMultipleBlanks']) {
				$say = preg_replace('@ +@', ' ', $say);
			}
			
			$irc->sayToChannel($say, $channel);
		}
	}
}