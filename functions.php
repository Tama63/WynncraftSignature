<?php
/**
 * Wynncraft Signature
 *
 * @copyright Wynncraft 2013
 * @author Chris Ireland <ireland63@gmail.com>
 * @license MIT <http://opensource.org/licenses/MIT>
 */

/**
 * Clean a string
 * @param $protectinput
 * @return mixed|string
 */
function protectInput($protectinput){
	$protectinput = stripslashes($protectinput);
	$protectinput = str_replace(array("<", ">", "!", "?", "(", ")"), "", $protectinput);

	return $protectinput;
}

/**
 * Format a number as a human-readable string
 * @param $numrev
 * @return float|string
 */
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