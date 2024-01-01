<?php
  $pagename = "scoreboard";
  include("includes/hopmod.php");

/* Date limit disabled
  if ( ! isset($_SESSION['querydate'])) {
    $_SESSION['querydate'] = "month";
  }
*/

  if ( ! isset($_GET['page'])) {
    $_GET['page'] = 0;
  }

// Setup statsdb and assign it to an object.
  $dbh = setup_pdo_statsdb($db);

/* Date limit disabled
  $times = array (
    "year" => date('Y'),
    "week" => date('W'),
    "end" => array (
      "day" => strtotime("today +1 day"),
      "week" => strtotime(date('Y') . "-W" . date('W') . "-7"),
      "month" => mktime(23, 59, 0, date("n"), date("t")),
      "year" => strtotime("today"),
      "nolimit" => strtotime("today +1 day"),
    ),
    "start" => array (
      "day" => strtotime("today"),
      "week" => strtotime(date('Y') . "-W" . date('W') . "-1"),
      "month" => mktime(0, 0, 0, date("n"), 1),
      "year" => strtotime("today -365 days"),
      "nolimit" => "0",
    )
  );

  $_SESSION['end_date'] = $times["end"][$_SESSION['querydate']];
  $_SESSION['start_date'] = $times["start"][$_SESSION['querydate']];

  $day = "";
*/

/* Date limit disabled
	$sql = $dbh->prepare("		
		SELECT name,
			ipaddr AS PlayerIP,
			country AS PlayerCountry,
			SUM(score) AS TotalScored,
			SUM(teamkills) AS TotalTeamkills,
			MAX(frags) AS MostFrags,
			SUM(frags) AS TotalFrags,
			SUM(deaths) AS TotalDeaths,
			COUNT(name) AS TotalGames,
			ROUND((0.0+SUM(hits))/(SUM(hits)+SUM(misses))*100) AS Accuracy,
			ROUND((0.0+SUM(frags))/SUM(deaths),2) AS Kpd
		FROM
			players
		INNER JOIN
			games ON players.game_id=games.id
		WHERE
			UNIX_TIMESTAMP(games.datetime) BETWEEN :start_date AND :end_date
			AND frags > 0 
		GROUP BY
			name
		HAVING
			TotalGames >= :MinimumGames
		ORDER BY
			" . $_SESSION['orderby'] . " DESC");
//		LIMIT
//			" . $_SESSION['paging'] . "," . $rows_per_page);
*/

        $sql = $dbh->prepare("
                SELECT name,
                        ipaddr AS PlayerIP,
                        country AS PlayerCountry,
                        teamkills AS TotalTeamkills,
			suicides AS TotalSuicides,
                        frags AS TotalFrags,
			max_frags AS MostFrags,
                        deaths AS TotalDeaths,
                        games AS TotalGames,
                        ROUND((0.0+hits)/(hits+misses)*100) AS Accuracy,
                        ROUND((0.0+frags)/deaths,2) AS Kpd
                FROM
                        playertotals
                WHERE
                        frags > 0
                      AND games > :MinimumGames
                ORDER BY
                        " . $_SESSION['orderby'] . " DESC
		LIMIT 
                        " . $rows_per_page . "
                      OFFSET " . $_SESSION['paging']);
//$sql = $dbh->prepare("SELECT name, ipaddr AS PlayerIP, country AS PlayerCountry, teamkills AS TotalTeamkills,  frags AS TotalFrags,    deaths AS TotalDeaths,     games AS TotalGames,  ROUND((0.0+hits)/(hits+misses)*100) AS Accuracy FROM playertotals;");

//                WHERE
//                        frags > 0
//			AND games > :MinimumGames
//                ORDER BY
//                        " . $_SESSION['orderby'] . " DESC");


//  $pager_query = $dbh->prepare("SELECT FOUND_ROWS()");
  
$pager_query = $dbh->prepare("SELECT COUNT(*) FROM playertotals WHERE frags > 0 AND games > :MinimumGames");


/* Date limit disabled
  $titles = array (
    "day" => "DAY",
    "week" => "WEEK",
    "month" => "MONTH",
    "year" => "YEAR",
    "nolimit" => "NO LIMIT"
  );
*/
?>

<h1><?php print(colorname("$server_title ")); ?> Scoreboard</h1>

<div id="filter-panel">
  <span class="filter-form" style="margin-right:0.5em">
    <a style="border:0.2em solid; padding:0.5em;margin:0 -0.9em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="activity.php">Daily activity</a>
  </span>
<?php
/*
  <span class="filter-form">
    Limit to this [
    <?php // foreach ($titles as $title => $display) { ?>
        <a href="?querydate=<?= $title ?>" <?= $_SESSION['querydate'] == $title ? "class=\"selected\" style=\"color:black\"" : ""; ?>><?= $display ?></a><?= ($title != "nolimit" ? " | " : "") ?>
  <?php //} ?>
    ]</span>
*/
?>
  <span class="filter-form"><form id="filter-form">Name Filter: <input name="filter" id="filter" value="" maxlength="30" size="30" type="text"></form></span>
  <span class="filter-form"><a style="border:0.2em solid; padding:0.5em;margin:0 -0.6em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="servers.php">Server list</a></span>
</div>
<?php 
/* Date limit disabled
	$sql->execute(array ( ':start_date' => $_SESSION['start_date'], ':end_date' => $_SESSION['end_date'], ':MinimumGames' => $_SESSION['MinimumGames'] ));
*/

//	$sql->execute(array ( ':MinimumGames' => $_SESSION['MinimumGames'], ':Offset' => $_SESSION['paging'], ':NumRows' => $rows_per_page));
      $sql->execute(array ( ':MinimumGames' => $_SESSION['MinimumGames']));

	$pager_query->execute(array(':MinimumGames' => $_SESSION['MinimumGames'] ));
	//$pager_query->execute(array ( ':start_date' => $_SESSION['start_date'], ':end_date' => $_SESSION['end_date'], ':MinimumGames' => $_SESSION['MinimumGames'] ));
	build_pager($_GET['page'], $pager_query, $rows_per_page); //Generate Pager Bar 
	stats_table($sql); //Build stats table data 
?> 
<?php stopbench(); //Stop and display benchmark. ?>
</body>
</html>
