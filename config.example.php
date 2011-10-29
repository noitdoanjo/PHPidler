<?php
/*
 * Configure your bot here and rename this file to config.php
 */
$config = Array(
    // The server
    'server_host' => 'irc.example.net',
    
    // The port. Usually is 6667 (plain) or 6697 (ssl)
    'server_port' => 6667,
    
    // Encrypt the connection to the server?
    'server_ssl' => false,
    
    // Channels to join after connecting
    'server_channels' => array('#PHPIdler', '#hello', '#AnotherChannel'),
    
    // Nickname of the bot
    'nick' => 'PHPIdler',
    
    // The owner's nick
    'master' => 'somebody',
    
    // Where are the plugins stored
    'plugindir' => './plugins/',
    
    // Are you having problems?
    'debug' => false,
    
    // Try to reconnect if something fails
    'reconnect' => true,
);