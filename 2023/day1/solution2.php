<?php

    $map = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5,
        'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9,
        '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
        '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9
    ];
$input = fopen('input', 'rb');
$total = 0;
while ($line = fgets($input)) {
	echo $line;
	$firstDigit = findFirstDigit($line);
	echo $firstDigit;
	$lastDigit = findLastDigit($line);
	echo $lastDigit;
	if ($firstDigit === null || $lastDigit === null) {
		die("error on line $line$firstDigit - $lastDigit\n");
	}
	$lineSum = $firstDigit . $lastDigit;
	echo  "\n";
	$total += $lineSum;
}
echo $total . "\n";

function convertToNumber($word) {

    return isset($map[$word]) ? $map[$word] : null;
}



function findFirstDigit($str) {
	global $map;

	for ($i=1, $iMax = strlen($str); $i<= $iMax; $i++) {
		$comparisonString = substr($str, 0, $i);
        $results = [];
        foreach ($map as $key => $value) {
            if (str_ends_with($comparisonString, $key)) {
                $results[] = $value;
            }
        }

        if (count($results) > 0) {
            if (count($results) > 1) {
                die('pp[');
            }
            return $results[0];
        }
	}
    return null;
}

function findLastDigit($str) {
	if ($str === '') {
		return null;
	}
	global $map;
	for ($i=1, $iMax = strlen($str); $i<= $iMax; $i++) {
		$comparisonString = substr($str, -$i);
		foreach ($map as $key => $value) {
			if (str_starts_with($comparisonString, $key)) {
                return $value;
            }
		}
//		if(array_key_exists($comparisonString, $map)) {
//			return $map[$comparisonString];
//		}
//		if (strlen($comparisonString) > 1) {
//			$lastDigit=findLastDigit(substr($comparisonString, 0, -1));
//			if ($lastDigit !== null) {
//				return $lastDigit;
//			}
//		}
	}
    return null;
}
