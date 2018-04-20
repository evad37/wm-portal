<?php
/*
  - Based on source files from: "PHP Hit Counter"
  - https://github.com/JulianLaval/php-hit-counter
  -
  - Original files:
  - Copyright (c) 2013 Julian Laval
  - 
  - Permission is hereby granted, free of charge, to any person obtaining a copy of
  - this software and associated documentation files (the "Software"), to deal in
  - the Software without restriction, including without limitation the rights to
  - use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
  - the Software, and to permit persons to whom the Software is furnished to do so,
  - subject to the following conditions:
  - 
  - The above copyright notice and this permission notice shall be included in all
  - copies or substantial portions of the Software.
  - 
  - THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  - IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
  - FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
  - COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
  - IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
  - CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
  - 
  - 
  - This modified file:
  - BSD-3-Clause License (see the LICENSE file for full details).
*/
function getConnectInfo($server) {
	if ( $server == 'localhost' ) {
		return [
			"host"  => "localhost",
			"name"  => "pageviews",
			"user"  => "root",
			"pw"    => "",
		];
	} else {
		$ts_pw = posix_getpwuid(posix_getuid());
		$ts_mycnf = parse_ini_file($ts_pw['dir'] . "/replica.my.cnf");

		return [
			"host"  => "tools.db.svc.eqiad.wmflabs",
			"name"  => $ts_mycnf['user'] . "__views",
			"user"  => $ts_mycnf['user'],
			"pw"    => $ts_mycnf['password'],
		];
	}
}

function connect($server) {
	// DB CONNECT INFO
	$info = getConnectInfo($server);

	// CONNECT TO DB
	try {   
		$db = new PDO(
			"mysql:host=".$info['host'].";dbname=".$info['name'],
			$info['user'],
			$info['pw'],
			array(
				PDO::ATTR_PERSISTENT => false,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false
			)
		);
		return $db;
	}  
	catch(PDOException $e) {  
		throw new Exception($e->getMessage());
	}
}

function checkPageName($page_name, $date, $db){
		$sql = "SELECT * FROM `hits` WHERE page = :page AND date = :date";
		$query = $db->prepare($sql);
		$query->execute([':page' => $page_name, ':date' => $date]);
		if ($query->rowCount() == 0){
			$sql = "INSERT INTO `hits` (page, date, count) VALUES (:page, :date, 0)";
			$query = $db->prepare($sql);
			$query->execute([':page' => $page_name, ':date' => $date]);
		}
}

function updateCounter($page_name){
	try {
		$db = connect(htmlspecialchars($_SERVER['HTTP_HOST']));
		$today = gmdate("Y-m-d");
		checkPageName($page_name, $today, $db);
		$sql = "UPDATE `hits` SET count = count+1 WHERE page = :page AND date = :date";
		$query = $db->prepare($sql);
		$query->execute([':page' => $page_name, ':date' => $today]);
	} catch(Exception $e) {
		echo "<!-- Database error: " . $e . " -->";
	}
}

function getHits($item, $startdate, $enddate){
	if ( !preg_match("/^Q\d+$/", $item) ) {
		die("Bad item code");
	}
	if (
		!preg_match("/^\d\d\d\d-\d\d-\d\d$/", $startdate) ||
		!preg_match("/^\d\d\d\d-\d\d-\d\d$/", $enddate) ||
		strtotime($startdate) === false ||
		strtotime($enddate) === false
	) {
		die("Bad date code: must be formatted as YYYY-MM-DD");
	}
	
	try {
		$db = connect(htmlspecialchars($_SERVER['HTTP_HOST']));
		$sql = "SELECT `date`,`count` FROM `hits` WHERE `page`= :page AND `date` >= :date1 AND `date` <= :date2 ORDER BY `date`";
		$query = $db->prepare($sql);
		$query->execute([':page' => $item, ':date1' => $startdate, ':date2' => $enddate]);
		$hits = $query->fetchAll(PDO::FETCH_UNIQUE);
		return $hits;
	} catch(Exception $e) {
		echo "<!-- Database error: " . $e . " -->";
		throw new Exception($e->getMessage());
	}
}

?>