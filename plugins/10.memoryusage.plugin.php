<?php
/*
	Commands:
		.memusage [real]
*/

class memoryusage_plugin{

	public function __construct(&$irc){	
		$irc->addActionHandler($this, 'memoryUsageToChannel', '/^\.memusage( real)?/');
	}
	
	private function byte_convert($bytes)
	{
		$symbol = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
		$exp = 0;
		$converted_value = 0;
		if( $bytes > 0 )
		{
			$exp = floor( log($bytes)/log(1024) );
			$converted_value = ( $bytes/pow(1024,floor($exp)) );
		}
		return sprintf( '%.2f '.$symbol[$exp], $converted_value );
	}
	
	public function memoryUsageToChannel(&$irc,$msg,$channel,$matches,$who)
	{
		if ($irc->userLevels->getLevel($who) >= USER_LEVEL_ADMIN) {
			if($matches[1]=='real')
			{
				$pid = getmypid();
				exec("ps -eo%mem,rss,pid | grep $pid", $output);
				$output = explode("  ", $output[0]);
				$mem =  $output[1] * 1024;
			}else{
				$mem = memory_get_usage(); 
			}
			$memory = $this->byte_convert($mem);
			
			//say it to the world!	
			$irc->sayToChannel('I\'m using '.$memory.' of RAM to run currently', $channel);
		}
	}
}