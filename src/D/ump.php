<?php

namespace D;

class ump
{

	protected $config = [
		/*
		 *	enabled: boolean
		 *	should dump be enabled
		*/
		'enabled' => true,
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

	protected $enabled = true;


	public function __construct(array $config = [])
	{
		$this->config($config);
		if ($this->config['enabled'])
			$this->enable();
		else
			$this->disable();
	}


	public function config($key = null, $val = null, $default = null)
	{
		// passed an array, merge it with config
		if (is_array($key) && $val == null) {
			$this->config = array_merge($this->config, $key);

			// key and val is set, it's a setter
		} elseif ($key != null && $val != null) {
			$this->config[$key] = $val;

			// passed just key, getter
		} elseif ($key != null) {
			return $this->config[$key] !== null ? $this->config[$key] : $default;

			// passed nothing, give back everything
		} else {
			return $this->config;
		}
	}


	/*
	 *	State Setters
	*/
	public function enable()
	{
		$this->enabled = true;

		return $this->enabled();
	}

	public function disable()
	{
		$this->enabled = false;

		return $this->disabled();
	}

	/*
	 *	State Getters
	*/
	public function enabled()
	{
		return $this->enabled === true;
	}

	public function disabled()
	{
		return $this->enabled === false;
	}


	public function dump()
	{
		// the last argument will always be an instance of \D\DumpSettings
		// since that is set in the callers
		$args = func_get_args();
		$settings = array_pop($args);

		// can't have both kill and returh flags set
		if (($settings->flags & (\D::KILL | \D::OB)) === (\D::KILL | \D::OB))
			throw new \InvalidArgumentException('Calling dump with both the KILL and OB flag is invalid. There can only be one.');

		// if we're buffering
		// remove the ob flag, append settings, and call dump again
		if ($settings->flags & \D::OB) {
			$settings->flags = $settings->flags & ~\D::OB;
			$args[] = $settings;

			ob_start();
			call_user_func_array([$this, 'dump'], $args);

			return ob_get_clean();
		}

		// if we're running CLI we don't want all the extra output
		if (!($settings->flags & \D::IGNORE_CLI) && $this->isCli()) {
			foreach ($args as $r) {
				print_r($r);
			}

			if ($settings->flags & \D::KILL)
				die();

			return;
		}


		$clear_object_recursion_protection = false;
		if ($this->object_recursion_protection === NULL) {
			$this->object_recursion_protection = [];
			$clear_object_recursion_protection = true;
		}

		$this->expand_all = ($settings->flags & \D::EXPAND);

		print "\n" . '<section class="d-wrapper" data-expanded="' . ($settings->flags & \D::EXPAND ? 'true' : 'false') . '">' . "\n";
		print "\t" . '<header class="d-header">' . "\n";

		print "\t\t" . '<p class="d-toggle-all">toggle all</p>' . "\n";
		print $settings->title ? "\t\t" . '<h1 class="d-title">' . $settings->title . '</h1>' . "\n" : '';

		if ($this->config('display.show_call_info', null, true)) {
			print "\t\t" . '<span class="d-call" style="white-space:nowrap;">';
			print 'Called from <code class="d-file">' . $settings->backtrace['file'] . '</code>, ';
			print 'line <code class="d-line">' . $settings->backtrace['line'] . '</code>';
			print '</span>' . "\n";
		}

		print "\t" . '</header>' . "\n";

		foreach ($args as $data) {
			print "\t" . '<div class="d-root">' . "\n";
			print "\t" . '<ul class="d-node">' . "\n";

			print "\t\t" . $this->render($data) . "\n";

			print "\t" . '</ul>' . "\n";
			print "\t" . '</div>' . "\n";
		}


		if ($this->config('display.show_version', null, true)) {
			$url = 'http://github.com/aronduby/dump';

			print "\t" . '<footer class="d-footer">' . "\n";
			print "\t\t" . '<div class="d-version" style="white-space:nowrap;">' . "\n";
			print '<strong class="d-version-number">D::ump v' . \D::VERSION . '</strong>' . "\n";
			print ' | <a class="d-url" href="' . $url . '" target="_blank">' . $url . '</a>' . "\n";
			print "\t\t" . '</div>' . "\n";
			print "\t" . '</footer>' . "\n";
		}

		// resources (css/js)
		$this->resources();

		print '</section>' . "\n";

		// flee the hive
		$recursion_marker = $this->marker();
		if ($hive =& $this->hive($dummy)) {
			foreach ($hive as $i => $bee) {
				if (is_object($bee)) {
					if (
						($hash = spl_object_hash($bee))
						&& isset($this->object_recursion_protection[$hash])
					) {
						unset($this->object_recursion_protection[$hash]);
					}
				} elseif (isset($hive[$i]->$recursion_marker)) {
					unset($hive[$i][$recursion_marker]);
				}
			}
		}

		if ($clear_object_recursion_protection) {
			$this->object_recursion_protection = null;
		}


		if ($settings->flags & \D::KILL)
			die();
	}


	/*
	 *	Private Rendering Functions and helpers
	*/
	private $level = 0; // recursion level
	private $object_recursion_protection = null;
	private $expand_all = false;

	private function render(&$data, $name = "&raquo;")
	{

		// Highlight elements that have a space in their name.
		// Spaces are hard to see in the HTML and are hard to troubleshoot
		$name = $this->sanitizeName($name);

		if (is_object($data))
			return $this->_object($data, $name);

		if (is_callable($data))
			return $this->_callable($data, $name);

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

	private function _recursion()
	{
		$html = '<ul class="d-node">
					<li class="d-child">
						<a class="d-name">&#8734;</a>
						(<em class="d-type">Recursion</em>)
					</li>
				</ul>';

		// should that div.d-nest be closed?
		echo $html;
	}

	private function _object(&$data, $name)
	{
		$reflection = new \ReflectionObject($data);
		$properties = $reflection->getProperties();

		$child_count = count($properties);
		$collapsed = $this->isCollapsed($this->level, $child_count);

		$classes = (count($data) > 0 ? 'd-expandable' . (!$collapsed ? ' d-open' : '') : '');
		print '<li class="d-child ' . $classes . '">';
		print '<span class="d-name">' . $name . '</span> <span class="d-type">Object</span> ';
		print $this->config('display.separator');
		print '<span class="d-class">' . get_class($data) . '</span>';
		print (count($child_count) == 0 ? ' (empty)' : '');

		if ($properties) {
			$this->_vars($data);
		}
		print '</li>';
	}

	// properties of an array or object
	private function _vars(&$data)
	{
		$is_object = is_object($data);
		$recursion_marker = $this->marker();

		if ($is_object) {
			if (($hash = spl_object_hash($data)) && isset($this->object_recursion_protection[$hash])) {
				$r = $this->object_recursion_protection[$hash];
			} else {
				$r = NULL;
			}
		} else {
			$r = isset($data[$recursion_marker]) ? $data[$recursion_marker] : null;
		}

		// recursion detected
		if ($r > 0) {
			return $this->_recursion();
		}

		// stain it
		$this->hive($data);

		// rendering
		print '<ul class="d-node">';

		// deeper and deeper, way down
		$this->level++;

		if ($is_object) {
			$reflection = new \ReflectionObject($data);

			$this->_reflectionParent($reflection);
			$this->_reflectionInterfaces($reflection);
			$this->_reflectionTraits($reflection);
			$this->_reflectionConstants($reflection);
			$this->_reflectionProperties($reflection, $data);
			$this->_reflectionMethods($reflection);

		} else {
			// keys
			$keys = array_keys($data);
			foreach ($keys as $k) {
				// skip marker
				if ($k === $recursion_marker)
					continue;

				// get real value
				$v =& $data[$k];
				$this->render($v, $k);
			}
		}

		print '</ul>' . "\n";
		$this->level--;
	}

	private function _reflectionParent(\ReflectionObject $reflection)
	{
		if ($pc = $reflection->getParentClass()) {
			$name = '<span class="d-information" title="Class this object extends"></span>&nbsp;';
			$name .= '<span class="d-obj-info">Extends</span>';
			// $inst = $pc->newInstanceWithoutConstructor();
			$this->render($pc->name, $name);
		}
	}

	private function _reflectionInterfaces(\ReflectionObject $reflection)
	{
		$interfaces = $reflection->getInterfaceNames();
		if (count($interfaces)) {
			$name = '<span class="d-information" title="Interfaces that this object implements"></span>&nbsp;';
			$name .= '<span class="d-obj-info">Interfaces</span>';
			$inamesstr = implode(', ', $interfaces);
			$this->render($inamesstr, $name);
		}
	}

	private function _reflectionTraits(\ReflectionObject $reflection)
	{
		$traits = $reflection->getTraitNames();
		if (count($traits)) {
			$name = '<span class="d-information" title="Traits this object uses"></span>&nbsp;';
			$name .= '<span class="d-obj-info">Traits</span>';
			$traits = implode(', ', $traits);
			$this->render($traits, $name);
		}
	}

	private function _reflectionConstants(\ReflectionObject $reflection)
	{
		$constants = $reflection->getConstants();
		if (count($constants)) {
			$name = '<span class="d-information" title="Constants defined in this object"></span>&nbsp;';
			$name .= '<span class="d-obj-info">Class&nbsp;Constants</span>';
			$this->render($constants, $name);
		}
	}

	private function _reflectionProperties(\ReflectionObject $reflection, $data)
	{
		$cache = [];
		$properties = $reflection->getProperties();
		$default_properties = $reflection->getDefaultProperties();

		foreach ($properties as $property) {
			$visibility = null;

			if ($property->isPrivate()) {
				$visibility = 'private';
			} elseif ($property->isProtected()) {
				$visibility = 'protected';
			} elseif ($property->isPublic()) {
				$visibility = 'public';
			}

			if ($property->isStatic())
				$visibility .= '&nbsp;static';

			$name = $property->getName();
			if ($property->isPrivate() || $property->isProtected()) {
				$property->setAccessible(true);
			}
			$value = $property->getValue($data);


			// additional information about this property
			// gets appended after the name
			$info_flgs = [];
			$is_default_property = $property->isDefault();
			if ($is_default_property && ($value == $default_properties[$name])) {
				$info_flgs[] = '<span class="d-obj-icon d-obj-prop-default-val" title="This is the Property\'s Default Value"></span>';
			}
			if (!$is_default_property) {
				$info_flgs[] = '<span class="d-obj-icon d-obj-prop-added" title="This Property Was Added Dynamically"></span>';
			}

			$display_name = '<span class="d-visibility">' . $visibility . '</span>&nbsp;' . $name . (count($info_flgs) ? implode('', $info_flgs) : '');
			$cache[$display_name] = $value;

			if ($property->isPrivate() || $property->isProtected()) {
				$property->setAccessible(false);
			}
		}

		$name = '<span class="d-information" title="Properties in this object (includes inherited and dynamically added)"></span>&nbsp;';
		$name .= '<span class="d-obj-info">Properties</span>';
		$this->render($cache, $name);
	}

	private function _reflectionMethods(\ReflectionObject $reflection)
	{
		$cache = [];
		$methods = $reflection->getMethods();
		if (!count($methods))
			return false;

		foreach ($methods as $method) {
			$visibility = '';

			if ($method->isFinal())
				$visibility = 'final&nbsp;';

			if ($method->isPrivate()) {
				$visibility .= 'private';
			} elseif ($method->isProtected()) {
				$visibility .= 'protected';
			} elseif ($method->isPublic()) {
				$visibility .= 'public';
			}

			if ($method->isStatic())
				$visibility .= '&nbsp;static';


			$display_name = '<span class="d-visibility">' . $visibility . '</span>&nbsp;' . $method->name;
			$cache[$display_name] = '( ' . implode(', ', $this->_reflectionParameters($method)) . ' )';
		}

		if ($this->config('sorting.arrays', null, true)) {
			$new = $cache;
			ksort($new);
			if ($new === $cache) {
				$sorted = false;
			} else {
				$cache = $new;
				$sorted = true;
			}
		} else {
			$sorted = false;
		}

		$child_count = count($cache);
		$collapsed = $this->isCollapsed($this->level, count($cache));

		$name = '<span class="d-information" title="Methods in this object (includes inherited)"></span>&nbsp;';
		$name .= '<span class="d-obj-info">Methods</span>';

		print '<li class="d-child ' . ($child_count > 0 ? 'd-expandable ' . (!$collapsed ? 'd-open' : '') : '') . '">';
		print '<span class="d-name">' . $name . '</span> <span class="d-type">Array(<span class="d-array-length">';
		print count($cache) . '</span>)</span>';
		if (count($cache) > 0)
			print " &hellip;";
		if ($sorted)
			print ' <span class="d-sorted" title="Array has been sorted prior to display. This is configurable using config sorting.arrays">Sorted</span>';

		print '<ul class="d-node">';
		foreach ($cache as $k => $v) {
			$this->_formattedString($v, $k);
		}
		print '</ul>';
	}

	private function _reflectionParameters(\ReflectionFunctionAbstract $function)
	{
		// argument list
		$params = [];
		foreach ($function->getParameters() as $param) {
			$optional = $param->isOptional();
			$temp = '<span class="d-param-' . ($optional ? 'optional' : 'required') . '" title="parameter is ' . ($optional ? 'optional' : 'required') . '">';
			if ($param->isPassedByReference())
				$temp .= '<span class="d-param-reference">&</span>';
			$temp .= '$' . $param->name;
			if ($optional) {
				try {
					$temp .= ' = ' . $param->getDefaultValue();
				} catch (\ReflectionException $e) {
					$temp .= '<span class="d-obj-icon d-param-exception" title="Reflection: ' . $e->getMessage() . '"></span>';
				}
			}
			$temp .= '</span>';

			$params[] = $temp;
		}

		return $params;
	}

	private function _callable($data, $name)
	{
		$function = new \ReflectionFunction($data);
		$params = $this->_reflectionParameters($function);

		print '<li class="d-child">';
		print '<span class="d-name">' . $name . '</span> <span class="d-type">Callable</span>';
		print $this->config('display.separator') . '<span class="d-callable">' . $function->name . '</span> (' . implode(', ', $this->_reflectionParameters($function)) . ')';
	}

	private function _array($data, $name)
	{
		$config_sort = $this->config('sorting.arrays', null, true);

		// If the sort is enabled in the config (default = yes) and the array is assoc (non-numeric)
		if (sizeof($data) > 1 && $config_sort && $this->isAssoc($data)) {
			// Copy the array to a temp variable and sort it
			$new = $data;
			ksort($new);

			// If the sorted array is the same as the old don't sort it
			if ($new === $data) {
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


		print '<li class="d-child ' . ($child_count > 0 ? 'd-expandable ' . (!$collapsed ? 'd-open' : '') : '') . '">';

		print '<span class="d-name">' . $name . '</span> <span class="d-type">Array(<span class="d-array-length">';
		print count($data) . '</span>)</span>';

		if (count($data) > 0) {
			print " &hellip;";
		}

		if ($sort) {
			print ' <span class="d-sorted" title="Array has been sorted prior to display. This is configurable using config sorting.arrays">Sorted</span>';
		}

		// callback
		if (is_callable($data)) {
			$vals = array_values($data);
			print '<span class="d-callback"> |';
			print ' (<span class="d-type">Callback</span>) <span class="d-string">';
			if (!is_object($vals[0])) {
				echo htmlSpecialChars($vals[0]);
			} else {
				echo htmlSpecialChars(get_class($vals[0])) . '::';
			}

			echo htmlSpecialChars($vals[1]) . '()</span></span>';
		}

		if (count($data)) {
			$this->_vars($data);
		}

		print "</li>";
	}

	private function _resource($data, $name)
	{
		$html = '<li class="d-child">
				<span class="d-name">' . $name . '</span> <span class="d-type">Resource</span>
				' . $this->config('display.separator') . '<span class="d-resource">' . get_resource_type($data) . '</span>
		</li>';

		echo $html;
	}

	private function _string($data, $name)
	{
		// extra
		$extra = false;
		$temp = $data;

		// Get the truncate length from the config, or default to 100
		$truncate_length = $this->config('display.truncate_length', null, 100);
		$replace_returns = $this->config('display.replace_returns', null, true);

		if (strlen($data) > $truncate_length) {
			$temp = substr($data, 0, $truncate_length - 1);
			$extra = true;
		}

		$temp = htmlentities($temp);

		if ($replace_returns) {
			$temp = nl2br($temp);
		} else {
			$temp = preg_replace("/\\n/", '<span class="d-carrage-return"> &para; </span>', $temp);
		}

		$collapsed = $this->isCollapsed($this->level, 1);
		print '<li class="d-child ' . ($extra ? 'd-expandable' . (!$collapsed ? ' d-open' : '') : '') . '">';
		print '<span class="d-name">' . $name . '</span> ';
		print '<span class="d-type">String(<span class="d-string-length">' . strlen($data) . '</span>)</span> ';
		print $this->config('display.separator') . '<span class="d-string">' . $temp;

		// This has to go AFTER the htmlspecialchars
		if ($extra) {
			print '&hellip;';
		}
		print '</span>';

		$ut = $this->isTimestamp($name, $data);
		if ($ut) {
			print ' ~ <span class="d-timestamp">' . $ut . '</span>';
		}

		// callback
		if (is_callable($data)) {
			print '<span class="d-callback"> | ';
			print '(<span class="d-type">Callback</span>) <span class="d-string">' . htmlSpecialChars($temp) . '()</span></span>';
		}

		if ($extra) {
			$data = htmlentities($data);

			if ($replace_returns) {
				$temp = nl2br($temp);
			} else {
				$temp = preg_replace("/\\n/", '<span class="d-carrage-return"> &para; </span>', $temp);
			}

			print '<ul class="d-node">';
			print '<li class="d-child">';
			print '<div class="d-preview">' . $data . '</div>';
			print '</li>';
			print '</ul>';
		}

		print '</li>';
	}

	private function _formattedString($data, $name)
	{
		$name = $this->sanitizeName($name);

		print '<li class="d-child">';
		print '<span class="d-name">' . $name . '</span> ';
		print '<span class="d-string-formatted">' . $data . '</span>';
		print '</li>';
	}

	private function _float($data, $name)
	{
		print '<li class="d-child">';
		print '<span class="d-name">' . $name . '</span> <span class="d-type">Float</span> ';
		print $this->config('display.separator') . ' <span class="d-float">' . $data . '</span>';

		$ut = $this->isTimestamp($name, $data);
		if ($ut) {
			print ' ~ <span class="d-timestamp">' . $ut . '</span>';
		}
		print '</li>';
	}

	private function _integer($data, $name)
	{
		print '<li class="d-child">';
		print '<span class="d-name">' . $name . '</span> <span class="d-type">Integer</span> ';
		print $this->config('display.separator') . ' <span class="d-integer">' . $data . '</span>';

		$ut = $this->isTimestamp($name, $data);
		if ($ut) {
			print ' ~ <span class="d-timestamp">' . $ut . '</span>';
		}
		print '</li>';
	}

	private function _boolean($data, $name)
	{
		print '<li class="d-child">
				<span class="d-name">' . $name . '</span> <span class="d-type">Boolean</span>
				' . $this->config('display.separator') . '<span class="d-boolean">' . ($data ? 'TRUE' : 'FALSE') . '</span>
		</li>';
	}

	private function _null($name)
	{
		print '<li class="d-child">
				<a class="d-name">' . $name . '</a> ' . $this->config('display.separator') . ' <span class="d-type d-null">NULL</span>
		</li>';
	}


	private function sanitizeName($name)
	{
		// Check if the key has whitespace in it, if so show it and add an icon explanation
		$has_white_space = preg_match('/\s+(?![^<>]*>)/x', $name);
		if ($has_white_space) {
			// Convert the white space to unicode underbars to visualize it
			$name = preg_replace("/\s+(?![^<>]*>)/x", "&#9251;", $name);
			$icon = '';// <span class="d-icon d-information" title="Key contains white space"></span> ';
			$ret = $name . $icon;
		} else {
			$ret = $name;
		}

		return $ret;
	}

	private function isCli()
	{
		return php_sapi_name() === 'cli';
	}

	private function isAssoc($var)
	{
		return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
	}

	private function isCollapsed($level, $child_count)
	{
		if ($this->expand_all)
			return false;

		$cascade = $this->config('display.cascade');
		if ($cascade == null) {
			return true;
		} else {
			return isset($cascade[$level]) ? $child_count > $cascade[$level] : true;
		}
	}

	private function isTimestamp($name, $value)
	{
		// If the name contains date or time, and the value looks like a unixtime
		if (preg_match("/date|time/i", $name) && ($value > 10000000 && $value < 4000000000)) {
			$ret = date("r", $value);

			return $ret;
		}

		return false;
	}

	private function marker()
	{
		static $recursion_marker;
		if (!isset($recursion_marker)) {
			$recursion_marker = uniqid('d');
		}

		return $recursion_marker;
	}

	private function &hive(&$bee)
	{
		static $arr = [];
		// new bee
		if (!is_null($bee)) {
			// stain it
			$recursion_marker = $this->marker();
			if (is_object($bee)) {
				$hash = spl_object_hash($bee);
				if ($hash && isset($this->object_recursion_protection[$hash])) {
					$this->object_recursion_protection[$hash]++;
				} elseif ($hash) {
					$this->object_recursion_protection[$hash] = 1;
				}
			} else {
				if (isset($bee[$recursion_marker])) {
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

	private function resources()
	{
		static $output = false;
		if ($output)
			return true;

		$css = '';
		$default_css_file = realpath(__DIR__ . '/resources/d.css');
		$css_file = $this->config('css_file', null, $default_css_file);
		if (is_readable($css_file)) {
			$css = file_get_contents($css_file);
		} else {
			$css = '/* missing css file */';
		}

		// skipping over the css url rewriting

		print '<style type="text/css">' . "\n";
		print "\t" . trim($css) . "\n";
		print '</style>' . "\n";

		$js = '';
		$js_file = realpath(__DIR__ . '/resources/d.js');
		if (is_readable($js_file)) {
			$js = file_get_contents($js_file);
		} else {
			$js = '// missing js file';
		}

		print '<script type="text/javascript">' . "\n";
		print "\t" . $js . "\n";
		print '</script>' . "\n";

		$output = true;
	}

}

?>