<?php
/**
 * Wynncraft Signature
 *
 * @copyright Wynncraft 2013
 * @author Chris Ireland <ireland63@gmail.com>
 */

// Simple Sanitizier
function protectInput($protectinput){
	$protectinput = stripslashes($protectinput);
	$protectinput = str_replace(array("<", ">", "!", "?", "(", ")"), "", $protectinput);

	return $protectinput;
}

// Tidy num
function convertNum($numrev) {
	if($numrev >= 1000 && $numrev < 1000000) {
		$num = $numrev /1000;
		$num = number_format((float)$num, 1, ".", "");
		$num = $num."k";
	} elseif($numrev >= 1000000) {
		$num = $numrev /1000000;
		$num = number_format((float)$num, 1, ".", "");
		$num = $num."m";
	} else {
		$num = $numrev;
	}
	             
	return $num;
}
