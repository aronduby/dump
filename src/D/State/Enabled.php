<?php

namespace D\State;
use D\D;

class Enabled{

	private $config = [];
	public function config($key = null, $val = null, $default = null){
		// passed an array, merge it with config
		if(is_array($key) && $val == null){
			$this->config = array_merge($this->config, $key);
		
		// key and val is set, it's a setter
		} elseif($key != null && $val != null){
			$this->config[$key] = $val;
		
		// passed key & and value, getter
		} elseif($key != null && $val == null) {
			return $this->config[$key] !== null ? $this->config[$key] : $default;
		
		// passed nothing, give back everything
		} else {
			return $this->config;
		}
	}


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
		$this->setTitle('Currently Defined Functions <code class="via">get_defined_functions()</code>', $settings);
		return $this->dump(get_defined_functions(), $settings);
	}

	public function defines(\D\DumpSettings $settings = null){
		$this->setTitle('Currently Declared Constants (define) <code class="via">get_defined_constants()</code>', $settings);
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

	// helper function to easily set the title for settings, if its not already set
	private function setTitle($title, \D\DumpSettings &$settings = null){
		if($settings == null)
			$settings = new \D\DumpSettings();
		if($settings->title === false)
			$settings->title = $title;
	}


	public function dump(){
		// the last argument will always be an instance of \D\DumpSettings
		// since that is set in the callers
		$args = func_get_args();
		$settings = array_pop($args);

		// can't have both kill and returh flags set
		if(($settings->flags & (D::KILL | D::OB)) === (D::KILL | D::OB))
			throw new \InvalidArgumentException('Calling dump with both the KILL and OB flag is invalid. There can only be one.');

		// if we're buffering
		// remove the ob flag, append settings, and call dump again
		if($settings->flags & D::OB){
			$settings->flags = $settings->flags & ~D::OB;
			$args[] = $settings;

			ob_start();
			call_user_func_array([$this, 'dump'], $args);
			return ob_get_clean();
		}

		// if we're running CLI we don't want all the extra output
		if($this->isCli()){
			foreach($args as $r){
				print_r($r);
			}

			if($settings->flags & D::KILL)
				die();

			return;
		}


		$clear_object_recursion_protection = false;
		if($this->object_recursion_protection === NULL) {
			$this->object_recursion_protection = [];
			$clear_object_recursion_protection = true;
		}

		$this->expand_all = ($settings->flags & D::EXPAND);

		print "\n".'<section class="d-wrapper" data-expanded="'.($settings->flags & D::EXPAND ? 'true' : 'false').'">'."\n";
			print "\t".'<header class="d-header">'."\n";

				print "\t\t".'<p class="d-toggle-all">toggle all</p>'."\n";
				print $settings->title ? "\t\t".'<h1 class="d-title">'.$settings->title.'</h1>'."\n" : '';

				if($this->config('display.show_call_info', null, true)){
					print "\t\t".'<span class="d-call" style="white-space:nowrap;">';
					print 'Called from <code class="d-file">'.$settings->backtrace['file'].'</code>, ';
					print 'line <code class="d-line">'.$settings->backtrace['line'].'</code>';
					print '</span>'."\n";
				}
			
			print "\t".'</header>'."\n";

			foreach($args as $data){
				print "\t".'<div class="d-root">'."\n";
				print "\t".'<ul class="d-node">'."\n";

					print "\t\t".$this->render($data)."\n";

				print "\t".'</ul>'."\n";
				print "\t".'</div>'."\n";
			}


			if($this->config('display.show_version', null, true)){
				$url = 'http://github.com/aronduby/dump';

				print "\t".'<footer class="d-footer">'."\n";
					print "\t\t".'<div class="d-version" style="white-space:nowrap;">'."\n";
						print '<strong class="d-version-number">D::ump v'.D::VERSION.'</strong>'."\n";
						print ' | <a class="d-url" href="'.$url.'" target="_blank">'.$url.'</a>'."\n";
					print "\t\t".'</div>'."\n";
				print "\t".'</footer>'."\n";
			}

			// resources (css/js)
			$this->resources();

		print '</section>'."\n";

		// flee the hive
		$recursion_marker = $this->marker();
		if($hive =& $this->hive($dummy)){
			foreach($hive as $i => $bee){
				if(is_object($bee)){
					if(
						($hash = spl_object_hash($bee)) 
						&& isset($this->object_recursion_protection[$hash])
					){
						unset($this->object_recursion_protection[$hash]);
					}
				}elseif(isset($hive[$i]->$recursion_marker)) {
					unset($hive[$i][$recursion_marker]);
				}
			}
		}

		if($clear_object_recursion_protection){
			$this->object_recursion_protection = null;
		}


		if($settings->flags & D::KILL)
			die();
	}


	/*
	 *	Private Rendering Functions and helpers
	*/
	private $level = 0; // recursion level
	private $object_recursion_protection = null;
	private $expand_all = false;

	private function render(&$data, $name="&raquo;"){

		// Highlight elements that have a space in their name.
		// Spaces are hard to see in the HTML and are hard to troubleshoot
		$name = $this->sanitizeName($name);

		if (is_object($data))
			return $this->_object($data, $name);

		if (is_array($data))
			return $this->_array($data, $name);

		if (is_resource($data))
			return $this->_resource($data, $name);

		if (is_string($data))
			return $this->_string($data, $name);

		if (is_float($data))
			return $this->_float($data, $name);

		if (is_integer($data))
			return $this->_integer($data, $name);

		if (is_bool($data))
			return $this->_boolean($data, $name);

		if (is_null($data))
			return $this->_null($name);
	}

	private function _recursion() {
		$html = '<ul class="d-node">
					<li class="d-child">
						<a class="d-name">&#8734;</a>
						(<em class="d-type">Recursion</em>)
					</li>
				</ul>';

		// should that div.d-nest be closed?
		echo $html;
	}

	private function _object(&$data, $name){
		$reflection = new \ReflectionObject($data);
		$properties = $reflection->getProperties();

		$child_count = count($properties);
		$collapsed = $this->isCollapsed($this->level, $child_count);

		$classes = (count($data)>0 ? 'd-expandable'.(!$collapsed ? ' d-open' : '') : '');
		print '<li class="d-child '.$classes.'">';		
			print '<span class="d-name">'.$name.'</span> <span class="d-type">Object</span> ';
			print $this->config('display.separator');
			print '<span class="d-class">'.get_class($data).'</span>';
			print (count($child_count) == 0 ? ' (empty)' : '');

			if($properties){
				$this->_vars($data);
			}
		print '</li>';
	}

	// properties of an array or object
	private function _vars(&$data){
		$is_object = is_object($data);
		$recursion_marker = $this->marker();

		if ($is_object) {
			if(($hash = spl_object_hash($data)) && isset($this->object_recursion_protection[$hash])) {
				$r = $this->object_recursion_protection[$hash];
			} else {
				$r = NULL;
			}
		} else {
			$r = isset($data[$recursion_marker]) ? $data[$recursion_marker] : null;
		}

		// recursion detected
		if($r > 0) {
			return $this->_recursion();
		}

		// stain it
		$this->hive($data);

		// rendering
		print '<ul class="d-node">';

		// deeper and deeper, way down
		$this->level++;

		if($is_object){
			$reflection = new \ReflectionObject($data);
			$properties = $reflection->getProperties();

			foreach($properties as $property){
				$visibility = null;
	
				if($property->isPrivate()){
					$visibility = 'private';
				} elseif ($property->isProtected()) {
					$visibility = 'protected';
				} elseif ($property->isPublic()) {
					$visibility = 'public';
				}
	
				$name = $property->getName();
				if($visibility=='private' || $visibility=='protected') {
					$property->setAccessible(true);
				}
	
				$value = $property->getValue($data);
	
				$this->render($value, '<span class="d-visibility">'.$visibility.'</span>&nbsp;'.$name);
				if($visibility=='private' || $visibility=='protected') {
					$property->setAccessible(false);
				}
			}
		} else {
			// keys
			$keys = array_keys($data);
			foreach($keys as $k){
				// skip marker
				if ($k === $recursion_marker)
					continue;

				// get real value
				$v =& $data[$k];
				$this->render($v,$k);
			}
		}

		print '</ul>'."\n";
		$this->level--;
	}

	private function _array($data, $name){
		$config_sort = $this->config('sorting.arrays', null, true);

		// If the sort is enabled in the config (default = yes) and the array is assoc (non-numeric)
		if (sizeof($data) > 1 && $config_sort && $this->isAssoc($data)){
			// Copy the array to a temp variable and sort it
			$new = $data;
			ksort($new);

			// If the sorted array is the same as the old don't sort it
			if($new === $data) {
				$sort = 0;
			} else {
				$data = $new;
				$sort = 1;
			}
		} else {
			$sort = 0;
		}

		$child_count = count($data);
		$collapsed = $this->isCollapsed($this->level, count($data));


		print '<li class="d-child '.($child_count>0 ? 'd-expandable '.(!$collapsed?'d-open':'') : '').'">';

		print '<span class="d-name">'.$name.'</span> <span class="d-type">Array(<span class="d-array-length">';
		print count($data).'</span>)</span>';

		if(count($data)>0){
			print " &hellip;";
		}

		if ($sort) {
			print ' <span class="d-sorted" title="Array has been sorted prior to display. This is configurable using config sorting.arrays">Sorted</span>';
		}

		// callback
		if(is_callable($data)){
			$vals = array_values($data);
			print '<span class="d-callback"> |';
			print ' (<span class="d-type">Callback</span>) <span class="d-string">';
			if(!is_object($vals[0])){
				echo htmlSpecialChars($vals[0]);
			} else {
				echo htmlSpecialChars(get_class($vals[0])).'::';
			}

			echo htmlSpecialChars($vals[1]).'()</span></span>';
		}

		if (count($data)) {
			$this->_vars($data);
		}

		print "</li>";
	}

	private function _resource($data, $name){
		$html = '<li class="d-child">
				<span class="d-name">'.$name.'</span> <span class="d-type">Resource</span>
				'.$this->config('display.separator').'<span class="d-resource">'.get_resource_type($data).'</span>
		</li>';

		echo $html;
	}

	private function _string($data, $name){
		// extra
		$extra = false;
		$temp = $data;

		// Get the truncate length from the config, or default to 100
		$truncate_length = $this->config('display.truncate_length', null, 100);
		$replace_returns = $this->config('display.replace_returns', null, true);

		if(strlen($data) > $truncate_length) {
			$temp = substr($data, 0, $truncate_length - 1);
			$extra = true;
		}

		$temp = htmlentities($temp);

		if($replace_returns){
			$temp = nl2br($temp);
		} else {
			$temp = preg_replace("/\\n/", '<span class="d-carrage-return"> &para; </span>', $temp);
		}

		$collapsed = $this->isCollapsed($this->level, 1);
		print '<li class="d-child '.($extra ? 'd-expandable' . (!$collapsed ? ' d-open' : '') : '').'">';
			print '<span class="d-name">'.$name.'</span> ';
			print '<span class="d-type">String(<span class="d-string-length">'.strlen($data).'</span>)</span> ';
			print $this->config('display.separator').'<span class="d-string">'.$temp;

			// This has to go AFTER the htmlspecialchars
			if($extra){
				print '&hellip;';
			}
			print '</span>';

			$ut = $this->isTimestamp($name, $data);
			if($ut){
				print ' ~ <span class="d-timestamp">'.$ut.'</span>';
			}

			// callback
			if(is_callable($data)){
				print '<span class="d-callback"> | ';
				print '(<span class="d-type">Callback</span>) <span class="d-string">'.htmlSpecialChars($temp).'()</span></span>';
			}

		if($extra) {
			$data = htmlentities($data);

			if($replace_returns){
				$temp = nl2br($temp);
			} else {
				$temp = preg_replace("/\\n/", '<span class="d-carrage-return"> &para; </span>', $temp);
			}

			print '<ul class="d-node">';
				print '<li class="d-child">';
					print '<div class="d-preview">'.$data.'</div>';
				print '</li>';
			print '</ul>';
		}

		print '</li>';
	}

	private function _float($data, $name){
		print '<li class="d-child">';
			print '<span class="d-name">'.$name.'</span> <span class="d-type">Float</span> ';
			print $this->config('display.separator').' <span class="d-float">'.$data.'</span>';

			$ut = $this->isTimestamp($name,$data);
			if($ut){
				print ' ~ <span class="d-timestamp">'.$ut.'</span>';
			}
		print '</li>';
	}

	private function _integer($data, $name){
		print '<li class="d-child">';
			print '<span class="d-name">'.$name.'</span> <span class="d-type">Integer</span> ';
			print $this->config('display.separator').' <span class="d-integer">'.$data.'</span>';

			$ut = $this->isTimestamp($name, $data);
			if($ut){
				print ' ~ <span class="d-timestamp">'.$ut.'</span>';
			}
		print '</li>';
	}

	private function _boolean($data, $name){
		print '<li class="d-child">
				<span class="d-name">'.$name.'</span> <span class="d-type">Boolean</span>
				'.$this->config('display.separator').'<span class="d-boolean">'.($data ? 'TRUE' : 'FALSE').'</span>
		</li>';
	}

	private function _null($name){
		print '<li class="d-child">
				<a class="d-name">'.$name.'</a> '.$this->config('display.separator').' <span class="d-type d-null">NULL</span>
		</li>';
	}


	private function sanitizeName($name){
		// Check if the key has whitespace in it, if so show it and add an icon explanation
		$has_white_space = preg_match('/\s+(?![^<>]*>)/x', $name);
		if ($has_white_space) {
			// Convert the white space to unicode underbars to visualize it
			$name  = preg_replace("/\s+(?![^<>]*>)/x","&#9251;",$name);
			$icon = ' <span class="d-icon d-information" title="Note: Key contains white space"></span> ';
			$ret = $name . $icon;
		} else {
			$ret = $name;
		}	
		return $ret;
	}

	private function isCli(){
		return php_sapi_name() === 'cli';
	}

	private function isAssoc($var){
		return is_array($var) && array_diff_key($var,array_keys(array_keys($var)));
	}

	private function isCollapsed($level, $child_count){
		if($this->expand_all)
			return false;

		$cascade = $this->config('display.cascade');
		if($cascade == null){
			return true;
		} else {
			return isset($cascade[$level]) ? $child_count > $cascade[$level] : true;
		}
	}

	private function isTimestamp($name, $value){
		// If the name contains date or time, and the value looks like a unixtime
		if (preg_match("/date|time/i",$name) && ($value > 10000000 && $value < 4000000000)) {
			$ret = date("r",$value);
			return $ret;
		}
		return false;
	}

	private function marker(){
		static $recursion_marker;
		if (!isset($recursion_marker)) {
			$recursion_marker = uniqid('d');
		}
		return $recursion_marker;
	}

	private function &hive(&$bee) {
		static $arr = array();
		// new bee
		if (!is_null($bee)) {
			// stain it
			$recursion_marker = $this->marker();
			if(is_object($bee)) {
				$hash = spl_object_hash($bee);
				if ($hash && isset($this->object_recursion_protection[$hash])) {
					$this->object_recursion_protection[$hash]++;
				} elseif ($hash) {
					$this->object_recursion_protection[$hash] = 1;
				}
			} else {
				if(isset($bee[$recursion_marker])) {
					$bee[$recursion_marker]++;
				} else {
					$bee[$recursion_marker] = 1;
				}
			}
			$arr[0][] =& $bee;
		}
		// return all bees
		return $arr[0];
	}

	private function resources(){
		static $output = false;
		if($output)
			return true;

		$css = '';
		$default_css_file = realpath(__DIR__.'../../resources/d.css');
		$css_file = $this->config('css_file', null, $default_css_file);
		if(is_readable($css_file)){
			$css = file_get_contents($css_file);
		} else {
			$css = '/* missing css file */';
		}

		// skipping over the css url rewriting

		print '<style type="text/css">'."\n";
		print "\t".trim($css)."\n";
		print '</style>'."\n";

		$js = '';
		$js_file = realpath(__DIR__.'../../resources/d.js');
		if(is_readable($js_file)){
			$js = file_get_contents($js_file);
		} else {
			$js = '// missing js file';
		}

		print '<script type="text/javascript">'."\n";
		print "\t".$js."\n";
		print '</script>'."\n";

		$output = true;
	}

}
?>