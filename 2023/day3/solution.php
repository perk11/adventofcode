<?php

$input = fopen('input', 'rb');
$chars = [];
$x = 0;
$maxLength = 0;
while ($line = fgets($input)) {
    $line = trim($line);
    $maxLength = max($maxLength, strlen($line));
    $y = 0;
    foreach (str_split($line) as $char) {
        $chars[$x][$y] = $char;
        $y++;
    }
    $x++;
}
$emptyRow = [];
for ($i = 0; $i < $maxLength; $i++) {
    $emptyRow[$i] = '.';
}
array_unshift($chars, $emptyRow);
$chars[] = $emptyRow;
$numbers = [];
for ($x = 1; $x < count($chars) - 1; $x++) {
    $readingNumber = false;
    $number = null;
    for ($y = 0; $y < $maxLength; $y++) {
        $currentChar = $chars[$x][$y];
        if ($readingNumber) {
            if ($currentChar === '.' || !ctype_digit($currentChar)) {
                $readingNumber = false;
                $number->endY = $y - 1;
                $numbers[] = $number;
            } else {
                $number->value .= $currentChar;
            }
        } else {
            if ($currentChar === '.') {
                continue;
            }
            if (!ctype_digit($currentChar)) {
                continue;
            }

            $readingNumber = true;
            $number = new Number();
            $number->x = $x;
            $number->startY = $y;
            $number->value = $currentChar;
        }
    }
    if ($readingNumber) {
        $readingNumber = false;
        $number->endY = $y;
        $numbers[] = $number;
    }
}
$total = 0;
/** @var Number[] $numbersByRow */
$numbersByRow = [];
for($x =0, $xMax = count($chars); $x < $xMax; $x++) {
    $numbersByRow[$x] = [];
}
foreach ($numbers as $number) {
    if ($number->isValid()) {
//        echo 'R' . $number->x . 'C' . $number->startY. ':' . $number->value . "\n";
        $total += (int)$number->value;
        $numbersByRow[$number->x][] = $number;
    }
}
//echo $total;


$gears = [];
for ($x = 1; $x < count($chars) - 1; $x++) {
    for ($y = 0; $y < $maxLength; $y++) {
        if ($chars[$x][$y] === '*') {
            $gear = new Gear();
            $gear->x = $x;
            $gear->y = $y;
            $gears[] = $gear;
        }
    }
}
$gearTotal = 0;
foreach ($gears as $gear) {
    $adjacentNumbers = $gear->getAdjacentNumbers();
    if (count($adjacentNumbers) !== 2) {
        continue;
    }

    $gearTotal += $adjacentNumbers[0]->value * $adjacentNumbers[1]->value;
}

echo $gearTotal . "\n";
$a = 1;

class Gear
{
    public int $x;

    public int $y;

    public function getAdjacentNumbers(): array
    {
        $numbers = [];
        global $maxLength;
        $minX = max(1, $this->x - 1);
        $maxX = $this->x + 1;
        $minY = max(0, $this->y - 1);
        $maxY = min($maxLength, $this->y + 1);
        for ($x = $minX; $x <= $maxX; $x++) {
            for ($y = $minY; $y <= $maxY; $y++) {
                $numberAtCoordinates = $this->getNumberAtCoordinates($x, $y);
                if ($numberAtCoordinates === null) {
                    continue;
                }
                foreach ($numbers as $number) {
                    if ($number->isSame($numberAtCoordinates)) {
                        continue 2;
                    }
                }
                $numbers[] = $numberAtCoordinates;
            }
        }
        return $numbers;
    }

    private function getNumberAtCoordinates(int $x, int $y): ?Number
    {
        global $numbersByRow;
        foreach ($numbersByRow[$x] as $number) {
            if ($number->startY <= $y && $number->endY >= $y) {
                return $number;
            }
        }

        return null;
    }
}

class Number
{
    public $startY;

    public $endY;

    public $x;

    public $value;

    public function isSame(Number $number): bool
    {
        return $number->startY === $this->startY && $number->endY === $this->endY && $number->x === $this->x;
    }

    public function isValid(): bool
    {
        if (!is_numeric($this->value)) {
            return false;
        }
        global $maxLength;
        global $chars;
        $checkAdjacentYStart = max(0, $this->startY - 1);
        $checkAdjacentYEnd = min($maxLength - 1, $this->endY + 1);
        if ($this->doesRowContainSymbols($this->x - 1, $checkAdjacentYStart, $checkAdjacentYEnd)) {
            return true;
        }
        if ($this->doesRowContainSymbols($this->x + 1, $checkAdjacentYStart, $checkAdjacentYEnd)) {
            return true;
        }
        if ($this->startY > 0) {
            if ($this->doesRowContainSymbols($this->x, $this->startY - 1, $this->startY - 1)) {
                return true;
            }
        }

        if ($this->endY < $maxLength) {
            if ($this->doesRowContainSymbols($this->x, $this->endY + 1, $this->endY + 1)) {
                return true;
            }
        }

        return false;
    }

    public function doesRowContainSymbols(int $x, int $startY, int $endY)
    {
        global $chars;
        for ($y = $startY; $y <= $endY; $y++) {
            if ($chars[$x][$y] !== '.') {
                return true;
            }
        }

        return false;
    }
}
