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
$player = protectInput(isset($_GET['player']) ? $_GET['player'] : '');

// Check if the user is premium
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mojang.com/users/profiles/minecraft/" . $player);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_exec($ch);

if (!isset($_GET['player']) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200)
    die('Error: Player is not premium');

// Themes
$theme = protectInput(isset($_GET['theme']) ? $_GET['theme'] : 0);
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
    case 0:
    default:
        $theme = 'img/dirt.png';
        break;
}

$img = imagecreatefrompng($theme);

// Get player data from the api and decode it
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.wynncraft.com/public_api.php?action=playerStats&command=" . $player);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$playerData = curl_exec($ch);
$playerData = json_decode($playerData);

if (isset($playerData->error))
    die('Error: Player not logged in Wynncraft stats');


// Handle no skin
$playerSkin = $player;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://skins.minecraft.net/MinecraftSkins/" . $player . ".png");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 404) {
    $playerSkin = 'char';
}

// Get avatar and merge it with the background
$avatar = file_get_contents('https://minotar.net/bust/' . $playerSkin . '/100.png');
$avatar = imagecreatefromstring($avatar);

imagesetbrush($img, $avatar);
imageline($img, imagesx($img) / 1.15, imagesy($img) / 1.5, imagesx($img) / 1.15, imagesy($img) / 1.5, IMG_COLOR_BRUSHED);

// Prepare rank colour formatting
switch ($playerData->rank) {
    case 'Moderator':
        $colorRank = imagecolorallocate($img, 255, 170, 0);
        break;
    case 'Administrator':
        $colorRank = imagecolorallocate($img, 170, 0, 0);
        break;
    case 'Media':
        $colorRank = imagecolorallocate($img, 170, 0, 170);
        break;
    default:
        $colorRank = imagecolorallocate($img, 220, 220, 220);
}
$rank = $playerData->rank;
if($playerData->tag != "" ) {
    $rank = $playerData->tag;
    switch($playerData->tag){
        case 'VIP':
            $colorRank = imagecolorallocate($img, 0, 170, 0);
            break;
        case 'VIP+':
            $colorRank = imagecolorallocate($img, 0, 195, 255);
            break;

    }
}

if($playerData->veteran) {
    $rank = "Veteran";
    $colorRank = imagecolorallocate($img, 0, 195, 255);
}

// Handle current server
if (strstr($playerData->current_server, 'WC')) {
    $currentServer = 'Playing on Server ' . $playerData->current_server;
    $colorStatus = imagecolorallocate($img, 0, 170, 0); // Green if online
} else {
    $currentServer = 'Offline or in Lobby';
    $colorStatus = imagecolorallocate($img, 170, 0, 0); // Red if offline
}

// Handle kills
$mobs = convertNum($playerData->global->mobs_killed);
$players = convertNum($playerData->global->pvp_kills);

if ($mobs == 1) {
    $mobsLocale = 'mob';
} else {
    $mobsLocale = 'mobs';
}

if ($players == 1) {
    $playersLocale = 'player';
} else {
    $playersLocale = 'players';
}

// Handle playtime plural
if ($playerData->playtime == 1) {
    $playtimeLocale = 'hour';
} else {
    $playtimeLocale = 'hours';
}

// Font Variables
$font = dirname(__FILE__) . '/DroidSans-Bold.ttf';
$color = imagecolorallocate($img, 250, 250, 250);
$fontAlt = dirname(__FILE__) . '/DroidSans.ttf';
$colorAlt = imagecolorallocate($img, 10, 10, 10);

// Echo Messages
imagettftext($img, 10, 0, 15, 35, $color, $font, 'Rank:');
imagettftext($img, 10, 0, 65, 35, $colorRank, $fontAlt, $rank);

imagettftext($img, 10, 0, 15, 50, $color, $font, 'Playtime:');
imagettftext($img, 10, 0, 85, 50, $colorAlt, $fontAlt, $playerData->playtime . ' ' . $playtimeLocale);

imagettftext($img, 10, 0, 15, 63, $color, $font, 'Total Level:');
imagettftext($img, 10, 0, 100, 63, $colorAlt, $fontAlt, $playerData->global->total_level);

imagettftext($img, 10, 0, 15, 76, $color, $font, 'Killed:');
imagettftext($img, 10, 0, 65, 76, $colorAlt, $fontAlt, $mobs . ' ' . $mobsLocale . ' & ' . $players . ' ' . $playersLocale);

imagettftext($img, 10, 0, 15, 89, $color, $font, 'Status:');
imagettftext($img, 10, 0, 70, 89, $colorStatus, $fontAlt, $currentServer);


imagettftext($img, 10, 0, 323, 89, $color, $font, $player);

// Render
header('Content-type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
header('Pragma: no-cache');

imagepng($img) or die('Imaged failed to load');
imagedestroy($img);
