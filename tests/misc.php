<?PHP

include("../vendor/autoload.php");
use \D\D;

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

print "<h2>capture</h2>\n";
$str = D::ump(array('foo' => 'bar'), D::S(D::OB));
print '<p>this should be before the output</p>';
print $str;

print "<h2>regular</h2>\n";
D::ump(array('likes','kittens','and','dogs'));

print "<h2>passing multiple args</h2>\n";
D::ump('likes','kittens','and','dogs');

print '<h2>disabling</h2>'."\n";
D::ump(D::enabled(), D::S(0, 'enabled?'));
D::ump(D::disabled(), D::S(0, 'disabled?'));
D::disable();
D::ump('you shouldn\'t see this');
D::enable();

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

