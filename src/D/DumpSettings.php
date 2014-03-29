<?php

/*
 *	Dump Settings (ds)
 *	dump calls are variable length but want to have the ability to pass in settings as the last argument
 *	only way I can think to do that is to make a specific class and check the instanceof the last arg
 *	
 *	flags: bitmask 	bitmask of the class constants above
 *	title: string 	title to print at the top of the output
 *
*/

namespace D;

class DumpSettings{
	
	public $flags = 0;
	public $title = false;
	public $backtrace = false;

	public function __construct($flags  = 0, $title = false){
		$this->flags = $flags;
		$this->title = $title;
	}
}

?>