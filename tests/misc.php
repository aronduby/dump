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

	if (isset($_GET['short']) || php_sapi_name() === 'cli') {
		D::ump($a, D::S(D::KILL));
	}

	print "<h2>regular</h2>\n";
	D::ump(array('likes','kittens','and','dogs'));



	$str = D::ump( ['foo'=>'bar'], D::S(D::OB, 'Capture') );
	print '<p>this should be before the output</p>';
	print $str;


	D::ump( $a, D::S(D::EXPAND, 'Expanded Values by Default'));



	D::ump('likes','kittens','and','dogs', D::S(0,'Passing Multiple Arguments'));



	print '<h2>disabling</h2>'."\n";
	D::ump(D::enabled(), D::S(0, 'enabled?'));
	D::ump(D::disabled(), D::S(0, 'disabled?'));
	D::disable();
	D::ump('you shouldn\'t see this');
	D::enable();



	print '<h2>Flag Check</h2>';
	try{
		D::ump('test', D::S(D::KILL | D::OB));
	} catch (InvalidArgumentException $e){
		print '<p>Exception: '.$e->getMessage().'</p>';
	}

	D::ump( (object)array('a' => array('b' => array('c' => array('d' => array('e' => null))))) , D::S(0, 'Long Object Example'));


	print '<h2>server</h2>'."\n";
	D::server();



	print "<h2>kill</h2>\n";
	D::ump($a, D::S(D::KILL));
	print "<p>If you see this something is broken</p>";


	class bar
	{
	    public $b = 'bb';
	    public $a = 'aa';

	    function foo()
	    {
	        return 'bar';
	    }
	}
	?>
</body>
</html>

