<?php
/**
 * Wynncraft Signature
 *
 * @copyright Wynncraft 2013
 * @author Chris Ireland <ireland63@gmail.com>
 * @license MIT <http://opensource.org/licenses/MIT>
 */

// Include Basic Functions
include('functions.php');

// Check input else exit
$player = protectInput($_GET['player']);
if (empty($_GET['player']) || (file_get_contents('https://minecraft.net/haspaid.jsp?user=' . $player) != 'true'))
    die('Error: Player is not premium');

// Themes
$theme = protectInput($_GET['theme']);
switch ($theme) {
	case 1:
        $theme = 'img/sea.png';
        break;
	case 2:
        $theme = 'img/sky.png';
        break;
	case 3:
        $theme = 'img/stone.png';
        break;
	case 4:
        $theme = 'img/tower.png';
        break;
	case 5:
        $theme = 'img/intro.png';
        break;
	case 6:
        $theme = 'img/wall.png';
        break;
	case 7:
        $theme = 'img/board.png';
        break;
	default:
        $theme = 'img/dirt.png';
        break;
}

$img = imagecreatefrompng($theme);

// Get player data from the api and decode it
$playerData = @file_get_contents('http://wynncraft.com/api/public_api.php?type=player&json&player=' . $player);
if (!$playerData)
    die('Error: Player not logged in Wynncraft stats');

$playerData = json_decode($playerData, true);

// Handle no skin
$playerSkin = $player;
$check = @file_get_contents('http://skins.minecraft.net/MinecraftSkins/' . $player . '.png');
if ($check == '') $playerSkin = 'char';

// Get avatar and merge it with the background
$avatar = file_get_contents('http://signaturecraft.us/avatars/10/body/' . $playerSkin . '.png');
$avatar = imagecreatefromstring($avatar);

imagesetbrush($img, $avatar);
imageline($img, imagesx($img) / 1.15, imagesy($img) / 1.5, imagesx($img) / 1.15, imagesy($img) / 1.5, IMG_COLOR_BRUSHED);

// Prepare rank colour formatting
switch ($playerData['rank']){
    case 'VIP':
        $colorRank = imagecolorallocate($img, 0, 170, 0);
		break;
    case 'Moderator':
        $colorRank = imagecolorallocate($img, 255, 170, 0);
		break;
    case 'Administrator':
        $colorRank = imagecolorallocate($img, 170, 0, 0);
		break;
    default:
        $colorRank = imagecolorallocate($img, 220, 220, 220);
}

// Handle current server
if (strstr($playerData['current_server'], 'WC')) {
    $currentServer = 'Playing on Server ' . $playerData['current_server'];
    $colorStatus = imagecolorallocate($img, 0, 170, 0); // Green if online
} else {
    $currentServer = 'Offline or in Lobby';
    $colorStatus = imagecolorallocate($img, 170, 0, 0); // Red if offline
}

// Handle kills
$mobs = convertNum($playerData['mobs_killed']);
$players = convertNum($playerData['pvp_kills']);

if($mobs == 1) {
    $mobsLocale = 'mob';
} else {
    $mobsLocale = 'mobs';
}

if($players == 1) {
    $playersLocale = 'player';
} else {
    $playersLocale = 'players';
}

// Handle playtime plural
if($playerData['playtime'] == 1) {
    $playtimeLocale = 'hour';
} else {
    $playtimeLocale = 'hours';
}

// Font Variables
$font = 'fonts/DroidSans-Bold.ttf';
$color = imagecolorallocate($img, 250, 250, 250);
$fontAlt = 'fonts/DroidSans.ttf';
$colorAlt = imagecolorallocate($img, 10, 10, 10);

// Echo Messages
imagettftext($img, 10, 0, 15, 35, $color, $font, 'Rank:');
imagettftext($img, 10, 0, 65, 35, $colorRank, $fontAlt, $playerData['rank']);

imagettftext($img, 10, 0, 15, 50, $color, $font, 'Playtime:');
imagettftext($img, 10, 0, 85, 50, $colorAlt, $fontAlt, $playerData['playtime'] . ' ' . $playtimeLocale);

imagettftext($img, 10, 0, 15, 63, $color, $font, 'Total Level:');
imagettftext($img, 10, 0, 100, 63, $colorAlt, $fontAlt, $playerData['total_level']);

imagettftext($img, 10, 0, 15, 76, $color, $font, 'Killed:');
imagettftext($img, 10, 0, 65, 76, $colorAlt, $fontAlt, $mobs . ' ' . $mobsLocale . ' & ' . $players . ' ' . $playersLocale);

imagettftext($img, 10, 0, 15, 89, $color, $font, 'Status:');
imagettftext($img, 10, 0, 70, 89, $colorStatus, $fontAlt, $currentServer);

// Render
header('Content-type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
header('Pragma: no-cache');

imagepng($img) or die('Imaged failed to load');
imagedestroy($img);