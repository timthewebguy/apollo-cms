<?php 

class Content {
	public $name;
	public $page;
	public $description;
	public $type;
	public $values;
	public $minValues;
	public $maxValues;


	function __construct($n, $p, $d, $t, $v, $min = 1, $max = 1) {
		$this->name = $n;
		$this->page = $p;
		$this->description = $d;
		$this->type = $t;
		$this->values = $v;
		$this->minValues = $min;
		$this->maxValues = $max;
	}
}

class CustomTypeContent {
	public $fields;
	public $title;

	function __construct($t) {
		$this->$fields = [];
		$this->$title = $t;
	}

	function AddField($content) {
		$this->$fields[] = $content;
	}
}



