#D::ump#
A `print_r`/`var_dump` replacement for PHP >= 5.4.0 based on [Krumo](http://krumo.sourceforge.net/) and the [oodle/krumo](https://github.com/oodle/krumo) fork.

![D::ump screenshot](https://googledrive.com/host/0B8oqop_VKmsoLXQxMDdvaThxdkE/screenshot.png "D::ump screenshot")

##Differences from Krumo
- *modified* HTML, CSS, and JS has been completely reworked, makes multiple arguments passed look like they're together
- *added* Passing in bitmask to be able to do Titles, Output Buffering, and Stopping Executing after output
- *added* Object Reflection to not only show the properties (which are now sorted as arrays) but also:
	- Parent Class Name
	- Interface Names
	- Trait Names
	- Constants
	- Methods (with the argument list and defaults*)
- *added* Callables are now first class citizens instead of strings, complete with argument list and defaults* from reflection
- *removed* All the different skins, if you *really* want it to look different there's a `css_file` [config](#config) option, but who cares, its development
- *removed* All the helper functions, because why not just type `D::ump($_ENV)`, not like your going to remember them all anyway
- *removed* .ini settings file is gone, use [`D::config()`](#config) if you want to set global changes
- probably some other things...

_**Note:**_ reflection can't get the default values for arguments of built-in methods/functions, it will display the argument name with a note in these circumstances
##Install
###Using [Composer](http://getcomposer.org)
```json
{
	"require": {
		"aronduby/dump" : "*"
	}
}
```
then run `composer install` or `composer update`

##Usage
###Basic Usage
```php
D::ump($arg1);
```

You can also pass multiple arguments:
```php
D::ump($arg1, $arg2, $arg3);
```

###Dump Settings
If you pass an instance of `D\DumpSettings` as the last argument to `D::ump` you can set a title, output buffer the return, kill the process after returning, and expand all the collapsibles by default.

```php
$ds = new \D\DumpSettings(D::KILL | D::EXPAND, 'This is a Title');
D::ump($arg1, $arg2, $ds);
```

####Short Cut:
The `D` object has a shortcut to quickly create and return an instance of `D\DumpSettings`, so the same example could be rewritten as
```php
D::ump($arg1, $arg2, D::S(D::KILL | D::EXPAND, 'This is a Title'));
```

####Flags
The following flags are available as constants of the `D` class:
- **D::KILL** -- will call die() after output
- **D::OB** -- will use the output buffer and return the output instead of printing it
- **D::EXPAND** -- starts with the output fully expanded
- **D::IGNORE_CLI** -- by default, if the script detects you are running command line it just uses `print_r`, use this to include the full output, useful if you are doing html logging

_**Note:**_ Passing a bitmask containing both `D::KILL` and `D::OB` will result in an `InvalidArgumentException` being thrown since you can't do both
_**Note:**_ `D\DumpSettings` also has a backtrace property which is used by `D::ump()`

##Config
You can globally modify the following properties by passing an associative array into `D::config($arr)` with the following values

|Key |Type |Default |Description |
|:---|:---:|:------:|:-----------|
|**enabled**|Boolean|true|globally enabled/disable output, can also call `D::disable` & `D::enable`|
|**css_file**|String|null|path to a custom CSS file, file will be read in using `file_get_contents`, should be absolute path|
|**display.separator**|String| => |string to use as a separator between the key/values (the default is wrapped in spaces)|
|**display.truncate_length**|Integer|80|If a string is longer than X characters it will be truncated with the non-truncated version displaying as a collapsible|
|**display.cascade**|Array|null|Array of integers to determine when a level should collapse. If the specified level has greater than X amount of element it shows collapsed. `display.cascade=>[5,10]` will expand the first level if there are 5 or less items and the second level with 10 or less. Set to null to have everything collapse|
|**display.show_version**|Boolean|true|Include version # and link in the footer of the output|
|**display.show_call_info**|Boolean|true|Include the file/line # D was called from|
|**display.replace_returns**|Boolean|false|Should we replace returns `\n` with br's in the output|
|**sorting.arrays**|Boolean|true|Reorder associative arrays (and object properties and methods) based on their keys|


###Example
```php
D::config([
	'css_file' => "absolute/path/to/your/custom/css/file.css",
	'display.cascade' => [5, 10],
	'sorting.arrays' => false
]);
// ... some other stuff in your code
D::ump($arg1);
```