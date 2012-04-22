#!/usr/bin/php
<?php


//Load things
if (isset($argv[1])){
    require($argv[1]); 
    chdir(dirname(__FILE__));
}else{
    chdir(dirname(__FILE__));
    require('./config.php');
}
require('./irc.class.php');
if(!isset($config)) die('No configuration!'."\n");

//Create the bot!
$irc = new IRC($config);

//Load plugins
$irc->loadPlugins();

//And send it online!
$irc->connect();

