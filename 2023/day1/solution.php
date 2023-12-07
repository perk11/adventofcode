<?php

$input = fopen('input', 'rb');
$total = 0;
while ($line = fgets($input)) {
	// echo $line;
	$firstDigit = findFirstDigit($line);
	$lastDigit = findLastDigit($line);
	if ($firstDigit === null || $lastDigit === null) {
		die("error");
	}
	$lineSum = $firstDigit . $lastDigit;
	$total+= $lineSum;
}
echo $total . "\n";


function findFirstDigit($str) {
    for ($i = 0; $i < strlen($str); $i++) {
        if (is_numeric($str[$i])) {
            return $str[$i];
        }
    }
    return null;
}

function findLastDigit($str) {
    for ($i = strlen($str) - 1; $i >= 0; $i--) {
        if (is_numeric($str[$i])) {
            return $str[$i];
        }
    }
    return null;
}