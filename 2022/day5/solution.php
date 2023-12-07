<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL. $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    while ($line = fgets($input)) {
        $line = trim($line);

    }
    echo $minValue ."\n";

}

