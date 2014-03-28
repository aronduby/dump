<?php

/*
 *	Static Usage Class
 *	just encapsulates D\ump() object with static calls
*/

namespace D;

class D {

	const VERSION = ".1";
	/*
	 *	Bitwise Flags for changing dump behavior
	 *	D::KILL		will call die() after output
	 *	D::OB		will use the output buffer and return the output instead of printing it
	 *	D::EXPAND	starts with everything expanded
	*/
	const KILL = 1;
	const OB = 2;
	const EXPAND = 4;


	/*
	 *	STATIC USAGE
	*/
	private static $instance;
	
	public static function __callStatic($name, $args){
		if(!self::$instance)
			self::$instance = new ump();

		return call_user_func_array([self::$instance, $name], $args);		
	}

	public static function S($flags=0, $title=false){
		if(!self::$instance)
			self::$instance = new ump();

		return self::$instance->dumpSettings($flags, $title);
	}

	public static function getInstance(){
		return self::$instance;
	}



}

?>