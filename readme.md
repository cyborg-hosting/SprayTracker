# Spray n' Display

## ALERT: THIS IS PLUGIN WHICH I DO NOT OWN. I COULDN'T REACH ITS REPOSITORY, SO I UPLOAD THIS TO MY GIT REPOSITORY. ORIGINAL LINK: <https://forums.alliedmods.net/showthread.php?p=1190200>

## Spray 'n Display allows you to see all the sprays that have been used on your server in a simple web panel, along with admin tools to block inappropriate sprays

### Links

[vtf2apng](https://github.com/jobggun/vtf2apng)

### Requirements

- SRCDS server with SM installed
- Web Server:
  - PHP 5.2 or later
  - MYSQL 4.1 or later
- vtf2apng
  - For Windows, docker windows or python 3.x with pillow(PIL) library required
  - For Linux, docker is required, and docker-compose is recommended to install
    [vtf2apng](https://hub.docker.com/r/datmoyan/vtf2apng) docker container with necessary files

### Known bugs

~~Some sprays may sometimes fail to convert, the cause is unknown.~~ Maybe Fixed
  
### Credits

Original idea and reference: Ubercow and the rest at nom-nom-nom.us etc  
Original code: Darkimmortal, Geit - Gamingmasters.co.uk  
vBulletin intergrate to Barebones conversion of web script: Reag - Reager.org, LasPlagas (CrudeOil) - rrerr.me  
  
### Installation
  
1. - Using docker, you need to follow vtf2apng image's readme.
   - Using python in windows, you need to collect necessary files to run vtf2tga.exe which is written in vtf2apng image's readme.
1. You now need to setup the webpanel, see below for installation instructions for Barebones and vBulletin installation  
1. Setup the vtf2apng container (windows users can use vtf2apng directly with cronw or equivalent) following directions
1. Compile spraytracker.sp, edit if needed as the plugin automatically connects to the default mysql entry on sourcemod.  
1. Setup your sourcemod databases.cfg to set it so the plugin can connect to the mysql database.  
1. Upload plugin once done and change map to get player details, if no errors player info should now be presented inside the mysql database.  
  
If all went right your system should now be running.  
  
### Barebones Webpanel Setup
  
1. First you need to edit the settings file (`webpanel/barebones/settings.php`), this holds the MySQL details.
1. Proceed to upload the following folders to your webspace under the dir /sprays/: css, images, inc, js.
1. Please now import sprays.sql into your created database, if you haven't created one, Do it now, otherwise step 2 was a waste of a time.  
1. Remember to edit index.php's line 34, this is used for the simple login system to allow admins to block sprays from view on the server.  

### ChangeLog

### 1.1

- Added IP tracking to the plugin.
- Changed index.php so it uses ip:port for server names rather than just port.
- Changed badspray.png to be more generic
- (1.11) - Ninja update to fix a bug that would crash servers.
