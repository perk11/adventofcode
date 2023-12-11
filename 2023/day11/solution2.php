<?php
ini_set('memory_limit', '100G');
foreach (['testInput', 'input'] as $fileName) {
    if ($fileName === 'testInput') {
        $expansionFactor = 10;
    } else {
        $expansionFactor =1000000;
    }
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $universe = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $universe[] = str_split($line);
    }

    $extraColumnIndexes = [];
    for ($x = 0, $xMax = count($universe[0]); $x < $xMax; $x++) {
        $allDots = true;
        for ($y = 0, $yMax = count($universe); $y < $yMax; $y++) {
            $symbol = $universe[$y][$x];
            if ($symbol !== '.') {
                $allDots = false;
                break;
            }
        }

        if ($allDots) {
            $extraColumnIndexes[] = $x;
        }
    }
    $extraRowIndexes = [];
    foreach ($universe as $index => $row) {
        $onlyDots = true;
        for ($i = 0, $iMax = count($row); $i < $iMax; $i++) {
            if ($row[$i] !== '.') {
                $onlyDots = false;
                break;
            }
        }

        if ($onlyDots) {
            $extraRowIndexes[] = $index;
        }
    }
    $galaxies = [];
    $id = 0;
    foreach ($universe as $rowIndex => $row) {
//        echo implode('', $row) . "\n";
        foreach ($row as $columIndex => $char) {
            if ($char === '#') {
                $xOffset = 0;
                $yOffset = 0;
                foreach ($extraRowIndexes as $extraRowIndex) {
                    if ($extraRowIndex < $rowIndex) {
                        $yOffset += $expansionFactor - 1;
                    }
                }
                foreach ($extraColumnIndexes as $extraColumnIndex) {
                    if ($extraColumnIndex < $columIndex) {
                        $xOffset += $expansionFactor - 1;
                    }
                }
                $galaxy = new Galaxy();
                $galaxy->id = $id;
                $galaxy->x = $columIndex + $xOffset;
                $galaxy->y = $rowIndex + $yOffset;
                $galaxies[] = $galaxy;
                $id++;
            }
        }
    }
    $sum = 0;
    $distances = [];
    foreach ($galaxies as $galaxyA) {

        echo $galaxyA->id . ' '. $galaxyA->x . ' ' . $galaxyA->y. "\n";

        foreach ($galaxies as $galaxyB) {
            if ($galaxyA === $galaxyB) {
                continue;
            }
            if ($galaxyA->id < $galaxyB->id) {
                $pairKey = $galaxyA->id . '_' . $galaxyB->id;
            } else {
                $pairKey = $galaxyB->id . '_' . $galaxyA->id;
            }
            if (array_key_exists($pairKey, $distances)) {
                continue;
            }
            $distances[$pairKey] = $galaxyA->distanceTo($galaxyB);
            $sum += $distances[$pairKey];
        }
    }


    echo $sum . PHP_EOL;
}

class Galaxy
{
    public int $id;

    public int $x;

    public int $y;

    public function distanceTo(Galaxy $galaxy): int
    {
        return abs($galaxy->x - $this->x) + abs($galaxy->y - $this->y);
    }
}

