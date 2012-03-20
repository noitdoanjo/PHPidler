<?php
/*
	Commands:
		.reverse <domain>
*/
//To do: sort results from right to left (first tld, then domain, then subdomain...)
class reversedns_plugin{

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'reverse', '/^\.reverse (.+)/');
	}

	public function reverse(&$irc,$msg,$channel,$matches,$who) 
	{
        $ip = gethostbynamel($matches[1]);
        //$txt is the html to be sent to pastehtml
        $txt = '<h1>Reverse dns for ' . htmlentities($matches[1], ENT_QUOTES) . '</h1><br />';
        $totalCount = 0;
        
        //There can be multiple A records
        foreach ($ip as $this_ip) {
            $txt.= '<h2>IP: ' . htmlentities($this_ip, ENT_QUOTES) . '</h2>';
            
            //Confirmed domains are those wich resolve to the given IP. Unconfirmed are those wich don't but are in the bing results anyway.
            $confirmedDomains = array();
            $unConfirmedDomains = array();
            
            $offset = 0;
            $pasado = null;
            $newFullResult = array();
            do{
                $oldFullResult = $newFullResult;
                $newFullResult = array();
                
                //Get the data
                $url = 'http://api.search.live.net/json.aspx?AppId=7066FAEB6435DB963AE3CD4AC79CBED8B962779C&Query=IP:' . $this_ip . '&Sources=web&web.count=50&Web.Offset=' . $offset;
                $data  = json_decode(file_get_contents($url), true);
                
				if(isset($data['SearchResponse']['Web']['Results'])){
					foreach ($data['SearchResponse']['Web']['Results'] as $result) {
						//Take the hostname...
						$host = parse_url($result['Url'], PHP_URL_HOST);

						//... and see if it's in the list
						if ( (!in_array ($host, $confirmedDomains)) and (!in_array ($host, $unConfirmedDomains)) ){
							//It isn't in the list. 
							if($irc->debug) echo 'Found host: '.$host; 
							//Does it resolve to the same ip as the original domain?
							if (in_array($this_ip, is_array(gethostbynamel($host))?gethostbynamel($host):array())) {
								$confirmedDomains[] = $host;
							}else{
								$unConfirmedDomains[] = $host;
							}
						}
						$newFullResult[] = $host;
					}
					$offset = $offset + 50;
				}else{
					if($irc->debug) echo 'No results'; 
				}
            
            //Keep going ultil geting the same results as the last time
            }while ($oldFullResult != $newFullResult);
            
            $totalCount =+ sizeof($confirmedDomains) + sizeof($unConfirmedDomains);
            
            sort($confirmedDomains);
            sort($unConfirmedDomains);
            
            //Add the link
            foreach ($confirmedDomains as $key => $host) {
                $confirmedDomains[$key] = '<a href="http://' . htmlentities($host, ENT_QUOTES) . '">' . htmlentities($host, ENT_QUOTES) . '</a><br />';
            }
            foreach ($unConfirmedDomains as $key => $host) {
                $unConfirmedDomains[$key] = '<a href="http://' . htmlentities($host, ENT_QUOTES) . '">' . htmlentities($host, ENT_QUOTES) . '</a><br />';
            }
            
            $txt.= '<h3>Confirmed ('   . sizeof($confirmedDomains)   . '):</h3>' . implode('', $confirmedDomains) .
                   '<h3> Unconfirmed(' . sizeof($unConfirmedDomains) . '):</h3>' . implode('', $unConfirmedDomains) . '<br /><br />';
        }
        //Send the html
        $paste = new pastehtml ;
		$irc->sayToChannel($who . ', here is your reverse dns for ' . $matches[1] . ' (' . $totalCount . ' results)' . ': ' . $paste->paste($txt), $channel);
	}
}
