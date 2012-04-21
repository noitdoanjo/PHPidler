<?php
/*
	Commands:
            .php <search>
*/
class webgrep_php_plugin extends webgrep{
    protected $web = array('command' => '.php',
                           'url' => 'http://www.php.net/',
			   'regex' => '@<div class="methodsynopsis dc-description">\s*((?:\s|\S)*?)</div>@',
			   'maxMatchs' => 5,
		           'stripTags' => true,
			   'stripNewLines' => true,
			   'htmlDecode' => true,
			   'stripMultipleBlanks' => true);
}
