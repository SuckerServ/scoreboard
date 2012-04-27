<?php
$servers = array(
				"jokers-blackjack-table" => array("host" => "servers.cube2.fr", "port" => "10001"),
				"jokers-poker-table" => array("host" => "servers.cube2.fr", "port" => "20001"),
				"bloodfactory" => array("host" => "servers.cube2.fr", "port" => "30001"),
				"duelroom" => array("host" => "servers.cube2.fr", "port" => "40001"),
				"skullfield" => array("host" => "servers.cube2.fr", "port" => "50001")
			);
$db = array(
                "type" => "mysql",               // Type of database : mysql, sqlite3 WARNING :SQLite3 DOESN'T WORK
                "path" => "stats.sqlite",        // Path to SQLite3 Database
                "host" => "localhost",           // MySQL Database host
                "name" => "suckerserv",          // MySQL Database name
                "user" => "suckerserv",          // User for MySQL Database
                "pass" => "suckerserv"           // Password for MySQL Database
            );
$rows_per_page =  "20";
?>
