<?php
define("PIC_DL_DIR", "tmp/");
class PictureDownloader{
	public $url;
	public $dir;

	public function __construct ($url, $dir = null){
		$this->url = $url;
		$this->dir = is_null($dir) ? PIC_DL_DIR : $dir;
	}

	public function download(){
		file_put_contents($this->filepath(), file_get_contents($this->url));
	}

	public function filepath(){
		return $this->dir . $this->basename();
	}

	public function basename($suffix = null){
		return basename($this->url, $suffix);
	}
}