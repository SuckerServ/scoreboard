<?php
///////////////////////Player Details Page
$pagename = "player details";
include("includes/hopmod.php");

function no_name() {
?>
<h1>Please provide a correct player name</h1>
<?php stopbench(); ?>
</body>
</html>
<?php
exit;
}

if (isset($_GET['name']) and $_GET['name'] != "") {
    $_SESSION['name'] = $_GET['name'];
} elseif (isset($_SESSION['name']) and $_SESSION['name'] != "") {
} else { no_name(); }

// Setup main sqlite query.
$sql = $dbh->prepare("select name,
                country as PlayerCountry,
                ipaddr as PlayerIP,
                sum(score) as TotalScored,
                sum(teamkills) as TotalTeamkills,
		sum(suicides) as TotalSuicides,
                max(frags) as MostFrags,
                sum(frags) as TotalFrags,
                sum(deaths) as TotalDeaths,
                count(name) as TotalGames,
                round((0.0+sum(hits))/(sum(hits)+sum(misses))*100) as Accuracy,
                round((0.0+sum(frags))/sum(deaths),2) as Kpd
        from players
                inner join games on players.game_id=games.id
        where name = :name group by name");

$last_10 = $dbh->prepare("
select games.id as id,datetime,gamemode,mapname,duration,players,servername
        from games
                inner join players on players.game_id=games.id

        where name = :name order by ".$_SESSION['orderby']." desc limit ".$_SESSION['paging'].",".$rows_per_page);

$pager_query = $dbh->prepare("
select count(*) from 
(select games.id as id,datetime,gamemode,mapname,duration,players
        from games
                inner join players on players.game_id=games.id

        where name = :name) T");
?>

<h1><?php print htmlentities($_SESSION['name']) ?>'s profile</h1>

<div style="clear:both;float:left" class="box" style="position:absolute">
<table class="navbar" cellpadding="0" cellspacing="1">
<?php
//Build table data
$sql->execute(array(':name' => $_SESSION['name']));
foreach ($sql->fetchAll() as $row) {
    $country = (strtolower($row["PlayerCountry"]) != "" ? strtolower($row["PlayerCountry"]) : "unknown");
    $flag_image = "<img src=\"images/flags/" . $country . ".png\" alt=\"$country\" />";
    ?>
    <tr>
        <td style="width:100px;" class="headcol">Name</td>
        <td align="center"><?= $row["name"] ?></td>
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">Country</td>
        <td align="center"><?php overlib($row["PlayerCountry"], $flag_image) ?></a></td>
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">Most Frags</td>
        <td align="center"><?= $row["MostFrags"] ?></td>
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">Total Frags</td>
        <td align="center"><?= $row["TotalFrags"] ?></td>
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">Total Deaths</td>
        <td align="center"><?= $row["TotalDeaths"] ?></td>
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">Accuracy</td>
        <td align="center"><?= $row["Accuracy"] ?></td>	
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">KpD</td>
        <td align="center"><?= $row["Kpd"] ?></td>
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">Team Kills</td>
        <td align="center"><?= $row["TotalTeamkills"] ?></td>	
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">Suicides</td>
        <td align="center"><?= $row["TotalSuicides"] ?></td>   
    </tr>
    <tr>
        <td style="width:100px;" class="headcol">Total Games</td>
        <td align="center"><?= $row["TotalGames"] ?></td>
    </tr>
<?php
}
?>
</table>
</div>

<div style="margin-left:300px">

<a name="gm"></a>

<h2>Game history</h2>

<?php 
$last_10->execute(array(':name' => $_SESSION['name']));
match_player_table($last_10->fetchAll()); //Build game table data ?>
<?php $pager_query->execute(array(':name' => $_SESSION['name']));
build_pager($_GET['page'],$pager_query,$rows_per_page); //Generate Pager Bar ?>
<?php stopbench(); ?>
</body>
</html>
