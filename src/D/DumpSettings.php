<?php

namespace D;

class DumpSettings{
	
	public $flags = 0;
	public $title = false;

	public function __construct($flags  = 0, $title = false){
		$this->flags = $flags;
		$this->title = $title;
	}
}

?>