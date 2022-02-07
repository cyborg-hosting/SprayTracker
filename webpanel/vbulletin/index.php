<?php
include_once("settings.php");
$notvb = mysql_pconnect($host, $user, $pass) or mysql_error();
mysql_select_db($db, $notvb) or mysql_error($notvb);
mysql_set_charset("UTF8", $notvb);

define("DELETEDAYS", "7");

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################


define('THIS_SCRIPT', 'sprays_index');
define('CSRF_PROTECTION', true);  
// change this depending on your filename

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
require_once('./global.php');

require "blocks/sharedfunc.php";

ob_start();

if($vbulletin->userinfo['usergroupid'] == SUPERADMINS && $_GET['ban'] == 1 || $vbulletin->userinfo['usergroupid'] == ADMINS && $_GET['ban'] == 1){
	$res = mysql_query("select * from sprays where filename='".mysql_real_escape_string($_GET['filename'])."' limit 1", $notvb) or die(mysql_error());
	$row = mysql_fetch_array($res);
	mysql_query("update sprays set banned=1 where filename='{$row['filename']}' limit 1", $notvb) or die(mysql_error());
}

if($vbulletin->userinfo['usergroupid'] == SUPERADMINS && $_GET['unban'] == 1){
	$res = mysql_query("select * from sprays where filename='".mysql_real_escape_string($_GET['filename'])."' limit 1", $notvb) or die(mysql_error());
	$row = mysql_fetch_array($res);
	mysql_query("update sprays set banned=0 where filename='{$row['filename']}' limit 1", $notvb) or die(mysql_error());
}

$order = $_GET['o'];
$ordersql = 'firstdate DESC, count DESC, date DESC';
if($order == 'count'){
	$ordersql = 'count DESC, firstdate DESC, date DESC';
}


$result = mysql_query("SELECT *, UNIX_TIMESTAMP(firstdate) AS t_firstdate, UNIX_TIMESTAMP(date) AS t_date FROM sprays WHERE datediff(NOW(), firstdate) < ".DELETEDAYS." OR datediff(NOW(), date) <".DELETEDAYS." ORDER BY {$ordersql} LIMIT 3000", $notvb) or die(mysql_error());

if(mysql_num_rows($result) < 1) echo "<i>No sprays found</i><br /><br />";

echo "<center>";
echo "Displaying <b>".mysql_num_rows($result)."</b> sprays from the last ".DELETEDAYS." days.<br /><b>Warning: NSFW</b><br />";
if($order == 'count'){
	echo "<a href='/sprays/'>Order by upload date</a>";    
} else {
	echo "<a href='/sprays/?o=count'>Order by amount of times sprayed</a>";
}
echo "</center>";
?>
<br />
<link rel="stylesheet" type="text/css" href="/sprays/css/style.css" />
		<style type='text/css'>
			#sprays { background: url(/sprays/wall.jpg) repeat; text-align: center; }
			.spray { display: inline-block; width: 256px; padding: 3px; margin: 3px; /*border: 1px solid #ccc;*/ text-align: center; color: #fff !important; font-weight: bold; }
			.spray a { font-size: 16px; margin-top: 2px; color: #fff !important; }
			.spraypage { display: none; }
			#tooltip { position: absolute; border: 2px solid #666; padding: 6px 6px 6px 10px; background-color: #333; color: #eee; z-index: 3000; min-width: 180px; min-height: 50px; }
			#spraypagination { -moz-user-select: none; -khtml-user-select: none; user-select: none; }
		</style>

<div style='margin: 5px auto; width: 500px; text-align: center;'><div id='spraypagination'></div></div>
<div id='sprays'><div class='spraypage'>
<?php

$servers = array(
'123.321.111.222:27040' => 'Facepunch TF2UK', 
'123.321.111.222:27015' => 'Facepunch TF2UNSRS'
);

$pages = 1;
$onpage = 0;

while($row = mysql_fetch_array($result)){
	
	if(!file_exists("sprays/img/{$row['filename']}.gif") || filesize("sprays/img/{$row['filename']}.gif") < 1024) continue;
	
	$onpage ++;
	if($onpage > 32){
		$pages ++;
		$onpage = 1;
		echo "</div><div class='spraypage'>";
	}
	
	$date = date('d/m/Y H:i:s', $row['t_date']);
	if($row['t_date'] == 0)
		$date = "Never";
		
	$firstdate = date('d/m/Y H:i:s', $row['t_firstdate']);
		
	$port = intval($row['port']);
	
	if(array_key_exists("{$row['ip']}:{$port}", $servers))
		$server = $servers["{$row['ip']}:{$port}"];
	else
		$server = "Unknown Server";

	$f = steam2friend($row['steamid']);
	$link = "<a href='http://steamcommunity.com/profiles/{$f}'>{$row['name']}</a>";
	
	if($row['banned'] == 0)
		$av = "/sprays/img/{$row['filename']}.gif";
	else
		$av = "/sprays/badspray.png";
		
	$count = $row['count']." time".($count == 1 ? "" : "s");
	
	$manager ="";
	if($vbulletin->userinfo['usergroupid'] == SUPERADMINS && $row['banned'] == 1) $manager = "<br /><b>Manager blocked spray view:</b><br /><img src=\"/sprays/img/{$row['filename']}.gif\" alt=\"\" width=\"256\" height=\"256\" />";
	
	?>
	<div class='spray'>
		<img <?=($pages == 1 ? "src" : "srb")?>='<?=$av?>' alt='' width='256' height='256' title='<b>Uploaded:</b> <?=$firstdate?><br /><b>Last sprayed:</b> <?=$date?><br /><b>Last server: </b><?=$server?><br /><b>Sprayed:</b> <?=$count?><?=$manager?>' />
		<div>
		<?=$link?> <?php if($vbulletin->userinfo['usergroupid'] == SUPERADMINS && $row['banned'] == 1){ ?> &mdash; <a href='/sprays/?unban=1&filename=<?=$row['filename']?>' title='Manager-only option - Unblocks converted spray and Unblocks the spray from being used in the future' style='font-size: 11px'>Unblock</a><?php } ?>
		<?php if($vbulletin->userinfo['usergroupid'] == SUPERADMINS && $row['banned'] == 0 || $vbulletin->userinfo['usergroupid'] == ADMINS && $row['banned'] == 0){ ?> &mdash; <a href='/sprays/?ban=1&filename=<?=$row['filename']?>' title='Blocks the spray from being used in the future' style='font-size: 11px'>Block</a><?php } ?>
		</div>
	</div>		
	<?php
}
?>

</div></div>

<script type='text/javascript' src='/sprays/jquery.js'></script>
<script type='text/javascript' src='/sprays/jquery.paginate.js'></script>
<script type='text/javascript' src='/sprays/jquery.tooltip.js'></script>
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
		}).get(0).onselectstart = function(){ return false };
		$(".spraypage").eq(0).show();
		$(".spray > img").tooltip({ delay: 0, track: true, showURL: false/*, left: -110, top: 20*/ });
	});
// ]]>
</script>

<?php
// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$navbits = construct_navbits(array('derp' => 'Sprays'));
$navbar = render_navbar_template($navbits);

// ###### YOUR CUSTOM CODE GOES HERE #####
$pagetitle = 'Sprays';

// ###### NOW YOUR TEMPLATE IS BEING RENDERED ######

$templater = vB_Template::create('drops_index');
$templater->register_page_templates();
$templater->register('navbar', $navbar);
$templater->register('pagetitle', $pagetitle);
$templater->register('htmlfaggotry', ob_get_clean());
//ob_end_clean();
//ob_start();
header("Content-Type: text/html; charset=UTF-8", true);
print_output(str_ireplace("ISO-8859-1", "UTF-8", $templater->render()));
//echo  ob_get_clean());