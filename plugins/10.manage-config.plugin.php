<?php
/*
	Commands:
		.config list
		.config get <property>
		.config set <property> <value>
*/

//TODO: mostrar en "list" los parametros configurados en config,php

function manageConfig(&$irc,$msg,$channel,$matches,$who) 
{
    
    $properties = $irc->database->get(null, 'manage-config');

    //List all the properties
    if (substr($msg, 0, 12) == '.config list') {
        foreach ($properties as $property) {
			foreach ($property as $name => $value) {
			    $irc->sayToChannel($name.' => '.$value, $channel);
			}
        }
        
    //Show one property value
    }elseif (preg_match('@^\.config get (\S+)@', $msg, $matches)) {
        foreach ($properties as $property) {
			if (isset($property[$matches[1]])) {
				$irc->sayToChannel($matches[1].' => '.$property[$matches[1]], $channel);
			}
		}
		
    //Change an existing property or add a new one
    }elseif (preg_match('@^\.config set (\S+) (.+)@', $msg, $matches)) {
        foreach ($properties as $property) {
			if (isset($property[$matches[1]])) {
                $irc->database->change($matches[1], $matches[2],  null, 'manage-config');
                break 1;
            }
        }
        if (!isset($property[$matches[1]])) {
            $irc->database->insert($matches[1], $matches[2], 'manage-config');
        }
        $irc->$property=$value;
    }
}

function loadConfig(&$irc){
    $properties = $irc->database->get(null, 'manage-config');
    foreach ($properties as $property) {
		foreach ($property	as $name => $value) {
		    $irc->$name=$value;
		}
	}
}


loadConfig($this);
$this->handlers['*']['manageConfig'] = '/^\.config (set (\S+) (.+)|get (.+)|list)/';
