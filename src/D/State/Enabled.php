<?php

namespace D\State;

class Enabled{

	public function backtrace(\D\DumpSettings $settings = null){
		$this->setTitle('Debug Backtrace <code class="via">debug_backtrace()</code>', $settings);
		return $this->dump(debug_backtrace(), $settings);
	}

	public function classes(\D\DumpSettings $settings = null){
		$this->setTitle('Currently Declared Classes <codeclass="via">get_declared_classes()</code>', $settings);
		return $this->dump(get_declared_classes(), $settings);
	}

	public function interfaces(\D\DumpSettings $settings = null){
		$this->setTitle('Currently Declared Interfaces <code class="via">get_declared_interfaces()</code>', $settings);
		return $this->dump(get_declared_interfaces(), $settings);
	}

	public function includes(\D\DumpSettings $settings = null){
		$this->setTitle('Currently <code>include</code>d or <code>require</code>d files <code class="via">get_included_files()</code>', $settings);
		return $this->dump(get_included_files(), $settings);
	}

	public function functions(\D\DumpSettings $settings = null){
		$this->setTitle('Currently Declared Functions <code class="via">get_defined_functions()</code>', $settings);
		return $this->dump(get_defined_functions(), $settings);
	}

	public function defines(\D\DumpSettings $settings = null){
		$this->setTitle('Currently Declared Constants (define) <code class="via">get_defined_constants()()</code>', $settings);
		return $this->dump(get_defined_constants(), $settings);
	}

	public function extensions(\D\DumpSettings $settings = null){
		$this->setTitle('Currently Loaded Extensions <code class="via">get_loaded_extensions()</code>', $settings);
		return $this->dump(get_loaded_extensions(), $settings);
	}

	public function headers(\D\DumpSettings $settings = null){
		$this->setTitle('All HTTP Headers for the Current Request <code class="via">getAllHeaders()</code>', $settings);
		return $this->dump(getAllHeaders(), $settings);
	}

	public function phpini(\D\DumpSettings $settings = null){
		$this->setTitle('All Registered Configuration Options for PHP <code class="via">ini_get_all()</code>', $settings);
		return $this->dump(ini_get_all(), $settings);
	}

	public function path(\D\DumpSettings $settings = null){
		$this->setTitle('Specified directories under your ini\'s </code>include_path</code> <code class="via">ini_get(\'include_path\')</code>', $settings);
		return $this->dump(explode(PATH_SEPARATOR, ini_get('include_path')), $settings);
	}

	public function request(\D\DumpSettings $settings = null){
		$this->setTitle('Values from <code>$_REQUEST</code>', $settings);
		return $this->dump($_REQUEST, $settings);
	}

	public function get(\D\DumpSettings $settings = null){
		$this->setTitle('Values from <code>$_GET</code>', $settings);
		return $this->dump($_GET, $settings);
	}

	public function post(\D\DumpSettings $settings = null){
		$this->setTitle('Values from <code>$_POST</code>', $settings);
		return $this->dump($_POST, $settings);
	}

	public function server(\D\DumpSettings $settings = null){
		$this->setTitle('Values from <code>$_SERVER</code>', $settings);
		return $this->dump($_SERVER, $settings);
	}

	public function cookie(\D\DumpSettings $settings = null){
		$this->setTitle('Values from <code>$_COOKIE</code>', $settings);
		return $this->dump($_COOKIE, $settings);
	}

	public function env(\D\DumpSettings $settings = null){
		$this->setTitle('Values from <code>$_ENV</code>', $settings);
		return $this->dump($_ENV, $settings);
	}

	public function session(\D\DumpSettings $settings = null){
		$this->setTitle('Values from <code>$_SESSION</code>', $settings);
		return $this->dump($_SESSION, $settings);
	}

	public function ini($ini_file, \D\DumpSettings $settings = null){
		$this->setTitle('Values from <code>'.$ini_file.' <code class="via">parse_ini_file()</code>', $settings);
		return $this->dump(parse_ini_file($ini_file, true), $settings);
	}


	// syntatic sugar for dump
	public function ump(){
		call_user_func_array([$this, 'dump'], func_get_args());
	}
	public function dump(){
		print '<pre>';
		var_dump(func_get_args());
		print '</pre>';
	}

	// helper function to easily set the title, if its not already set
	private function setTitle($title, \D\DumpSettings &$settings = null){
		if($settings == null)
			$settings = new \D\DumpSettings();
		if($settings->title === false)
			$settings->title = $title;
	}

}
?>