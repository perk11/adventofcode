<?php

ini_set('memory_limit', '60G');

class Position
{
    public static int $maxX;

    public static int $maxY;

    public int $y;

    public int $x;

    public function __toString()
    {
        return $this->x . ',' . $this->y;
    }

    public function getAvailablePositions(array $map): array
    {
        $positions = [];
        $candidates = [
            [$this->x + 1, $this->y],
            [$this->x - 1, $this->y],
            [$this->x, $this->y + 1],
            [$this->x, $this->y - 1],
        ];
        foreach ($candidates as $candidate) {
            [$x, $y] = $candidate;
            if ($x < 0 || $y < 0 || $x > self::$maxX || $y > self::$maxY) {
                continue;
            }
            $tileValue = $map[$y][$x];
            if ($tileValue === '.' || $tileValue === 'O' || $tileValue === 'S') {
                $position = new Position();
                $position->x = $x;
                $position->y = $y;
                $positions[] = $position;
            }
        }

        return $positions;
    }

    public static function debugMap(array $map): void
    {
        for ($y = 0; $y < Position::$maxY; $y++) {
            for ($x = 0; $x < Position::$maxX; $x++) {
                echo $map[$y][$x];
            }
            echo "\n";
        }
        echo "\n";
    }
}

foreach (['input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $map = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $map[] = str_split($line);
    }
    Position::$maxY = count($map) - 1;
    Position::$maxX = count($map[0]) - 1;
    foreach ($map as $y => $row) {
        if (in_array('S', $row, true)) {
            $startingPosition = new Position();
            $startingPosition->x = array_search('S', $row, true);
            $startingPosition->y = $y;
            break;
        }
    }
    if (!isset($startingPosition)) {
        die("Start not found");
    }
    $countOfStepsOnEvenFullyFilledField = 0;
    $positions = [$startingPosition];
    for ($i=1; $i<=Position::$maxY; $i++) {
        echo "Step $i. Positions: " . count($positions) ."\n";
        $availablePositions = [];
        foreach ($positions as $position) {
            $availablePositions = [...$availablePositions,...$position->getAvailablePositions($map)];
            $mapClone = $map;
        }
        $availablePositions = array_unique($availablePositions);
        foreach ($availablePositions as $availablePosition) {
            $mapClone[$availablePosition->y][$availablePosition->x] = 'O';
        }
        $positions = $availablePositions;
    }
    $positionsByCoordinates = [];
    foreach ($positions as $position) {
        $positionsByCoordinates[$position->y][$position->x] = $position;
    }

    $targetStep = 26501365;

    for ($x = 0; $x <= Position::$maxX; $x++) {
        if ($x % 2 === 1) {
            continue;
        }

        for ($y = 0; $y <= Position::$maxY; $y++) {
            if ($y % 2 === 1) {
                continue;
            }

            if (isset($positionsByCoordinates[$y][$x])) {
                $countOfStepsOnEvenFullyFilledField++;
            }
        }
    }
    $countOfStepsOnOddFullyFilledField = 0;
    for ($x = 0; $x <= Position::$maxX; $x++) {
        if ($x % 2 === 0) {
            continue;
        }
        for ($y = 0; $y <= Position::$maxY; $y++) {
            if ($y % 2 === 0) {
                continue;
            }
            if (isset($positionsByCoordinates[$y][$x])) {
                $countOfStepsOnOddFullyFilledField++;
            }
        }
    }

    $fullInternalSquareSideLength = $targetStep;
    $fullyFilledFields = floor($targetStep / Position::$maxY) + 1;
    $onFinalField = $targetStep % Position::$maxY . "\n";


    for ($x = 0; $x <= Position::$maxX; $x++) {
        for ($y = 0; $y <= Position::$maxY; $y++) {
            if ($y % 2 === 0 && $x % 2 === 1) {
                continue;
            }
            if (!isset($positionsByCoordinates[$y][$x])) {
                continue;
            }
            if ($y % 2 === 0) {
                $innerEvenPositions[$y][$x] = $positionsByCoordinates[$y][$x];
            } else {
                $innerOddPositions[$y][$x] = $positionsByCoordinates[$y][$x];
            }
        }
    }
    $countOfStepsOnOuterEvenField = 0;
    $countOfStepsOnOuterOddField = 0;

    foreach ($positions as $position) {
            if (($position->x - 65) * ($position->y - 65) > 0) {
                continue;
            }

            if ($position->y % 2 === 0 && $position->x % 2 === 1) {
                continue;
            }

            if ($position->y %2 === 0) {
                $countOfStepsOnOuterEvenField++;
            } else {
                $countOfStepsOnOuterOddField++;
            }
    }

    $fullyFilledEvenFields = ($targetStep + 1);
    $fullyFilledOddFields = $targetStep;

    echo (int) ($countOfStepsOnEvenFullyFilledField * $fullyFilledEvenFields +
        $countOfStepsOnOddFullyFilledField * $fullyFilledOddFields +
        ($targetStep + 1) * $targetStep * ($countOfStepsOnOuterEvenField + $countOfStepsOnOuterOddField));
    echo "\n";

//746320477 too low
//775060512 too low
//775064314 too low
// 775068116 wrong
//     //$x- $startingPosition->x + $y- $startingPosition->y <= $targetStep + odd
//    for ($y=$fullyFilledFields+1; $y<=Position::maxY; $y++) {
//        for ($x=0;)
//    }


    echo $startingPosition->x . ' ' . $startingPosition->y . PHP_EOL;
}
