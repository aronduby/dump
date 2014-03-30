<?php

namespace D;

class ump {
	
	protected $config = [
		/*
		 *	enabled: boolean
		 *	should dump be enabled
		*/
		'enabled' => true,
		/*
		 *	skin: string
		 *	which skin to use, this will be disappearing soon(ish)
		*/
		'skin' => "stylish",
		/*
		 *	css_file: string
		 *	path to a custom css file to use instead of the default
		*/
		'css_file' => null,
		/*
		 *	display.separator: string
		 *	the string to use as a seperator between the key/values
		*/
		'display.separator' => " => ",
		/*
		 *	display.truncate_length: integer
		 *	Strings longer than X characters will be truncate to prevent word wrap
		 *	Truncated items will display a drop-down with the full string
		*/
		'display.truncate_length' => 80,
		/*
		 *	display.cascade: array | null
		 *	Array of integers to determine when a level should collapse. If the specified level has
		 *	greater than X amount of elements it shows collapsed. The example below expands first
		 *	level if there is 5 or less items and the second level with 10 or less
		 *		'display.cascade'=>[5, 10]
		 *	set to null to have everything collapse by default
		*/
		'display.cascade' => null,
		/*
		 *	display.show_version: boolean
		 *	should we include the D version information in the output
		*/
		'display.show_version' => true,
		/*
		 *	display.show_call_info: boolean
		 *	should we include the file/line # D was called from
		*/
		'display.show_call_info' => true,
		/*
		 *	display.replace_returns: boolean
		 *	should we replace returns (\n) with br's in the output
		*/
		'display.replace_returns' => false,
		/*
		 *	sorting.arrays: boolean
		 *	should we reorder associative arrays based on their keys? 
		*/
		'sorting.arrays' => true
	];
	
	// will hold the state objects which contain the actual functions
	public $state;


	public function __construct(array $config = []){
		$this->config($config);
		if($this->config['enabled'])
			$this->enable();
		else
			$this->disable();
	}


	public function config($key = null, $val = null){
		// passed an array, merge it with config
		if(is_array($key) && $val == null){
			$this->config = array_merge($this->config, $key);
			if($this->enabled())
				$this->state->config($key, $val);
		
		// key and val is set, it's a setter
		} elseif($key != null && $val != null){
			$this->config[$key] = $val;
			if($this->enabled())
				$this->state->config($key, $val);
		
		// passed just key, getter
		} elseif($key != null) {
			return $this->config[$key] !== null ? $this->config[$key] : $default;
		
		// passed nothing, give back everything
		} else {
			return $this->config;
		}
	}


	/*
	 *	State Setters
	*/
	public function enable(){
		if(!$this->enabled())
			$this->state = new State\Enabled;

		$this->state->config($this->config);
		return $this->enabled();
	}
	public function disable(){
		if(!$this->disabled())
			$this->state = new State\Disabled;
		return $this->disabled();
	}

	/*
	 *	State Getters
	*/
	public function enabled(){
		return $this->state instanceof State\Enabled;
	}
	public function disabled(){
		return $this->state instanceof State\Disabled;
	}


	/*
	 *	Use magic methods to call the state functions
	*/
	public function __call($name, $args){
		// handle settings
		if(count($args) > 0 && $args[count($args) - 1] instanceof DumpSettings){
			$settings = array_pop($args);
		} else {
			$settings = new DumpSettings();
		}

		// add the backtrace to the settings
		// but check to make sure it wasn't set already from D::ump()
		if(!$settings->backtrace){
			$settings->backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
		}
		$args[] = $settings;

		return call_user_func_array([$this->state, $name], $args);
	}
}

?>