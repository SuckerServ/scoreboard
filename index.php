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

$day_start = strtotime("today");
$week_start = strtotime("{$year}-W{$week}-1");
$month_start = mktime(0,0,0,$dat[0],1,$dat[1]);;
$year_start = strtotime("today -365 days");


$_SESSION['start_date'] = ${$_SESSION['querydate']."_start"};
$_SESSION['end_date'] = ${$_SESSION['querydate']."_end"};

$day = "";
$pager_query = "
        select COUNT(*)
        from
                (select name,
                        frags,
                        count(name) as TotalGames
                from players
                        inner join games on players.game_id=games.id
                where UNIX_TIMESTAMP(games.datetime) between ".$_SESSION['start_date']." and ".$_SESSION['end_date']."  and frags > 0 group by name) T
        where TotalGames >= ". $_SESSION['MinimumGames']."
";
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
            <a href="?querydate=year" <?php if ( $_SESSION['querydate'] == "year" ) { print "class=\"selected\" style=\"color:black\""; } ?>>YEAR</a> ]</span>
            <span class="filter-form"><form id="filter-form">Name Filter: <input name="filter" id="filter" value="" maxlength="30" size="30" type="text"></form></span>
			<span class="filter-form"><a style="border:0.2em solid; padding:0.5em;margin:0 -0.6em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="servers.php">Server list</a></span>

            <div style="float: right " id="pagebar">
                <?php build_pager($_GET['page'],$pager_query); //Generate Pager Bar ?>
            </div>
        </div>
        <?php stats_table(); //Build stats table data ?> 
        <?php stopbench(); //Stop and display benchmark.?>
    </body>
</html>
