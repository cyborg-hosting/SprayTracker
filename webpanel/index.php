<?php
require('vendor/autoload.php');

require_once("settings.php");
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

require("inc/session.php");

$query_string = "SELECT *, UNIX_TIMESTAMP(`firstdate`) AS `firstdate_ts`, UNIX_TIMESTAMP(`date`) AS `date_ts` FROM `sprays`";

$deletedays = intval($_GET['deletedays']);
if ($deletedays <= 0) {
	$deletedays = Constants::DELETEDAYS;
}
$query_string .= " WHERE (datediff(NOW(), `firstdate`) < {$deletedays} OR datediff(NOW(), `date`) < {$deletedays})";

$sprayed = $_GET['sprayed'];
if ($sprayed == 1) {
	$query_string .= ' AND `count` > 0';
}

$order = $_GET['order'];
if ($order == 'count') {
	$query_string .= ' ORDER BY count DESC, firstdate DESC, date DESC';
} else if ($order == 'date') {
	$query_string .= ' ORDER BY date DESC, count DESC, firstdate DESC';
} else {
	$query_string .= ' ORDER BY firstdate DESC, count DESC, date DESC';
}

$limit = intval($_GET['limit']);
if ($limit <= 0) {
	$limit = Constants::LIMIT;
}
$query_string .= " LIMIT {$limit}";

$stmt = $db->query($query_string, PDO::FETCH_ASSOC);

$servname = gethostbyaddr(gethostbyname($_SERVER["SERVER_NAME"]));

// Smarty
$smarty = new Smarty();

$smarty->setCacheDir('smarty/cache');
$smarty->setConfigDir('smarty/configs');
$smarty->setTemplateDir('smarty/templates');
$smarty->setCompileDir('smarty/templates_c');

$smarty->assign('_SESSION', $_SESSION);

$smarty->assign('SERVER_NAME', Constants::SERVER_NAME);

$smarty->assign('deletedays', $deletedays, true);
$smarty->assign('order', $order, true);
$smarty->assign('limit', $limit, true);

$smarty->assign('num_rows', $stmt->rowCount());

// sprays

function spray_generator() {
	global $stmt;

	$pages = 1;
	$onpage = 0;

	while ($row = $stmt->fetch()) {
		if (!file_exists("img/{$row['filename']}.png")) {
			continue;
		}
//		if(filesize("img/{$row['filename']}.png") < 1024) {
//			continue;
//		}

		$assoc = $row;

		$assoc['onpage'] = $onpage;

		$onpage += 1;
		if ($onpage == 24) {
			$pages += 1;
			$onpage = 0;
		}

		if (isset(Constants::SERVERS["{$row['ip']}:{$row['port']}"])) {
			$assoc['server'] = Constants::SERVERS["{$row['ip']}:{$row['port']}"];
		} else {
			$assoc['server'] = 'Unknown Server';
		}

		$assoc['steamid64'] = steam2friend($row['steamid']);

		yield $assoc;
	}
}

$smarty->assign('sprays', spray_generator());

ob_start();
$smarty->display('index.tpl');
ob_end_flush();