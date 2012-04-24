<?php
/*
	Commands:
		.ddg <something>

	Api documentation: https://api.duckduckgo.com/
*/
class ddg_plugin{

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'duckDuckGo', '/^\.ddg (.+)/s');	
	}
	
	public function pluginHelp(){
		return array('ddg', ' <topic>: Searchs information about <topic> using duckduckgo zero-click info api. You can also use duckduckgo !bang sintax.', true);
	}

	public function duckDuckGo(&$irc,$msg,$channel,$matches,$who) 
	{
		$page = json_decode(file_get_contents('https://api.duckduckgo.com/?format=json&pretty=0&no_redirect=1&no_html=1&skip_disambig=1&kp=-1&q=' . urlencode($matches[1])), true);
		
		if (isset($page['AbstractText'])) {
			if (($page['AbstractText'] !== '') or (isset($page['AbstractURL']) and ($page['AbstractURL'] !== ''))) {
				$irc->sayToChannel($who . ': ' . html_entity_decode($page['AbstractText'], ENT_QUOTES, 'utf-8'), $channel);
			}elseif ( ((!isset($page['Answer'])) or ($page['Answer'] === '')) and (!isset($page['AbstractURL']) or ($page['AbstractURL'] !== '')) ) {
				$irc->sayToChannel($who . ': ' . $matches[1] . ' not found. Try https://duckduckgo.com/?q=' . urlencode($matches[1]), $channel);
			}
		}
		if (isset($page['AbstractURL']) and ($page['AbstractURL'] !== '')) {
			$irc->sayToChannel($page['AbstractURL'], $channel);
		}
		if(isset($page['Type'])){
			//Article
			if ($page['Type'] == 'A') {
				if (isset($page['Results']) and ($page['Results'] !== '')) {
					$i = 0;
					foreach ($page['Results'] as $thisResult) {
						if ($i++ >= 5) {
							$irc->sayToChannel('There is more, go to https://duckduckgo.com/?q=' . urlencode($matches[1]) . ' or redefine your search terms', $channel);
							break 1;
						}elseif ((!isset($page['AbstractURL'])) or ($page['AbstractURL'] !== $thisResult['FirstURL'])) {
							$irc->sayToChannel($thisResult['Text'] . ': ' . $thisResult['FirstURL'], $channel);
							usleep(250000);
						}
					}
				}
				
			//Category
			}elseif ($page['Type'] == 'C'){
				if (isset($page['RelatedTopics']) and ($page['RelatedTopics'] !== '')) {
					$i = 0;
					foreach ($page['RelatedTopics'] as $thisTopic) {
						if ($i++ >= 5) {
							$irc->sayToChannel('There is more, go to https://duckduckgo.com/?q=' . urlencode($matches[1]) . ' or redefine your search terms', $channel);
							break 1;
						}elseif ((!isset($page['AbstractURL'])) or ($page['AbstractURL'] !== $thisTopic['FirstURL'])) {
							$irc->sayToChannel($thisTopic['Text'] . ': ' . $thisTopic['FirstURL'], $channel);
							usleep(250000);
						}
					}
				}
				
			//Redirect
			}elseif ($page['Type'] == 'E'){
				if (isset($page['Redirect']) and ($page['Redirect'] !== '')) {
					$irc->sayToChannel($page['Redirect'], $channel);
				}
				
			}elseif (isset($page['Answer']) and ($page['Answer'] !== '')) {
				$irc->sayToChannel($who . ': ' . strip_tags($page['Answer']), $channel);
			}
		}
	}
}
