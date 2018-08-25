<?php

class Database {
	
	public $db;
	
	function __construct(){
		$this->db = new mysqli("localhost", "root", "", "bot");
	}
	
	function __destruct(){
		$this->db->close();
	}
}