<?php
$pagename = "Daily Activity";
include("includes/geoip.inc");
include("includes/hopmod.php");

if (! isset($_SESSION['days'])) { $_SESSION['days'] = 0;}
if ($_GET['select_day'] == "next") { $_SESSION['days'] = ($_SESSION['days'] + 1);header("location: activity.php");}
if ($_GET['select_day'] == "previous") { $_SESSION['days'] = ($_SESSION['days'] - 1);header("location: activity.php");}

$start_date = strtotime(($_SESSION['days']-1)." days");
$start_date = date("d F Y", $start_date);
$start_date = strtotime("$start_date");
$end_date = strtotime("+23 hours 59 minutes 59 seconds", $start_date); 

$day_games = $dbh->prepare("
        select games.id as id,datetime,gamemode,mapname,duration,players,servername
            from games
            where UNIX_TIMESTAMP(games.datetime) between :start_date and :end_date and mapname != '' and gamemode != ''  and players > '1' order by datetime desc 
            limit ".$_SESSION['paging'].",$rows_per_page");

$sql = $dbh->prepare("
        select *
        from
            (select name,
                    country as PlayerCountry,
                    ipaddr as PlayerIP,
                    sum(score) as TotalScored,
                    sum(teamkills) as TotalTeamkills,
                    max(frags) as MostFrags,
                    sum(frags) as TotalFrags,
                    sum(deaths) as TotalDeaths,
                    count(name) as TotalGames,
                    round((0.0+sum(hits))/(sum(hits)+sum(misses))*100) as Accuracy,
                    round((0.0+sum(frags))/sum(deaths),2) as Kpd
            from players
                    inner join games on players.game_id=games.id
            where UNIX_TIMESTAMP(games.datetime) between :start_date and :end_date and mapname != '' group by name order by ".$_SESSION['orderby']." desc) T
            limit ".$_SESSION['paging'].",$rows_per_page");

$players_pager_query = $dbh->prepare("
        select count(*)
        from
            (select name
            from players
                    inner join games on players.game_id=games.id
            where UNIX_TIMESTAMP(games.datetime) between :start_date and :end_date and games.mapname != '' group by name) T");

$games_pager_query = $dbh->prepare("
        select count(*)
            from games
            where UNIX_TIMESTAMP(games.datetime) between :start_date and :end_date and mapname != '' and gamemode != ''  order by datetime desc");

$count_day_games = count_rows("
        select count(*)
            from games
            where UNIX_TIMESTAMP(games.datetime) between '$start_date' and '$end_date' and mapname != '' and gamemode != ''  order by datetime desc");

$player_count = count_rows("
        select count(*)
        from
            (select name
            from players
                    inner join games on players.game_id=games.id
            where UNIX_TIMESTAMP(games.datetime) between $start_date and $end_date and mapname != '' group by name ) T");

?>
        <div id="container">
            <h1><?php print(colorname($server_title)); ?> 's daily activity for <span style="font-style:italic; font-size:1.1em; color:#000077"><?php print date(" jS M Y",$start_date); ?></span></h1>
            <div id="filter-panel">
                <span class="filter-form"><a style="border:0.2em solid; padding:0.5em;margin:0 -0.7em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="./">Scoreboard</a></span>
                <span class="filter-form"><form id="filter-form">Name Filter: <input name="filter" id="filter" value="" maxlength="30" size="30" type="text"></form></span>
							<span class="filter-form"><a style="border:0.2em solid; padding:0.5em;margin:0 -0.6em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="servers.php">Server list</a></span>
            </div>
            <div class="pagebar">
                <a href="activity.php?select_day=previous">&#171; Previous day</a>
                <a href="activity.php?select_day=next">Next day &#187;</a>
            </div>

<table id="organizer">
<tr><td>            <div id="leftColumn">
                <?php $games_pager_query->execute(array(':start_date' => $start_date, ':end_date' => $end_date));
                      build_pager($_GET['page'],$games_pager_query,$rows_per_page); //Generate Pager Bar ?>
<br />
                <?php $day_games->execute(array(':start_date' => $start_date, ':end_date' => $end_date));
                      match_player_table($day_games); //Build game table data ?>
            </div></td>
<td>            <div id="rightColumn">
                <?php $players_pager_query->execute(array(':start_date' => $start_date, ':end_date' => $end_date));
                      build_pager($_GET['page'],$players_pager_query,$rows_per_page); //Generate Pager Bar ?>
<br />
                <?php $sql->execute(array(':start_date' => $start_date, ':end_date' => $end_date));
                      stats_table($sql); //Build game table data ?>
            </div></tr>
</table>
        </div>
        <?php stopbench(); //Stop and display benchmark.?>
    </body>
</html>
