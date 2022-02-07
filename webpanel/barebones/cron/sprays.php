<?php

	// fix retardation
	chdir(dirname(__FILE__));

	// HELLO! IF YOUR INDEX.PHP IS NOT IN THE SAME FOLDER AS THIS FILE, PLEASE CHANGE THE FOLLOWING TO THE PATH WHERE SETTINGS.PHP IS LOCATED!!!

	include_once("../settings.php");

	$stamp = "o-m-d H:i:s";

	function get_ftp_mode($file)
	{    
		$path_parts = pathinfo($file);
		
		if (!isset($path_parts['extension'])) return FTP_BINARY;
		switch (strtolower($path_parts['extension'])) {
		case 'am':case 'asp':case 'bat':case 'c':case 'cfm':case 'cgi':case 'conf':
		case 'cpp':case 'css':case 'dhtml':case 'diz':case 'h':case 'hpp':case 'htm':
		case 'html':case 'in':case 'inc':case 'js':case 'm4':case 'mak':case 'nfs':
		case 'nsi':case 'pas':case 'patch':case 'php':case 'php3':case 'php4':case 'php5':
		case 'phtml':case 'pl':case 'po':case 'py':case 'qmail':case 'sh':case 'shtml':
		case 'sql':case 'tcl':case 'tpl':case 'txt':case 'vbs':case 'xml':case 'xrc':
			return FTP_ASCII;
		}
		return FTP_BINARY;
	}

	/* 
			And here comes my code. Rainbows make me cry.
			
			It might just be coding genocide, but it should be safe.
			*/
	//////

	if($use_ftp == true) {
		$conn = ftp_connect($ftp); // Connect to the ftp,
		if (!$conn) {
			file_put_contents(LOG, "[".date($stamp)."] FTP connection failure!\n", FILE_APPEND); // log
			die("Couldn't connect to FTP server :("); // die on failure
		}
		
		
		if (!@ftp_login($conn, $ftpuser, $ftpass)) { // Login on the ftp stream,
			file_put_contents(LOG, "[".date($stamp)."] FTP login failure!\n", FILE_APPEND); //log
			die("Failed to login"); // die on failure
		}
		
		ftp_pasv($conn, true);	// turn on passive mode, YEAH BABY TURN ME ON
		// sorry
	}

	// And this is where it's gonna get bad

	if($use_ftp == true) {
		$dirlist = ftp_nlist($conn, INDIR); // should return an array of files in 'INDIR', thus the sprays.
	}else{
		$dirlist = glob(INDIR."*");
	}

	$dirlistweb = glob("{".INDIR."/*.png,".INDIR."/*.gif}");

	// Oh dear god, take cover.
	$download = array(); // first declare an array

	file_put_contents(LOG, "\n\n--Files downloading--:\n\n", FILE_APPEND); // indicate file downloading in progress

	mysql_connect($host, $user, $pass); // connect to sql
	mysql_select_db($db); // select db
	
	
	$res = mysql_query("select * from sprays where datediff(firstdate, NOW()) < ".DELETEDAYS." or (date > 0 and datediff(date, NOW()) < ".DELETEDAYS.")"); // select all sprays from 7 days and those that were not sprayed yet
	
	while($row = mysql_fetch_assoc($res)) { // fetch all spray entries in sql
		if ($use_ftp == true) {
			if(!file_exists(OUTDIR."/"."{$row['filename']}.".FORMAT)) {
				$err1 = ftp_get($conn, OUTDIR."/{$row['filename']}.vtf", INDIR."/{$row['filename']}.dat", get_ftp_mode($download[$i])); // download the vtf file
				$err2 = ftp_get($conn, OUTDIR."/{$row['filename']}.vtf.ztmp", INDIR."/{$row['filename']}.dat.ztmp", get_ftp_mode($download[$i])); // download the vtf.ztmp file
			}
		}else{
			//copy
		}
	}
	
	if($err1 == true && $err2 == true) {
		file_put_contents(LOG, "[".date($stamp)."] File downloading done successfully!\n\n", FILE_APPEND); // indicate so.
	}else{
		file_put_contents(LOG, "[".date($stamp)."] One or more files failed to download!\n\n", FILE_APPEND); // indicate so.
	}
	
	
	ftp_close($conn); // finally,

	////// End of rape
	
	chdir(OUTDIR);
	
	$keep = array(); // create an array for sprays that are to be kept (7 days limit)
	
	$res2 = mysql_query("select * from sprays where datediff(firstdate, NOW()) < ".DELETEDAYS." or (date > 0 and datediff(date, NOW()) < ".DELETEDAYS.")"); // select all sprays from 7 days and those that were not sprayed yet
	
	while($row = mysql_fetch_assoc($res2)) { // fetch all spray entries in sql	
	
		if(!file_exists($row['filename'].FORMAT)) {
			if (!file_exists($row['filename'].".vtf")) { // if the vtf of a filename does not exist
				echo("File '".$row['filename'].".vtf' does not exist (according to the script)!<br>\n"); // panic
				$error = 1; // let the script know there was an error
			}
			
			exec("/usr/local/bin/vtfconv ".escapeshellarg($row['filename']).".vtf", $out); // convert the file
			$output = implode("\n", $out); // output lines, this turns an array into a single variable, in this case seperating them with newlines
			echo "CLI output: {$output}\n<br>"; // echo the cli output
			
			
			$derp = 0; // set frames to 0
			$src = array(); // initiate an array
			
			while(file_exists("{$row['filename']}.vtf.{$derp}.png")){ // for every frame that there is,
				$src[]="{$row['filename']}.vtf.{$derp}.png"; // put it in the source array
				$derp ++; // and count the frame
			} // end
			
			if($derp < 1){ // if the frame number never hit 1 something went wrong,
				echo "Failed to convert ".OUTDIR."/{$row['filename']}.vtf<br>\n"; // so tell reag that he needs to yell at me more
				$error = 1; // and tell the script that it bugged out
			}
			
			exec("convert -delay 25 -loop 0 -set dispose background ".implode(" ", $src)." {$row['filename']}.gif", $out); // convert the frames to a gif
			$output = implode("\n", $out); // output lines, this turns an array into a single variable, in this case seperating them with newlines
			echo "CLI output: {$output}<br>\n"; // echo the cli output
			
			
			echo "Saved {$derp} frame(s) to ".OUTDIR."/".$row['filename'].".".FORMAT."<br>\n"; // tell reag he needs to dance in joy
			
			foreach ($src as $del){ // for each spray frame
				unlink(OUTDIR."/".$del); // DELETE IT
			}
			unlink(OUTDIR."/".$row['filename'].".vtf"); // DELETE THIS AS WELL, COMMENT THIS OUT IF YOU WANT TO KEEP THE VTFS.
			unlink(OUTDIR."/".$row['filename'].".vtf.ztmp"); // AND THIS
		}
		
	}
	
	
?>