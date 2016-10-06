<?php 

//Media Object Class
class MediaObject {
	public $name;
	public $extension;
	public $abs_path;
	public $id;

	public function __construct($n, $e, $p, $i) {
		$this->name = $n;
		$this->extension = $e;
		$this->abs_path = $p;
		$this->id = $i;
	}
}





