# Spray n' Display

## ALERT: THIS IS PLUGIN WHICH I DO NOT OWN. I COULDN'T REACH ITS REPOSITORY, SO I UPLOAD THIS TO MY GIT REPOSITORY. ORIGINAL LINK: <https://forums.alliedmods.net/showthread.php?p=1190200>

## Spray 'n Display allows you to see all the sprays that have been used on your server in a simple web panel, along with admin tools to block inappropriate sprays

### Updating 1.0 to 1.1

- Run this on the SQL database

| Code |
|--|
|```ALTER TABLE `sprays`  ADD COLUMN `ip` VARCHAR(16) NOT NULL AFTER `steamid`;```|

- Reupload `index.php` and `badspray.png`
- Reupload compiled plugin.

### Links

[Spray Tracker in action.](http://www.gamingmasters.org/sprays/) (XenForo)

[DevIL](http://openil.sourceforge.net/)

[ImageMagick](http://www.imagemagick.org/script/index.php)

### Requirements

- SRCDS server with SM installed
- Web Server:
  - PHP 5.2 or later
  - MYSQL 4.1 or later
- Imagemagick (commandline)
- DevIL image Library 1.7.8

### Known bugs

Some sprays may sometimes fail to convert, the cause is unknown.  
  
### Credits

Original idea and reference: Ubercow and the rest at nom-nom-nom.us etc  
Original code: Darkimmortal, Geit - Gamingmasters.co.uk  
vBulletin intergrate to Barebones conversion of web script: Reag - Reager.org, LasPlagas (CrudeOil) - rrerr.me  
  
### Installation
  
1. - For linux, you are first required to compile vtfconv with the DevIL library, to do this just run the compile file which is included within the package. 
   - For Windows, simply extract vtfconv.exe and DevIL.dll to your windows\system32 folder or any other folder in your system PATH.

1. You now need to setup the webpanel, see below for installation instructions for Barebones and vBulletin installation  
1. Setup the following cron (windows users can use cronw or equivalent) job:

    `*/5 * * * * php server/path/to/sprays/sprays/cron/sprays.php &> /dev/null`

   - That will set the file to be executed every 5 minutes, the script automatically downloads the vtfs and sends them though vtfconv, making a series of pngs which imagemagick then merges together as a gif, the script then deletes the vtfs and the png frames.
   - Inside sprays.php you can disable the deletion of the vtf files by commenting out line 138.

1. Compile spraytracker.sp, edit if needed as the plugin automatically connects to the default mysql entry on sourcemod.  
1. Setup your sourcemod databases.cfg to set it so the plugin can connect to the mysql database.  
1. Upload plugin once done and change map to get player details, if no errors player info should now be presented inside the mysql database.  
  
If all went right your system should now be running.  
  
### Barebones Webpanel Setup
  
1. First you need to edit the settings file (`webpanel/barebones/settings.php`), this holds the INdir, OUTdir, FTP and MySQL details.  

   - If you are using an external server FTP will be required to extract the files, if your server is localhost you can disable FTP and set the indir to the server path to where your src server is located/tf/downloads, if not use the provided example and match it to your FTP folder. Once this file is setup, save and close.

1. Proceed to upload the following folders to your webspace under the dir /sprays/: cron, css, images, inc, js and (If on Linux) CHMOD cron/log to 777, this will enable the cron job to print out a simple log of every time it downloads a file to see if it failed or not.  
1. Please now import sprays.sql into your created database, if you haven't created one, Do it now, otherwise step 2 was a waste of a time.  
1. Remember to edit index.php's line 31, this is used for the simple login system to allow admins to block sprays from view on the server.  
  
### vBulletin Webpanel Setup
  
1. First you need to edit the settings file (`webpanel/vbulletin/settings.php`), this holds the INdir, OUTdir, FTP and MySQL details and the GID's of the admin groups.  

   - If you are using an external server FTP will be required to extract the files, if your server is localhost you can disable FTP and set the indir to the server path to where your src server is located/tf/downloads, if not use the provided example and match it to your FTP folder. Once this file is setup, save and close.

1. Add a new template called 'drops_index' to your Vbulletin installation (AdminCP -> Styles & Templates -> Style Manager -> Add new Template) using the content from webpanel/vbulletin/drops_index.txt -- You will need to add the template for every active style installed!  
1. Proceed to upload the following folders to your webspace under the dir {VBULLETINROOT}/sprays/: cron, css, images, inc, js and (If on Linux) CHMOD cron/log to 777, this will enable the cron job to print out a simple log of every time it downloads a file to see if it failed or not.  
1. Please now import sprays.sql into your created database, if you haven't created one, Do it now, otherwise step 2 was a waste of a time.  

### ChangeLog

### 1.1

- Added IP tracking to the plugin.
- Changed index.php so it uses ip:port for server names rather than just port.
- Changed badspray.png to be more generic
- (1.11) - Ninja update to fix a bug that would crash servers.
