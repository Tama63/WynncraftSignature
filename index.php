<?php
/**
 * Wynncraft Signature
 *
 * @copyright Wynncraft 2013
 * @author Chris Ireland <ireland63@gmail.com>
 */

/* Include Basic Functions */
include("functions.php");

/* Check input else exit */
$player = $_GET["player"];
$player = protectInput($player);
if(empty($_GET["player"]) || (file_get_contents("https://minecraft.net/haspaid.jsp?user=$player") != "true")) die("Player Invalid"); // Check for get and if player is premium

/* Theme Data */
$theme = $_GET["theme"];
$theme = protectInput($theme);

// Assign background image from number
switch ($theme) {
	case 1:
		$theme = "img/sea.png";
		break;
	case 2:
		$theme = "img/sky.png";
		break;
	case 3:
		$theme = "img/stone.png";
		break;
	case 4:
		$theme = "img/tower.png";
		break;
	case 5:
		$theme = "img/intro.png";
		break;
	case 6:
		$theme = "img/wall.png";
		break;
	case 7:
		$theme = "img/board.png";
		break;	
	default:
		$theme = "img/dirt.png";
		break;
}

/* Turn from string into array */
$playerdata = file_get_contents("http://wynncraft.com/api/public_api.php?type=player&player=$player");
if($playerdata == "false") die("Player not logged in Wynncraft stats"); // Has the player actually been on Wynncraft?
$playerdata = explode(",", $playerdata);
	
/* Array Doc
-Adding &json outputs json instead of csv
0 : rank  0 - normal, 1 - donator, 2 - moderator, 3 - admin
1 : diff in from now since log in, represeneted in months and days
2 : play time, how many many hours have they played for rounded to 2 d.p.
3 : current server number, 0 for lobby or offline
4 : items identified
5 : mobs killed
6 : emeralds found
7 : pvp kills
8 : loot chests found
9 : blocks walked
10 : logins
11 : deaths
12 : total level
*/

/* Fetching from external api */
// Handle no skin
$playerskin = $player;
$check = @file_get_contents("http://skins.minecraft.net/MinecraftSkins/".$player.".png");
if($check == "") $playerskin = "char";

// Get skin from api
$avatar = file_get_contents("http://signaturecraft.us/avatars/10/body/$playerskin.png");
$avatar = imagecreatefromstring($avatar);

/* Background Image */
$img = imagecreatefrompng($theme);

/* Hacking merge */
imagesetbrush($img, $avatar); 
imageline($img, imagesx($img) / 1.15, imagesy($img) / 1.5, imagesx($img) / 1.15, imagesy($img) / 1.5, IMG_COLOR_BRUSHED); 

/* Text */
// Handle rank data
$rank = $playerdata[0]; 
switch ($rank){
	case 0:
		$rank = "Member";
		$colorrank = imagecolorallocate($img, 220, 220, 220);
		break;
	case 1:
		$rank = "VIP";
		$colorrank = imagecolorallocate($img, 0, 170, 0);
		break;
	case 2:
		$rank = "Moderator";
		$colorrank = imagecolorallocate($img, 255, 170, 0);
		break;
	case 3:
		$rank = "Administrator";
		$colorrank = imagecolorallocate($img, 170, 0, 0);
		break;
}

// Handle current server
if(strstr($playerdata[3], "WC")) {
	$currentserver = "Playing on Server $playerdata[3]";
	$colorstatus = imagecolorallocate($img, 0, 170, 0); // Green if online
} else {
	$currentserver = "Offline or in Lobby";
	$colorstatus = imagecolorallocate($img, 170, 0, 0); // Red if offline
}

// Handle kills
$mobs = convertNum($playerdata[5]);
$players = convertNum($playerdata[7]);

if($mobs == 1) { // Convert for plurals
	$mobslocale = "mob";
} else {
	$mobslocale = "mobs";
}
if($players == 1) { // Convert for plurals
	$playerslocale = "player";
} else {
	$playerslocale = "players";
}

//Handle playtime plural
if($players <= 1 && !$players > 1 ) {
	$playtimelocale = "hour";
} else {
	$playtimelocale = "hours";
}

// Font Variables
$font = "fonts/DroidSans-Bold.ttf";
$color = imagecolorallocate($img, 250, 250, 250);
$fontalt = "fonts/DroidSans.ttf";
$coloralt = imagecolorallocate($img, 10, 10, 10);

// Echo Messages
imagettftext($img, 10, 0, 15, 35, $color, $font, "Rank:"); // Rank
imagettftext($img, 10, 0, 65, 35, $colorrank, $fontalt, "$rank");

imagettftext($img, 10, 0, 15, 50, $color, $font, "Playtime:"); // Playtime
imagettftext($img, 10, 0, 85, 50, $coloralt, $fontalt, "$playerdata[2] $playtimelocale"); 

imagettftext($img, 10, 0, 15, 63, $color, $font, "Total Level:"); // Total level
imagettftext($img, 10, 0, 100, 63, $coloralt, $fontalt, "$playerdata[12]"); 

imagettftext($img, 10, 0, 15, 76, $color, $font, "Killed:"); // kills
imagettftext($img, 10, 0, 65, 76, $coloralt, $fontalt, "$mobs $mobslocale & $players $playerslocale");

imagettftext($img, 10, 0, 15, 89, $color, $font, "Status:"); // Online
imagettftext($img, 10, 0, 70, 89, $colorstatus, $fontalt, "$currentserver");

/* Save */
header("Content-type: image/png");
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
header("Pragma: no-cache");

imagepng($img) or die("Imaged failed to load");
imagedestroy($img);
