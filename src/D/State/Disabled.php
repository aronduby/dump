<?php

namespace D\State;

class Disabled {

	public function __call($method){
		return false;
	}

}

?>