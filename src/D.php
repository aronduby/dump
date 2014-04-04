<?php

/*
 *	Static Usage Class
 *	Encapsulates D\ump object with static calls
 *	Holds the constants for the bitmask flags
*/

class D {

	const VERSION = "0.1";
	/*
	 *	Bitwise Flags for changing dump behavior
	 *	D::KILL		will call die() after output
	 *	D::OB		will use the output buffer and return the output instead of printing it
	 *	D::EXPAND	starts with everything expanded
	*/
	const KILL = 1;
	const OB = 2;
	const EXPAND = 4;


	private static $instance;

	// syntatic sugar for dump
	public static function ump(){
		if(!self::$instance)
			self::$instance = new \D\ump();

		if(self::$instance->disabled())
			return true;

		// handle settings
		$args = func_get_args();
		$settings = self::getSettings($args);
		$settings->backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
		$args[] = $settings;

		return call_user_func_array([self::$instance, 'dump'], $args);
	}

	// get a settings object
	public static function S($flags=0, $title=false){
		return new D\DumpSettings($flags, $title);
	}
	
	public static function __callStatic($name, $args){
		if(!self::$instance)
			self::$instance = new D\ump();

		return call_user_func_array([self::$instance, $name], $args);
	}

	public static function getInstance(){
		return self::$instance;
	}

	private static function getSettings(&$args){
		if(count($args)>0 && $args[count($args) - 1] instanceof D\DumpSettings){
			return array_pop($args);
		} else {
			return new D\DumpSettings();
		}
	}


}

?>