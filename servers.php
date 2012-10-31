<?php
$pagename = "Server list";
include("includes/geoip.inc");
include("includes/hopmod.php");
?>

        <h1>Server list</h1>

        <div id="filter-panel">
            <span class="filter-form" style="margin-right:0.5em">
				<a style="border:0.2em solid; padding:0.5em;margin:0 -0.9em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="index.php">Scoreboard</a>
            </span>
			<span class="filter-form" style="margin-right:0.5em">
				<a style="border:0.2em solid; padding:0.5em;margin:0 -0.7em 0 -0.7em;#555555;color:blue; font-weight:bold;font-size:1.1em" href="activity.php">Daily activity</a>
            </span>
		</div>
<div style="clear:both"> </div>
<?php 
foreach($servers as $server=>$settings) 
{
	$info = get_info($settings['host'], $settings['port']);
	$info['server'] = colorname($info['server']);
print <<<EOH
<div style="float:left;margin:1em 1em" >
<table class="navbar" cellpadding="0" cellspacing="1">
				<tr>
					<td style="width:100px;" class="headcol">Name</td>
					<td align="center">$info[server]</td>
				</tr>
				<tr>
					<td style="width:100px;" class="headcol">Host</td>
					<td align="center">$settings[host]</td>
				</tr>
				<tr>
					<td style="width:100px;" class="headcol">Port</td>
					<td align="center">$settings[port]</td>
				<tr>
					<td style="width:100px;" class="headcol">Map</td>
					<td align="center">$info[map]</td>
				</tr>
				<tr>
					<td style="width:100px;" class="headcol">Mode</td>
					<td align="center">$info[mode_int] ($info[mode])</td>
				</tr>
				<tr>
					<td style="width:100px;" class="headcol">Players</td>
					<td align="center">$info[players]</td>
				</tr>
				<tr>
					<td style="width:100px;" class="headcol">Slots</td>
					<td align="center">$info[slots]</td>
				</tr>
				<tr>
					<td style="width:100px;" class="headcol">Protocol</td>
					<td align="center">$info[protocol] ($info[version])</td>
				</tr>
				<tr>
					<td style="width:100px;" class="headcol">Time</td>
					<td align="center">$info[time]min ($info[time_seconds]sec)</td>
				</tr>
				<tr>
					<td style="width:100px;" class="headcol">Mastermode</td>
					<td align="center">$info[mastermode_int] ($info[mastermode])</td>
				</tr>
				</table>
</div>
EOH;
}
?>
        <?php stopbench(); //Stop and display benchmark.?>
    </body>
</html>
