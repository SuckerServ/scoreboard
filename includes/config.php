<?php
$servers = array(
				"jokers-blackjack-table" => array("host" => "servers.cube2.fr", "port" => "10000"),
				"jokers-poker-table" => array("host" => "servers.cube2.fr", "port" => "20000"),
				"bloodfactory" => array("host" => "servers.cube2.fr", "port" => "30000"),
				"duelroom" => array("host" => "servers.cube2.fr", "port" => "40000"),
				"skullfield" => array("host" => "servers.cube2.fr", "port" => "50000"),
				"hide&seek" => array("host" => "servers.cube2.fr", "port" => "60000")
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
