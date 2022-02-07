####################################################################################
############################# Source Sprays System #################################
####################################################################################
#Original idea and reference: Ubercow and the rest at nom-nom-nom.us etc           #
#Original code: Darkimmortal, Geit - Gamingmasters.co.uk                           #
#vB removal to barebones squad: Reag - Reager.org, LasPlagas (CrudeOil) - rrerr.me #
####################################################################################
####################################################################################

// Requirements:

Linux system - Fedora, Debian, etc
DevIL image Library 1.7.8 - http://openil.sourceforge.net/
Imagemagick (commandline) - http://www.imagemagick.org/script/index.php
Sourcemod - Plugin source included
Mysql server - Player data storage
PHP - Duh
Root access - Duh

// To install:

1. You are first required to compile vtfconv with the DevIL library, to do this just run the convert file which is included within the package.
2. Now before you upload the rest of the package contents you are required to setup settings.php, this folder holds the indir, outdir, FTP and mysql details.
	If you are using an external server FTP will be required to extract the files, if your server is localhost you can disable FTP and set the indir to the server path to 
	where your src server is located/tf/downloads, if not use the provided example and match it to your FTP folder. Once this file is setup, save and close.
3. Proceed to upload the following folders to your webspace under the dir sprays: cron, css, images, inc, js. Now please create the a log folder inside cron and cmod it to 777,
	this will enable the cron job to print out a simple log of every time it downloads a file to see if it failed or not.
4. Please now import sprays.sql into your created database, if you haven't created one, DO IT NOW FAGGOT OTHERWISE STEP 2 WAS A WASTE OF TIME.
5. Compile spraytracker.sp, edit if needed as the plugin automatically connects to the default mysql entry on sourcemod.
6. Setup your sourcemod databases.cfg to set it so the plugin can connect to the mysql database.
7. Upload plugin once done and change map to get player details, if no errors player info should now be presented inside the mysql database.
8. Setup the following cron job: */5 * * * * php server/path/to/sprays/sprays/cron/sprays.php &> /dev/null
	That will set the file to be executed every 5 minutes, the script automatically downloads the vtfs and sends them though vtfconv, making a series of pngs which imagemagick
	then merges together as a gif, the script then deletes the vtfs and the png frames.
	Inside sprays.php you can disable the deletion of the vtf files by commenting out line 138.
9. If all went right your system should now be running.

Bonus step: Remember to edit index.php's line 31, this is used for the simple login system to allow admins to block sprays from view on the server.
