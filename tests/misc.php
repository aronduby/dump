<!doctype html>
<head>
	<title>D::ump Misc Test</title>

	<link href="../src/D/resources/d.css" rel="stylesheet" />

</head>
<body>

	<?php

	include("../vendor/autoload.php");
	use \D\D;

	// disabled the css for dev
	D::config('css_file', 'fakefile');

	$fp = fopen(__FILE__,"r");

	$a = array(
		'first'           => $fp,
		'last'            => new bar,
		'null_var'        => NULL,
		'float'           => pi(),
		'bool'            => true,
		' leading_space'  => 6*8,
		'trailing_space ' => 'grapes',
		'middle space'    => 'Mt. Rushmore',
		'phones'          => array(5036541278,8714077831,'x253'),
		'long_str'        => "This is a really long string full of a\n bunch of crap that should eventually wrap. There once was a man from New Mexico...",
		'empty_arr'       => array(),
		'func_str'        => 'preg_replace',
		'address'         => array('street' => '123 Fake Street', 'city' => 'Portland', 'state' => 'Maine'),
		'unixtime'        => 1231241234,
		'microtime'       => microtime(1),
	);

	// basic call
	D::ump(array('likes','kittens','and','dogs'));


	/*
	 *	Pass an instance of D\DumpSettings as the final argument to add Flags and a Title
	 *	D::S() is syntatic sugar to quickly get a DumpSettings object
	 *	Available Flags are:
	 *		D::OB 		turns on output buffering to capture the output and returns it (InvalidArgumentException thrown if used with D::KILL)
	 *		D::KILL 	kills execution of PHP after printing the output (InvalidArgumentException thrown if used with D::OB)
	 *		D::EXPAND 	expands everything by default
	*/
	$str = D::ump( ['foo'=>'bar'], D::S(D::OB, 'D::OB Flag') );
	print '<p>this should be before the output</p>';
	print $str;


	// The D::EXPAND flag
	D::ump( $a, D::S(D::EXPAND, 'Expanded Values by Default'));


	// Variable length arguments
	D::ump('likes','kittens','and','dogs', D::S(0,'Passing Multiple Arguments'));


	// You can globally enable and disable output
	D::ump(D::enabled(), D::S(0, 'enabled?'));
	D::ump(D::disabled(), D::S(0, 'disabled?'));
	D::disable();
	D::ump('you shouldn\'t see this');
	D::enable();



	// Passing in D::KILL and D::OB will result in a InvalidArgumentException since you can't do both
	try{
		D::ump('test', D::S(D::KILL | D::OB));
	} catch (InvalidArgumentException $e){
		D::ump($e, D::S(0, 'Caught the InvalidArgumentException form D::KILL | D::OB'));
	}


	// lots of expanding
	D::ump( (object)array('a' => array('b' => array('c' => array('d' => array('e' => null))))) , D::S(0, 'Long Object Example'));


	/*
	 *	D::ump includes a lot of helper functions for getting different information
	 *	Unless specified, the only argument needed for all of them is an option DumpSettings object
	 *	Helper Functions (see the php docs for to learn more):
	 *	backtrace 	debug_backtrace()
	 *	classes 	get_declared_classes()
	 *	interfaces 	get_declared_interfaces()
	 *	includes 	get_included_files()
	 *	functions 	get_defined_functions()
	 *	defines 	get_defined_constants()
	 *	extensions 	get_loaded_extensions()
	 *	headers 	getAllHeaders()
	 *	phpini 		ini_get_all()
	 *	path 		ini_get('include_path')
	 *	request 	$_REQUEST
	 *	get 		$_GET
	 *	post 		$_POST
	 *	server 		$_SERVER
	 *	cookie 		$_COOKIE
	 *	env 		$_ENV
	 *	session 	$_SESSION
	 *	ini 		parse_ini_file(argument*, true) *argument to the function
	*/
	D::server();



	D::ump($a, D::S(D::KILL, 'D::KILL, there\'s a message below you shouldnt see'));
	print "<p>If you see this something is broken</p>";


	class bar {
		public $a = 'aa';
	    public $b = 'bb';

	    public static $d = 'dd';

	    private $c = 'cc';

	    public function foo() {
	        return 'bar';
	    }
	}
	?>
</body>
</html>

