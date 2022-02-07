<?php
include_once("settings.php");
require "inc/sharedfunc.php";
session_start();

$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_errno)
{
	die($conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array('drops_index');

// pre-cache templates used by specific actions
$actiontemplates = array();

// ######################### REQUIRE BACK-END ############################
// if your page is outside of your normal vb forums directory, you should change directories by uncommenting the next line
chdir ('../');

ob_start();
if($_POST['username'] == 'userhere' && $_POST['password'] == 'passwordhere') $_SESSION['logged_in'] = true;

if($_SESSION['logged_in'] && $_GET['ban'] == 1)
{
	$res = $conn->query("select * from sprays where filename='".$conn->real_escape_string($_GET['filename'])."' limit 1") or die($conn->error);
	$row = mysqli_fetch_assoc($res);
	$conn->query("update sprays set banned=1 where filename='{$row['filename']}' limit 1") or die($conn->error);
}

if($_SESSION['logged_in'] && $_GET['unban'] == 1)
{
	$res = $conn->query("select * from sprays where filename='".$conn->real_escape_string($_GET['filename'])."' limit 1") or die($conn->error);
	$row = mysqli_fetch_assoc($res);
	$conn->query("update sprays set banned=0 where filename='{$row['filename']}' limit 1") or die($conn->error);
}

$order = $_GET['o'];
$ordersql = 'firstdate DESC, count DESC, date DESC';
if($order == 'count'){
$ordersql = 'count DESC, firstdate DESC, date DESC';
}
if($order == 'date'){
$ordersql = 'date DESC, count DESC, firstdate DESC';
}

$result = $conn->query("SELECT *, UNIX_TIMESTAMP(firstdate) AS t_firstdate, UNIX_TIMESTAMP(date) AS t_date FROM sprays WHERE datediff(NOW(), firstdate) < ".DELETEDAYS." OR datediff(NOW(), date) <".DELETEDAYS." ORDER BY {$ordersql} LIMIT 3000") or die($conn->error);

$servname = gethostbyaddr(gethostbyname($_SERVER["SERVER_NAME"])); 

// format the uptime in case the browser doesn't support dhtml/javascript
// static uptime string
function format_uptime($seconds) {
  $secs = intval($seconds % 60);
  $mins = intval($seconds / 60 % 60);
  $hours = intval($seconds / 3600 % 24);
  $days = intval($seconds / 86400);
  
  if ($days > 0) {
    $uptimeString = $days;
    $uptimeString .= (($days == 1) ? " day" : " days");
  }
  if ($hours > 0) {
    $uptimeString .= (($days > 0) ? ", " : "") . $hours;
    $uptimeString .= (($hours == 1) ? " hour" : " hours");
  }
  if ($mins > 0) {
    $uptimeString .= (($days > 0 || $hours > 0) ? ", " : "") . $mins;
    $uptimeString .= (($mins == 1) ? " minute" : " minutes");
  }
  if ($secs > 0) {
    $uptimeString .= (($days > 0 || $hours > 0 || $mins > 0) ? ", " : "") . $secs;
    $uptimeString .= (($secs == 1) ? " second" : " seconds");
  }
  return $uptimeString;
}

// read in the uptime (using exec)
$uptime = exec("cat /proc/uptime");
$uptime = explode(" ", $uptime);
$uptimeSecs = $uptime[0];

// get the static uptime
$staticUptime = "Server Uptime: ".format_uptime($uptimeSecs);

	function get_server_load($windows = 0)
	{
		$os = strtolower(PHP_OS);

		if (strpos($os, "win") === false)
		{
			if (file_exists("/proc/loadavg"))
			{
				$load = file_get_contents("/proc/loadavg");
				$load = explode(' ', $load);
				return $load[0];
			}
			else if (function_exists("shell_exec"))
			{
				$load = explode(' ', shell_exec("uptime"));
				return $load[count($load)-1];
			}
			else
			{
				return "";
			}
		}
		else if ($windows)
		{
			if (class_exists("COM"))
			{
				$wmi = new COM("WinMgmts:\\\\.");
				$cpus = $wmi->InstancesOf("Win32_Processor");
				$cpuload = 0;
				$i = 0;
				while ($cpu = $cpus->Next())
				{
					$cpuload += $cpu->LoadPercentage;
					$i++;
				}
				$cpuload = round($cpuload / $i, 2);
				return $cpuload;
			}
			else
			{
				return "";
			}
		}
	}
	
$decimalval = get_server_load(true);
$percentage = $decimalval * 100 / 2; 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

	<head>
		<title>Facepunch TF2 - Sprays</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" type="text/css" href="css/style.css">
	</head>
	
<body>

<div align='center'>
	<div class='header'>
		<div id='pages'>
			<div id='spraypagination'></div>
		<div id='notice'>
		<?php
			echo "Displaying <b>".$result->num_rows."</b> sprays from the last ". DELETEDAYS ." days.<br /><b>Warning: NSFW</b><br />";
			if($order == 'count'){
				echo "<a href='/sprays/'>Order by upload date</a> | <a href='/sprays/?o=date'>Order by most recently sprayed</a>";    
			}	
			elseif($order == 'date'){
				echo "<a href='/sprays/'>Order by upload date</a> | <a href='/sprays/?o=count'>Order by amount of times sprayed</a>";    
			} else {
				echo "<a href='/sprays/?o=count'>Order by amount of times sprayed</a> | <a href='/sprays/?o=date'>Order by most recently sprayed</a>";
			}
			if($result->num_rows < 1) echo "<p><i>No sprays found</i></p>";
		?>
		</div>
		</div>
		<div id='login'>
			<form action="" method="post">
				Username: <input type="text" name="username" size="20"><br>
				<div style="height:5px;"></div>
				Password: <input type="password" name="password" size="20">
				<div style="height:5px;"></div>
				<input type="submit" value="Login">
			</form>
		</div>
	</div>
<br>
<div id='sprays'><div class='spraypage'>
The sprays page shows all current and used sprays by players on the Facepunch Team Fortress 2 server.<br>
<?php

$servers = array(
'123.321.111.222:27040' => 'Facepunch TF2UK', 
'123.321.111.222:27015' => 'Facepunch TF2UNSRS'
);

$pages = 1;
$onpage = 0;

foreach($result as $row)
{	
	if((!file_exists("sprays/img/{$row['filename']}.gif") || filesize("sprays/img/{$row['filename']}.gif") < 1024) && (!file_exists("sprays/img/{$row['filename']}.png") || filesize("sprays/img/{$row['filename']}.png") < 1024)) continue;
	
	$onpage++;
	if($onpage > 24)
	{
		$pages++;
		$onpage = 1;
		echo "</div><div class='spraypage'>";
	}
	
	$date = date('d/m/Y H:i:s', $row['t_date']);
	if($row['t_date'] == 0)
	{
		$date = "Never";
	}
		
	$firstdate = date('d/m/Y H:i:s', $row['t_firstdate']);
		
	$port = intval($row['port']);
	
	if(array_key_exists("{$row['ip']}:{$port}", $servers))
	{
		$server = $servers["{$row['ip']}:{$port}"];
	}
	else
	{
		$server = "Unknown Server";
	}

	$f = steam2friend($row['steamid']);
	$link = "<a href='http://steamcommunity.com/profiles/{$f}'>{$row['name']}</a>";
	
	if($row['banned'] == 0)
	{
		if(file_exists("sprays/img/{$row['filename']}.png"))
		{
			$av = "/sprays/img/{$row['filename']}.png";
		}
		else
		{
			$av = "/sprays/img/{$row['filename']}.gif";
		}
	}
	else
	{
		$av = "/sprays/badspray.png";
	}
		
	$count = $row['count']." time".($count == 1 ? "" : "s");
	
	$manager = "";
	if($row['banned'] == 1)
	{
		$manager = "<br><strong>Admin blocked spray view</strong>";
	}
	
	?>
	<div class='spray'>
		<img <?= ($pages == 1 ? "src" : "srb") ?>='<?= $av ?>' alt='' width='256' height='256' title='<strong>Uploaded:</strong> <?= $firstdate ?><br><strong>Last sprayed:</strong> <?= $date ?><br><strong>Last server: </strong><?= $server ?><br><strong>Sprayed:</strong> <?= $count ?><?= $manager ?>'>
		<div>
		<?= $link ?> <?php if($_SESSION['logged_in'] && $row['banned'] == 1){ ?> &mdash; <a href='?unban=1&filename=<?= $row['filename'] ?>' alt="\" title='Manager-only option - Unblocks converted spray and Unblocks the spray from being used in the future' style='font-size: 11px'>Unblock</a> &mdash; <a href="<?= $av ?>" alt="\" title='Manager-only option - Redirects to blocked spray' style='font-size: 11px'>Click to show.</a><?php } ?>
		<?php if($_SESSION['logged_in'] && $row['banned'] == 0){ ?> &mdash; <a href='?ban=1&filename=<?= $row['filename'] ?>' title='Blocks the spray from being used in the future' style='font-size: 11px'>Block</a><?php } ?>
		</div>
	</div>		
	<?php
}
?>
</div>
</div>

<script type='text/javascript' src='js/jquery.js'></script>
<script type='text/javascript' src='js/jquery.paginate.js'></script>
<script type='text/javascript' src='js/jquery.tooltip.js'></script>
<script type='text/javascript'>
// <![CDATA[
	$(document).ready(function(){
		$("#spraypagination").paginate({
			count                   : parseInt("<?=$pages?>", 10) || 1,
			start                   : 1,
			display                 : 15,
			border                  : true,
			border_color            : '#ccc',
			text_color              : '#333',
			background_color        : '#eee',    
			border_hover_color      : '#aaa',
			text_hover_color        : '#000',
			background_hover_color  : '#fff', 
			images                  : true,
			mouse                   : 'press',
			onChange                : function(page){
				$(".spraypage").hide().eq(page-1).show();
				$(".spraypage:visible img").each(function(){
					$(this).attr("src", $(this).attr("srb"));
				});
			}
		});
		$(".spraypage").eq(0).show();
		$(".spray > img").tooltip({ delay: 0, track: true, showURL: false });
	});
// ]]>
</script>

</div>

<div align='center'>
<?php echo "Server Name: " .$servname."<br>"; echo $staticUptime."<br>"; echo "Server Load: ".$percentage."%"; ?>
</div>

</body>

</html>
