<?php
$pagename = "game details";
include("includes/hopmod.php");

function no_id() {
?>
<h1>Please provide a correct game ID</h1>
<?php stopbench(); ?>
</body>
</html>
<?php
exit;
}
$_GET['id'] = intval($_GET['id']);
if (isset($_GET['id']) and $_GET['id'] != "") {
    $_SESSION['id'] = $_GET['id'];
} elseif (isset($_SESSION['id']) and $_SESSION['id'] != "") {
} else { no_id(); }

$sql = $dbh->prepare("
        select name,
            country AS PlayerCountry,
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
        where game_id = :id group by name order by ".$_SESSION['orderby']." desc");

  $pager_query = $dbh->prepare("SELECT FOUND_ROWS()")


?>
<?php match_table($_SESSION['id']); //Build stats table data ?>
<div style="clear:both">
<h2 style="margin-left:2em">Players</h2>
<?php 
	$sql->execute(array(':id' => $_SESSION['id']));
	$pager_query->execute();
	build_pager($_GET['page'], $pager_query, $rows_per_page); //Generate Pager Bar 
?>
</div>
<?php
	stats_table($sql); //Build stats table data 
?>
<?php stopbench(); //Stop and display benchmark.?>
</body>
</html>
