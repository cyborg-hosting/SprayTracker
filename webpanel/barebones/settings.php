<?php
	
		
		// downloader and converter settings
        define("OUTDIR", "/home/example/path/img"); 	/* 	change this to the directory the sprays are to be saved, please don't add a slash to the end of this.
																							make sure this directory has permissions set, to 777 (read/write) to be exact. */

        define("INDIR", "/89.145.94.47 port 27040/tf/downloads"); 	/*	change this to the /downloads directory on the server's ftp, !!do not end with a slash!!
																	 	You can figure this out by connecting to the FTP server and navigating to the /downloads directory
																	 	it's a directory with a lot of .ztmp and .dat files*/
		
		define("LOG", "log/log.txt"); 	/*	Set this to whatever you think is best for this. Believe me, you'd hate having to wait for this script just to die somewhere.
											You need to create this file, and set it's permessions to 777 (read/write)*/
		
        define("DELETEDAYS", "7"); // the amount of days sprays are to be saved.
		
        define("FORMAT", "gif"); // don't put a dot in here, bad idea. Just leave this as it is.


		// index settings
		define('THIS_SCRIPT', 'sprays_index'); // change depending on your page name
		
		define('CSRF_PROTECTION', true);  
		
		// --MYSQL SETTINGS-- //
		
		// Set these to the SQL server you're using for the sprays.
		
		$user = "";
		$pass = "";
		$db = "";
		$host = "";
		
		
		// --FTP SETTINGS--//
		
		// If your server is on the same machine as your webhost, set use_ftp to false.
		// Otherwise you will need to set this to the FTP of your GAMEserver.
		
		$use_ftp = true;
		
		$ftp = "";
		$ftpuser = "";
		$ftpass = "";
		
?>