<?php

ini_set('memory_limit', '16G');
foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $total = 0;

    $rows = [];
    $line = fgets($input);
        $line = trim($line);
        $sequences = explode(',', $line);
        foreach ($sequences as $sequence) {
            $chars = str_split($sequence);
            $currentValue = 0;
            foreach ($chars as $char) {
                $asciiCode = ord($char);
                $currentValue += $asciiCode;
                $currentValue *=17;
                $currentValue = $currentValue %256;
            }
            $total += $currentValue;
        }



    echo $total . PHP_EOL;
}
