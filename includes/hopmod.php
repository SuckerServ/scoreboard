<?php
include("config.php");
include("extinfo.php");

if($displayed_server == "") {
    $server_title = get_info(reset($servers)['host'], reset($servers)['port']);
} else {
    $server_title = get_info($servers[$displayed_server]['host'], $servers[$displayed_server]['port']);
}
$server_title = $server_title['server'];

//Table options
$desc_stats_table = array (
    array("name" => "Name", "description" => "Players Nick Name", "column" => "name"),
    array("name" => "Country", "description" => "Players Country", "column" => "country"),
    array("name" => "Score", "description" => "The total score for all games", "column" => "TotalScored"),
    array("name" => "Frags", "description" => "The total number of frags for all games", "column" => "TotalFrags"),
    array("name" => "Deaths", "description" => "The total number of deaths for all games", "column" => "TotalDeaths"),
    array("name" => "Max Frags", "description" => "The most frags ever acheived in one game", "column" => "MostFrags"),
    array("name" => "Accuracy", "description" => "The percentage of shots fired that resulted in a frag", "column" => "Accuracy"),
    array("name" => "KpD", "description" => "The number of frags made before being killed", "column" => "Kpd"),
    array("name" => "TK", "description" => "The number of times a team member was fragged", "column" => "TotalTeamkills"),
    array("name" => "Games", "description" => "The total number of games played", "column" => "TotalGames"),
);

$stats_column_to_name = array();
foreach($desc_stats_table as $row) {
    $stats_column_to_name[$row['column']] = $row['name'];
}

$desc_match_table = array (
    array("name" => "Game ID", "description" => "Global game number", "column" => "id"),
    array("name" => "Server", "description" => "The name of the server who started the game", "column" => "servername"),
    array("name" => "Date/Time", "description" => "Date and time when the game started", "column" => "datetime"),
    array("name" => "Duration", "description" => "Duration of the game in minutes", "column" => "duration"),
    array("name" => "Map", "description" => "Name of the played map", "column" => "mapname"),
    array("name" => "Mode", "description" => "Mode of the game", "column" => "gamemode"),
    array("name" => "Players", "description" => "The number of players during the game", "column" => "players"),
);

$match_column_to_name = array();
foreach($desc_match_table as $row) {
    $match_column_to_name[$row['column']] = $row['name'];
}

function select_columns($var)
{
	global $column_list;
        return preg_match("/($column_list)/", $var['name']) == 0;
}
function column_wrapper($array, $filter) {  // Wrapper for select_columns
	if (! $filter ) { return $array; }
	global $column_list;
	$column_list = $filter;
	$filtered_array = array_filter($array, "select_columns");
	$column_list = "";
	return $filtered_array;
}

function count_rows($query) {
	global $dbh;
	$count = $dbh->query($query) or die(print_r($dbh->errorInfo()));
	return $count->fetchColumn();
	
}
function startbench() {
	global $starttime;
	$mtime = microtime();
	$mtime = explode(' ', $mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
	return $starttime;
}
function stopbench() {
	global $starttime;
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
?>
<div id="footer">
<span id="date">This page was last updated <?php print date("F j, Y, g:i a"); ?> .</span> | <a href="http://www.sauerbraten.org">Sauerbraten.org</a> | <a href="http://suckerserv.googlecode.com">SuckerServ</a>
<?php echo '<p>This page was created in ' .round($totaltime,5). ' seconds.</p>'; ?>
</div>
<?php
}

function colorname($string) {
	$tmp = "";
	$ccode = false;
	$colored = false;

	$colors = array (
		"green",
		"blue",
		"yellow",
		"red",
		"grey",
		"magenta",
		"orange",
		"f" => "black"
	);

	for($i = 0; $i < strlen($string); $i++) {
		$c = $string[$i];
		if ($c == "") {
			$ccode = true;
			if ($colored == true) { $tmp .= "</span>"; } else { $colored = true; }
			continue;
		}
		if ($ccode) {
			$ccode = false;
                        $tmp .= '<span style="color:' . $colors[$c] . '">';
			continue;
		}
		$tmp .= $c;
	}
	if ($colored == true) { $tmp .= "</span>"; }
	return $tmp;
}

function strip_color_codes($string) {
	$tmp = "";
	$skip = false;
	for($i = 0; $i < strlen($string); $i++) {
		$c = $string[$i];
		if ($c == "") { $skip = true; continue; }
		if ($skip) { $skip = false; continue; }
		$tmp .= $c;
	}
	return $tmp;
}

function overlib($overtext,$heading = "") {
        print "<a  href=\"javascript:void(0);\" onmouseover=\"return overlib('$overtext');\" onmouseout=\"return nd();\">$heading</a>" ;
}
function overlib2($overtext,$heading = "") {
        return "<a  href=\"javascript:void(0);\" onmouseover=\"return overlib('$overtext');\" onmouseout=\"return nd();\">$heading</a>"
;
}
function setup_pdo_statsdb($db) {
	try {
		if ($db['type'] == "mysql")
		{
			$db_string = "mysql:dbname=".$db['name'].";host=".$db['host'];
			$dbh = new PDO($db_string, $db['user'], $db['pass']);
		}
		elseif ($db['type'] == "sqlite3")
		{ 
			$db_string = "sqlite:".$db['path'];
	                $dbh = new PDO($db_string);
		}
	}
	catch(PDOException $e)
	{
	        echo $e->getMessage();
	}
	return $dbh;
}
function build_pager ($page, $query, $rows_per_page) {
	// current_page query link enable filtering display
	$rows = $query->fetchColumn();
	$pages = ( ceil($rows / $rows_per_page) );
	print "<div style=\"float: right \" class=\"pagebar\">";
	if ( ! isset($page) or $page < "1" or $page > $pages ) { $page = 1; }
	if ( $page > "1" ) {
	        $nextpage = ($page - 1);
	        print "\n<a href=\"?page=$nextpage\" >&#171; Prev</a>\n";
	}

	if ($pages <= 10) {
		for ( $counter = 1; $counter <= $pages; $counter++) {
	            if ($counter == $page) { $class = " class=\"selected\""; } else { $class = ""; }
			print "<a href=\"?page=$counter&orderby=${_SESSION['orderby']}\"$class>$counter</a>";
		}
	} else {
		for ( $counter = 1; $counter <= $pages; $counter++) {
			if (($counter == 1) or (($counter >= $page-5) and ($counter <= $page)) or (($counter < $page+5) and ($counter > $page)) or ($counter == $pages/2) or ($counter == $page) or ($counter == $pages)) {
				if ($counter == $page) { $class = " class=\"selected\""; } else { $class = ""; }
				print "<a href=\"?page=$counter&orderby=${_SESSION['orderby']}\"$class>$counter</a>";
			}
		}
	}
	if ($page < $pages) {
		$nextpage = ($page + 1);
		print "\n<a href=\"?page=$nextpage&orderby=${_SESSION['orderby']}\" >Next &#187;</a>\n";
	}

	print overlib("Filtering in affect<br />Filter MinimumGames <font color=white>".$_SESSION['MinimumGames']."</font>Filter NoFrags","$rows results");
	print "</div>";
}
function check_get ($pagename) {
	global $rows_per_page;
	if ( isset($_GET['querydate']) ) switch ($_GET['querydate']) {
	        case "day":
	                $_SESSION['querydate'] = "day";
	                $_SESSION['MinimumGames'] = "1";
	        break;
	        case "week":
	                $_SESSION['querydate'] = "week";
	                $_SESSION['MinimumGames'] = "1";
	        break;
	        case "month":
	                $_SESSION['querydate'] = "month";
	                $_SESSION['MinimumGames']  = "1";
	        break;
	        case "year":
	                $_SESSION['querydate'] = "year";
	                $_SESSION['MinimumGames'] = "1";
	        break;
                case "nolimit":
                        $_SESSION['querydate'] = "nolimit";
                        $_SESSION['MinimumGames'] = "1";
                break;
	}
        if ( ! isset($_SESSION['querydate']) ) { $_SESSION['querydate'] = "nolimit"; }
	if ( ! isset($_SESSION['MinimumGames']) ) { $_SESSION['MinimumGames'] = 1; }

	if ( isset($_GET['page']) and $_GET['page'] >= 2 ) {
	        $_SESSION['paging'] = ( ($_GET['page'] * $rows_per_page) - $rows_per_page +1 );
	} else {
                $_GET['page'] = 1;
                $_SESSION['paging'] = 1;
        }

	if ( isset($_GET['orderby']) ) {
        // Input Validation
        $_GET['orderby'] = preg_replace("/[[:^alpha:]]/", "", $_GET['orderby']);
        if (($pagename == "scoreboard") or ($pagename == "Daily Activity") or ($pagename == "game details")) {
            if (preg_match("/(Kpd|Accuracy|TotalGames|name|country|TotalScored|MostFrags|TotalFrags|TotalDeaths|TotalTeamkills)/i", $_GET['orderby']) ) {
                $_SESSION['orderby'] = $_GET['orderby'];
            }
        } elseif ($pagename == "player details") {
            if (preg_match("/(id|servername|datetime|duration|mapname|gamemode|players)/i", $_GET['orderby']) ) {
                $_SESSION['orderby'] = $_GET['orderby'];
            }
        }
	} elseif ( isset($_SESSION['orderby']) ) {
        // Input Validation
        $_SESSION['orderby'] = preg_replace("/[[:^alpha:]]/", "", $_SESSION['orderby']);
        if (($pagename == "scoreboard") or ($pagename == "Daily Activity") or ($pagename == "game details")) {
            if (preg_match("/(Kpd|Accuracy|TotalGames|name|country|TotalScored|MostFrags|TotalFrags|TotalDeaths|TotalTeamkills)/i", $_SESSION['orderby']) ) {
                 $_SESSION['orderby'] = $_SESSION['orderby'];
            } else {
                $_SESSION['orderby'] = "TotalScored";
            }
        } elseif ($pagename == "player details") {
            if (preg_match("/(id|servername|datetime|duration|mapname|gamemode|players)/i", $_GET['orderby']) ) {
                $_SESSION['orderby'] = $_SESSION['orderby'];
            } else
                 $_SESSION['orderby'] = "datetime";
            }
	} else {
        if (($pagename == "scoreboard") or ($pagename == "Daily Activity") or ($pagename == "game details")) {
                    $_SESSION['orderby'] = "TotalScored";
        } elseif ($pagename == "player details") {
                    $_SESSION['orderby'] = "datetime";
        }
    }
	if ( isset($_GET['name']) ) { $_SESSION['name'] = $_GET['name']; }
}

function stats_table ($result, $exclude_columns = "NULL") {
    global $desc_stats_table;
    global $dbh;
    global $column_list; 
    global $rows_per_page;

?>
<table cellpadding="0" cellspacing="0" id="hopstats" class="tablesorter">
        <thead>
        <tr>
<?php
    foreach (column_wrapper($desc_stats_table, $exclude_columns) as $column) { print "<th>";overlib($column['description'], $column['name']); print "</th>"; }
    print "</tr></thead><tbody>";
    $pair = 1;
    foreach ($result as $row) {
//        $pair ++;
		if (($pair >= $_SESSION['paging']) && ($pair <= ($_SESSION['paging'] + $rows_per_page - 1)))
		{
			if ($pair % 2 == 1) {
				$parity = "unpair";
			} else {
				$parity = "pair";
			}
			$country = (strtolower($row["PlayerCountry"]) != "" ? strtolower($row["PlayerCountry"]) : "unknown");
			$flag_image = "<img src=\"images/flags/" . $country . ".png\" alt=\"$country\" />";
			?>
			<tr class="<?= $parity ?>" onmouseover="this.className = 'highlight'" onmouseout="this.className = '<?= $parity ?>'">
				<td><a href="player.php?name=<?= $row["name"] ?>"><?= htmlspecialchars($row["name"]) ?></a></td>
				<td><?= overlib($row["PlayerCountry"], $flag_image) ?></td>
				<?php
				foreach (column_wrapper($desc_stats_table, "Name|Country|$exclude_columns") as $column) {
				  print "<td>" . $row[$column['column']] . "</td>";
				}
				?>
			  </tr>
			  <?php
			  $flag_image = "";
		} elseif ($pair > ($_SESSION['paging'] + $rows_per_page))
			break;
	$pair++;
    }
    print "</tbody></table>";
}

function match_table ($game) {
    global $dbh;
    $sql3 = $dbh->prepare("
        select 
            servername,
            datetime,
            duration,
            mapname,
            gamemode,
            players
        from games 
        where id = :game");
    $sql3->execute(array(':game' => $game));
    $row = $sql3->fetch(PDO::FETCH_OBJ);
?>

<div align="left" id="content"><h1>Game details</h1>

<table cellpadding="0" cellspacing="1">
<img style="float:right; margin-right:25%; border:0.5em ridge blue" src="images/maps/<?php print $row->mapname; ?>.jpg" />
<tr>
        <td class="headcol">Server</td>
        <td><?php print $row->servername ?></td>
</tr>
<tr>
        <td style="width:100px;" class="headcol">Date/Time</td>
        <td><?php print $row->datetime ?></td>
</tr>
<tr>
        <td class="headcol">Duration</td>
        <td><?php print $row->duration ?></td>
</tr>
<tr>
        <td class="headcol">Map</td>
        <td><?php print $row->mapname ?></td>
</tr>
<tr>
        <td class="headcol">Mode</td>
        <td><?php print $row->gamemode ?></td>
</tr>
<tr>
        <td class="headcol">Players</td>
        <td><?php print $row->players ?></td>
</tr>
</table>
</div>
<?php
}

function match_player_table ($result ,$exclude_columns = "NULL"){
    global $desc_match_table;
    global $column_list; 

?>
<table cellpadding="0" cellspacing="0" id="matchstats" class="tablesorter">
        <thead>
        <tr>
<?php
	foreach (column_wrapper($desc_match_table, $exclude_columns) as $column) { print "<th>";overlib($column['description'], $column['name']); print "</th>"; }
	print "</tr></thead><tbody>";
    $pair = 0;
	foreach ($result as $row)
	{
                    $pair++;
                    if ($pair%2 == 1) { $parity = "unpair"; } else { $parity = "pair"; }
	                print "
	                        <tr class=\"$parity\" onmouseover=\"this.className='highlight'\" onmouseout=\"this.className='$parity'\">
					<td><a href=\"match.php?id=$row[id]\">$row[id]</a></td>
	                                ";

					foreach (column_wrapper($desc_match_table, "Game ID") as $column) {
						print "<td>".$row[$column['column']]."</td>";
					}
	                print "
	                        </tr>";
	        $flag_image ="";
	}
// Close db handle
print "</tbody></table>";
}

// Start page benchmark
startbench();

// Start session for session vars
session_start();

// Check for any http GET activity
check_get($pagename);

// Setup statsdb and assign it to an object.
$dbh = setup_pdo_statsdb($db);

// Print headers
print <<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/css" href="/css/style.css"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <title>
EOH;
print(strip_color_codes($server_title)."'s ".$pagename."</title>");
print <<<EOH

        <script type="text/javascript" src="js/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
        <script type="text/javascript" src="js/jquery-latest.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="js/jquery.uitablefilter.js"></script>
        <script type="text/javascript" src="js/hopstats.js"></script>
        <link rel="stylesheet" type="text/css" href="css/style.css" />
    </head>
    <body>
        <div id="header">
            <span style="float:left;margin-right:5em"><a href="./"><img src="images/suckerserv.png" alt="SuckerServ" /></a></span>
            <ul id="sddm">
EOH;

if (($pagename == "scoreboard") or ($pagename == "Daily Activity") or ($pagename == "game details")) {
print <<<EOH
                <li>
                    <a href="#" onmouseover="mopen('m1')"  onmouseout="mclosetime()">Ordered by <span style="color:blue">${stats_column_to_name[$_SESSION['orderby']]}</span></a>
                    <div id="m1" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                    <a style="border:none" href="?orderby=name">Name</a>
                    <a href="?orderby=country">Country</a>
                    <a href="?orderby=TotalScored">Score</a>
                    <a href="?orderby=TotalFrags">Frags</a>
                    <a href="?orderby=TotalDeaths">Deaths</a>
                    <a href="?orderby=MostFrags">Max Frags</a>
                    <a href="?orderby=Accuracy">Accuracy</a>
                    <a href="?orderby=Kpd">Kpd</a>
                    <a href="?orderby=TotalTeamkills">Teamkills</a>
                    <a href="?orderby=TotalGames">Games</a>
                    </div>
                </li>
EOH;
} elseif ($pagename == "player details") {
$name = urlencode($_SESSION['name']);
print <<<EOH
                <li>
                    <a href="#" onmouseover="mopen('m1')"  onmouseout="mclosetime()">Ordered by <span style="color:white">${match_column_to_name[$_SESSION['orderby']]}</span></a>
                    <div id="m1" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                    <a href="?orderby=id&name=${name}&page=${_GET['page']}">Game ID</a>
                    <a href="?orderby=servername&name=${name}&page=${_GET['page']}">Server</a>
                    <a href="?orderby=datetime&name=${name}&page=${_GET['page']}">Date/Time</a>
                    <a href="?orderby=duration&name=${name}&page=${_GET['page']}">Duration</a>
                    <a href="?orderby=mapname&name=${name}&page=${_GET['page']}">Map</a>
                    <a href="?orderby=gamemode&name=${name}&page=${_GET['page']}">Mode</a>
                    <a href="?orderby=players&name=${name}&page=${_GET['page']}">Players</a>
                    </div>
                </li>
EOH;
}
print <<<EOH
            </ul>

            <noscript><div class="error">This page uses JavaScript for table column sorting and producing an enhanced tooltip display.</div></noscript>
            <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000"></div>
        </div>
EOH;

?>
