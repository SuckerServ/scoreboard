<?php
$pagename = "scoreboard";
include("includes/geoip.inc");
include("includes/hopmod.php");

if (! isset($_SESSION['querydate']) ) { $_SESSION['querydate'] = "month";}
if (! isset($_GET['page']) ) { $_GET['page'] = 0;}
// Setup statsdb and assign it to an object.
$dbh = setup_pdo_statsdb($db);

$dat = explode(",",date('n,Y')); 
$year = date('Y');
$week = date('W');

$day_end = strtotime("today +1 day");
$week_end = strtotime("{$year}-W{$week}-7");
$month_end = mktime(23,59,59,$dat[0]+1,0,$dat[1]);
$year_end = strtotime("today");
$nolimit_end = strtotime("today +1 day");

$day_start = strtotime("today");
$week_start = strtotime("{$year}-W{$week}-1");
$month_start = mktime(0,0,0,$dat[0],1,$dat[1]);;
$year_start = strtotime("today -365 days");
$nolimit_start = "0";

$_SESSION['start_date'] = ${$_SESSION['querydate']."_start"};
$_SESSION['end_date'] = ${$_SESSION['querydate']."_end"};

$day = "";

$sql = $dbh->prepare("
        select *
        from
            (select name,
                ipaddr as country,
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
            where
                UNIX_TIMESTAMP(games.datetime) between :start_date and :end_date and frags > 0
            group by name
            order by ".$_SESSION['orderby']." desc) T
        where TotalGames >= :MinimumGames limit ".$_SESSION['paging'].",".$rows_per_page);

$pager_query = $dbh->prepare("
        select COUNT(*)
        from
                (select name,
                        frags,
                        count(name) as TotalGames
                from players
                        inner join games on players.game_id=games.id
                where UNIX_TIMESTAMP(games.datetime) between :start_date and :end_date and frags > 0 group by name) T
        where TotalGames >= :MinimumGames");
?>

        <h1><?php print(colorname("$server_title ")); ?> Scoreboard</h1>

        <div id="filter-panel">
            <span class="filter-form" style="margin-right:0.5em">
            <a style="border:0.2em solid; padding:0.5em;margin:0 -0.9em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="activity.php">Daily activity</a>
            </span>
            <span class="filter-form">
            Limit to this [ <a href="?querydate=day" <?php if ( $_SESSION['querydate'] == "day" ) { print "class=\"selected\" style=\"color:black\""; } ?>>DAY</a> | 
            <a href="?querydate=week" <?php if ( $_SESSION['querydate'] == "week" ) { print "class=\"selected\" style=\"color:black\""; } ?>>WEEK</a> | 
            <a href="?querydate=month" <?php if ( $_SESSION['querydate'] == "month" ) { print "class=\"selected\" style=\"color:black\""; } ?> >MONTH</a> | 
            <a href="?querydate=year" <?php if ( $_SESSION['querydate'] == "year" ) { print "class=\"selected\" style=\"color:black\""; } ?>>YEAR</a> |
            <a href="?querydate=nolimit" <?php if ( $_SESSION['querydate'] == "nolimit" ) { print "class=\"selected\" style=\"color:black\""; } ?>>NO LIMIT</a> ]</span>
            <span class="filter-form"><form id="filter-form">Name Filter: <input name="filter" id="filter" value="" maxlength="30" size="30" type="text"></form></span>
			<span class="filter-form"><a style="border:0.2em solid; padding:0.5em;margin:0 -0.6em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="servers.php">Server list</a></span>
        </div>
                <?php $pager_query->execute(array(':start_date' => $_SESSION['start_date'], ':end_date' => $_SESSION['end_date'],':MinimumGames' => $_SESSION['MinimumGames']));
                      build_pager($_GET['page'],$pager_query,$rows_per_page); //Generate Pager Bar ?>
        <?php $sql->execute(array(':start_date' => $_SESSION['start_date'], ':end_date' => $_SESSION['end_date'],':MinimumGames' => $_SESSION['MinimumGames']));
              stats_table($sql); //Build stats table data ?> 
        <?php stopbench(); //Stop and display benchmark.?>
    </body>
</html>
