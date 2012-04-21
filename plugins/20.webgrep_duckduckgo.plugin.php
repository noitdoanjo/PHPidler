<?php
/*
	Commands:
            .dg <search>
*/
class webgrep_duckduckgo_plugin extends webgrep{
    protected $web = array('command' => '.dg',
                           'url' => 'http://duckduckgo.com/html/?q=',
                           'regex' => '@<a rel="nofollow" class="large" href="(http.+?)"(>)(.*?)</a>@',
                           'maxMatchs' => 5,
                           'stripTags' => true,
                           'htmlDecode' => true);
}
