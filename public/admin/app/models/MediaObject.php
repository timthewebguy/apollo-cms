<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

//Media Object Class
class MediaObject {
	public $name;
	public $guid;
	public $extension;
	public $path;
	public $id;

	public function __construct($n, $g, $e, $p, $i) {
		$this->name = $n;
		$this->guid = $g;
		$this->extension = $e;
		$this->path = $p;
		$this->id = $i;
	}
}
