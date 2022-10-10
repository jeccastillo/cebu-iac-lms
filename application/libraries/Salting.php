<?php
	class salting {
		private $string_orig;
		private $string_hash;
		private $string_output;
		private $string_url;
		
		/*public function salting() {
			//global $hash;
			//$this->set_hash($hash);
		}*/

			
		public function __construct() {
			//global $hash;
			//$this->set_hash($hash);
		}
		
		public function set_string($string) {
			$this->string_orig = $string;
		}
		
		public function set_hash($hash) {
			$this->string_hash = $hash;
		}
		
		public function set_string_url($string) {
			$this->string_url = $string;
		}
		
		public function hash_string() {
			$str_rev = strrev($this->string_orig);
			$get_string = "";
			for($x=0;$x<strlen($str_rev);$x++){
				$inc = 0;
				if($this->string_hash[$inc]!="") {
					$get_string.=$str_rev[$x].$this->string_hash[$inc];
					$inc++;
				} else {
					$inc=0;
					$get_string.=$str_rev[$x].$this->string_hash[$inc];
				}
			}
			$this->string_output = substr($this->string_hash, 0, 5).$get_string.substr($this->string_hash, -5);
			return $this->string_output;
		}
		
		public function unhash_string() {
			$str_hash_first = substr($this->string_orig, 0, 5);
			$str_hash_last = substr($this->string_orig, -5);
			$str_unhash_first = str_replace($str_hash_first, "", $this->string_orig);
			$str_unhash = str_replace($str_hash_last, "", $str_unhash_first);
			$get_string = "";
			$inc = 0;
			for($x=0;$x<strlen($str_unhash);$x++) {
				if($inc==0) {
					$inc=1;
					$get_string.=$str_unhash[$x];
				} else {
					$inc=0;
				}
			}
			$this->string_output = strrev($get_string);
			return $this->string_output;
		}
		public function url_encode() {
			return base64_encode(urlencode($this->string_url));
		}
		public function url_decode() {
			return $a = @split('%3D', @base64_decode($this->string_url));
		}
        
        
	}
?>