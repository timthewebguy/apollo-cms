<?php

class Content {
	public $name;
	public $page;
	public $description;
	public $type;
	public $values;
	public $guid;
	public $minValues;
	public $maxValues;


	function __construct($n, $p, $d, $t, $v, $g, $min = 1, $max = 1) {
		$this->name = $n;
		$this->page = $p;
		$this->description = $d;
		$this->type = $t;
		$this->values = $v;
		$this->guid = $g;
		$this->minValues = $min;
		$this->maxValues = $max;
	}
}
