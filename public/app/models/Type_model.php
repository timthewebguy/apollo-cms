<?php 

/**
* Type Class
*/
class Type
{
	
	public $name;
	public $description;
	public $displayValue;
	public $structure;

	function __construct($n, $de, $di, $s)
	{
		$this->name = $n;
		$this->description = $de;
		$this->displayValue = $di;
		$this->structure = $s;
	}
}
