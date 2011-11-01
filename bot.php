#!/usr/bin/php
<?php

//Load things
require('./config.php');
require('./irc.class.php');
if(!isset($config)) die('No configuration!'."\n");

//Create the bot!
$irc = new IRC($config);

//Load plugins
$irc->initBot();

//And send it online!
$irc->connect();

