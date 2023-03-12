<?php
include_once("settings.php");
require("inc/sharedfunc.php");
session_start();

try {
	$db = new PDO($dsn, $pdo_username, $pdo_password);
} catch (PDOException $e) {
	print("Database Error: " . $e->getMessage());
	die(1);
}

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

ob_start();

require("inc/session.php");

$query_string = "SELECT *, UNIX_TIMESTAMP(`firstdate`) AS `firstdate_ts`, UNIX_TIMESTAMP(`date`) AS `date_ts` FROM `sprays`";

$deletedays = intval($_GET['deletedays']);
if ($deletedays > 0) {
	$query_string .= "WHERE (datediff(NOW(), `firstdate`) < {$deletedays} OR datediff(NOW(), `date`) < {$deletedays})";
} else {
	$query_string .= 'WHERE (datediff(NOW(), `firstdate`) < ' . Constants::DELETEDAYS . ' OR datediff(NOW(), `date`) < ' . Constants::DELETEDAYS;
}

$sprayed = $_GET['sprayed'];
if ($sprayed == 1) {
	$query_string .= 'AND `count` > 0';
}

$order = $_GET['order'];
if ($order == 'count') {
	$query_string .= 'ORDER BY count DESC, firstdate DESC, date DESC';
} else if ($order == 'date') {
	$query_string .= 'ORDER BY date DESC, count DESC, firstdate DESC';
} else {
	$query_string .= 'ORDER BY firstdate DESC, count DESC, date DESC';
}

$limit = intval($_GET['limit']);
if ($limit > 0) {
	$query_string .= "LIMIT {$limit}";
} else {
	$query_string .= 'LIMIT ' . Constants::LIMIT;
}

$stmt = $db->query($query_string, PDO::FETCH_ASSOC);

$servname = gethostbyaddr(gethostbyname($_SERVER["SERVER_NAME"]));
?>
<!DOCTYPE html>
<html>


<head>
	<title>Facepunch TF2 - Sprays</title>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>

	<div align='center'>
		<div class='header'>
			<div id='pages'>
				<div id='spraypagination'></div>
				<div id='notice'>
					<?php
					echo "Displaying <b>" . mysqli_num_rows($result) . "</b> sprays from the last " . Constants::DELETEDAYS . " days.<br /><b>Warning: NSFW</b><br />";
					if ($order == 'count') {
						echo "<a href='/sprays/'>Order by upload date</a> | <a href='/sprays/?o=date'>Order by most recently sprayed</a>";
					} elseif ($order == 'date') {
						echo "<a href='/sprays/'>Order by upload date</a> | <a href='/sprays/?o=count'>Order by amount of times sprayed</a>";
					} else {
						echo "<a href='/sprays/?o=count'>Order by amount of times sprayed</a> | <a href='/sprays/?o=date'>Order by most recently sprayed</a>";
					}
					if (mysqli_num_rows($result) < 1)
						echo "<p><i>No sprays found</i></p>";
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
		<div id='sprays'>
			<div class='spraypage'>
				The sprays page shows all current and used sprays by players on the Facepunch Team Fortress 2
				server.<br>
				<?php

				$servers = array(
				);

				$pages = 1;
				$onpage = 0;

				while ($row = mysqli_fetch_assoc($result)) {

					if ((!file_exists("sprays/img/{$row['filename']}.gif") || filesize("sprays/img/{$row['filename']}.gif") < 1024) && (!file_exists("sprays/img/{$row['filename']}.png") || filesize("sprays/img/{$row['filename']}.png") < 1024))
						continue;

					$onpage++;
					if ($onpage > 24) {
						$pages++;
						$onpage = 1;
						echo "</div><div class='spraypage'>";
					}

					$date = date('d/m/Y H:i:s', $row['t_date']);
					if ($row['t_date'] == 0)
						$date = "Never";

					$firstdate = date('d/m/Y H:i:s', $row['t_firstdate']);

					$port = intval($row['port']);

					if (array_key_exists("{$row['ip']}:{$port}", $servers))
						$server = $servers["{$row['ip']}:{$port}"];
					else
						$server = "Unknown Server";

					$f = steam2friend($row['steamid']);
					$link = "<a href='http://steamcommunity.com/profiles/{$f}'>{$row['name']}</a>";
					$copy = "<a style='font-size: 11px' href='javascript:copy2clipboard(\"{$row['steamid']}\")'>copy</a>";

					if ($row['banned'] == 0) {
						if (file_exists("sprays/img/{$row['filename']}.png"))
							$av = "/sprays/img/{$row['filename']}.png";
						else
							$av = "/sprays/img/{$row['filename']}.gif";
					} else
						$av = "/sprays/badspray.png";

					$count = $row['count'] . " time" . ($count == 1 ? "" : "s");

					$manager = "";
					if ($row['banned'] == 1)
						$manager = "<br><strong>Admin blocked spray view</strong>";

					?>
					<div class='spray'>
						<img <?= ($pages == 1 ? "src" : "srb") ?>='<?= $av ?>' alt='' width='256' height='256'
							title='<strong>Uploaded:</strong> <?= $firstdate ?><br><strong>Last sprayed:</strong> <?= $date ?><br><strong>Last server: </strong><?= $server ?><br><strong>Sprayed:</strong> <?= $count ?><?= $manager ?>'>
						<div>
							<?= $link ?>
							<?= $copy ?>
							<?php if ($_SESSION['logged_in'] && $row['banned'] == 1) { ?> &mdash; <a
									href='?unban=1&filename=<?= $row['filename'] ?>' alt="\"
									title='Manager-only option - Unblocks converted spray and Unblocks the spray from being used in the future'
									style='font-size: 11px'>Unblock</a> &mdash; <a href="<?= $av ?>" alt="\"
									title='Manager-only option - Redirects to blocked spray' style='font-size: 11px'>Click to
									show.</a>
							<?php } ?>
							<?php if ($_SESSION['logged_in'] && $row['banned'] == 0) { ?> &mdash; <a
									href='?ban=1&filename=<?= $row['filename'] ?>'
									title='Blocks the spray from being used in the future' style='font-size: 11px'>Block</a>
							<?php } ?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>


	</div>

	<div align='center'>
		Server name:
		<?= $servname ?><br>
	</div>

	<script type='text/javascript' src='js/jquery.js'></script>
	<script type='text/javascript' src='js/jquery.paginate.js'></script>
	<script type='text/javascript' src='js/jquery.tooltip.js'></script>
	<script type='text/javascript'>
		// <![CDATA[
		$(document).ready(function () {
			$("#spraypagination").paginate({
				count: parseInt("<?= $pages ?>", 10) || 1,
				start: 1,
				display: 15,
				border: true,
				border_color: '#ccc',
				text_color: '#333',
				background_color: '#eee',
				border_hover_color: '#aaa',
				text_hover_color: '#000',
				background_hover_color: '#fff',
				images: true,
				mouse: 'press',
				onChange: function (page) {
					$(".spraypage").hide().eq(page - 1).show();
					$(".spraypage:visible img").each(function () {
						$(this).attr("src", $(this).attr("srb"));
					});
				}
			});
			$(".spraypage").eq(0).show();
			$(".spray > img").tooltip({
				delay: 0,
				track: true,
				showURL: false
			});
		});

		function copy2clipboard(str) {
			navigator.clipboard.writeText(str);
		}
			// ]]>
	</script>

</body>

</html>