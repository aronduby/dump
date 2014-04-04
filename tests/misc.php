<!doctype html>
<head>
	<title>D::ump Misc Test</title>
</head>
<body>

	<?php

	include("../vendor/autoload.php");

	interface A{
		public function foo();
	}
	interface B{}

	trait C{
		public $pet = 'Dog';
		private function bark($times = 3){
			while($times > 0){
				print 'bark!';
				$times--;
			}
		}
	}

	class Foo{
		public $test = 'yep';
	}

	class Bar extends Foo implements A, B{

		use C;
		
		const FOO = 'bar';
		const HELLO = 'World';

		public $a = 'aa';
	    public $b = 'bb';

	    public static $d = 'dd';

	    private $c = 'cc';

	    public function foo() {
	        return 'bar';
	    }
	}

	$fp = fopen(__FILE__,"r");

	$a = array(
		'first'           => $fp,
		'last'            => new Bar(),
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
		'datetime'		  => new DateTime(),
		'func'			  => function($arg){ vaR_dump($arg); }
	);

	$a['last']->a = 'zz';
	$a['last']->new_property = 'new';


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


	D::ump($a, D::S(D::KILL, 'D::KILL, there\'s a message below you shouldnt see'));
	print "<p>If you see this something is broken</p>";

	?>
</body>
</html>

