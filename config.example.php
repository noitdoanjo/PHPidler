<?php
/*
 * Configure your bot here and rename this file to config.php
 */
$config = Array(
    // The server
    'serverHost' => 'irc.example.net',
    
    // The port. Usually is 6667 (plain) or 6697 (ssl)
    'serverPort' => 6667,
    
    // Encrypt the connection to the server?
    'serverSsl' => false,
    
    // Channels to join after connecting
    'serverChannels' => array('#PHPIdler', '#hello', '#AnotherChannel'),
    
    // Nickname of the bot
    'nick' => 'PHPIdler',
    
    // The owner's nick
    'master' => 'somebody',
    
    // Where are the plugins stored
    'pluginDir' => './plugins/',
    
    // Are you having problems?
    'debug' => false,
    
    // Try to reconnect if something fails
    'reconnect' => true,
    
    //Nickserv password
    'nickservPassword' => '',
    
    // Path to the sqlite database
    'pluginConfig' => array('db_path' => './db.sqlite',
                            'fortune_dir' => 'fortune')
);