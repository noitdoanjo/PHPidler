<?php
/*
	Commands:
            .translate <search>
*/
class webgrep_translate_plugin extends webgrep{
    protected $web = array('command' => '.translate',
			   'url' => 'http://api.wordreference.com/0.8/ec1b1/json/enes/',
			   'regex' => '@"OriginalTerm" : { "term" : "(.*?)", "POS" : (".*?"), "sense" : ".*?", "usage" : ".*?"},\s*"FirstTranslation" : {"term" (:) "(.*?)", "POS" : ".*?", "sense" : ".*?"}@',    
			   'maxMatchs' => 1,
			   'stripTags' => true,
			   'stripNewLines' => true,
			   'htmlDecode' => true,
			   'stripMultipleBlanks' => true);
}


