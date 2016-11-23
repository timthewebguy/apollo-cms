<?php 

/**
* Page Class
*/
class Page
{
	public $name;
	public $contents;

	function __construct($n, $c)
	{
		$this->name = $n;
		$this->contents = $c;
	}
}
