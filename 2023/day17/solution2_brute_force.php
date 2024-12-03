<?php

ini_set('memory_limit', '80G');

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $total = 0;

    $rows = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $rows[] = str_split($line);
    }
    gc_disable();

    Position::$map = $rows;
    Position::$maxX = count($rows[0]) - 1;
    Position::$maxY = count($rows) - 1;
    Position::$lastBlockKey = Position::$maxX . ',' . Position::$maxY;
    Position::$minHeightLossByBlock = [];
    Position::$minHeightLossByBlockAndMovesBefore = [];

    $startingPosition1 = new Position();
    $startingPosition1->x = 0;
    $startingPosition1->y = 0;
    $startingPosition1->straightMovesBefore = 0;
    $startingPosition1->totalHeatLoss = 0;
    $startingPosition1->direction = Direction::Right;
    $startingPosition1->previousPosition = null;
    $startingPosition2 = clone $startingPosition1;
    $startingPosition2->direction = Direction::Down;

    $positions = [$startingPosition1, $startingPosition2];
    $i = 0;
    while (count($positions) > 0) {
        $positionsCount = count($positions);
        echo $i . ' ' . $positionsCount . PHP_EOL;
        $newPositions = [];
        foreach ($positions as $position) {
            $newPositions = [...$newPositions, ...$position->getPossibleNextPositions()];
            unset($position);
        }
        $positions = $newPositions;
//        gc_collect_cycles();
        $i++;
    }

    echo Position::$minHeightLossByBlock[Position::$maxX . ',' . Position::$maxY];
}

class Direction
{
    public const Up = 'up';
    public const Down = 'down';
    public const Left = 'left';
    public const Right = 'right';
}

class Position
{
    public static array $map;

    public static int $maxX;

    public static int $maxY;

    public static array $minHeightLossByBlockAndMovesBefore = [];

    public static array $minHeightLossByBlock = [];

    public int $x;

    public int $y;
    public static string $lastBlockKey;

    public int $straightMovesBefore;

    public string $direction;

    public int $totalHeatLoss;

    public ?Position $previousPosition;

    public function getKey(): string
    {
        return $this->x . '_' . $this->y . ':' . $this->straightMovesBefore . '-' . $this->direction;
    }

    public function getCoordinatesString(): string
    {
        return $this->x . ',' . $this->y;
    }


    public function getPossibleNextPositions(): array
    {
        if ($this->y === self::$maxX && $this->x === self::$maxY) {
            return [];
        }
        $positions = [];
        //go up
        if (($this->direction === Direction::Up && $this->y > 0 && $this->straightMovesBefore < 10)
            || ($this->y >= 4 && $this->direction !== Direction::Down)
        ) {
            $position = clone $this;
            $position->previousPosition = $this;

            if ($this->direction === Direction::Up) {
                $position->y--;
                $position->straightMovesBefore++;
            } else {
                $position->y -= 4;
                $position->straightMovesBefore = 4;
                $position->direction = Direction::Up;
                for ($y = $this->y - 1; $y > $position->y; $y--) {
                    $position->totalHeatLoss += self::$map[$y][$position->x];
                }
            }
            $positions[] = $position;
        }

        //go down
        if (($this->direction === Direction::Down && $this->y < self::$maxY && $this->straightMovesBefore < 10) ||
            ($this->direction !== Direction::Up && $this->direction !== Direction::Down && self::$maxY - $this->y >= 4)) {
            $position = clone $this;
            $position->previousPosition = $this;
            if ($this->direction === Direction::Down) {
                $position->y++;
                $position->straightMovesBefore++;
            } else {
                $position->y += 4;
                $position->straightMovesBefore = 4;
                $position->direction = Direction::Down;
                for ($y = $this->y + 1; $y < $position->y; $y++) {
                    $position->totalHeatLoss += self::$map[$y][$position->x];
                }
            }
            $positions[] = $position;
        }
        //go left
        if (($this->direction === Direction::Left && $this->x > 0 && $this->straightMovesBefore < 10) ||
            ($this->direction !== Direction::Right && $this->direction !==Direction::Left && $this->x >= 4)) {
            $position = clone $this;
            $position->previousPosition = $this;

            if ($this->direction === Direction::Left) {
                $position->x--;
                $position->straightMovesBefore++;
            } else {
                $position->x -= 4;
                $position->straightMovesBefore = 4;
                $position->direction = Direction::Left;
                for ($x = $this->x - 1; $x > $position->x; $x--) {
                    $position->totalHeatLoss += self::$map[$position->y][$x];
                }
            }
            $positions[] = $position;
        }
        //go right
        if (($this->direction === Direction::Right && $this->x < self::$maxX && $this->straightMovesBefore < 10) ||
            ($this->direction !== Direction::Left&& $this->direction !== Direction::Right && self::$maxX - $this->x >= 4)) {
            $position = clone $this;
            $position->previousPosition = $this;
            if ($this->direction === Direction::Right) {
                $position->x++;
                $position->straightMovesBefore++;
            } else {
                $position->x += 4;
                $position->straightMovesBefore = 4;
                $position->direction = Direction::Right;
                for ($x = $this->x + 1; $x < $position->x; $x++) {
                    $position->totalHeatLoss += self::$map[$position->y][$x];
                }
            }
            $positions[] = $position;
        }

        foreach ($positions as $position) {
            $position->totalHeatLoss += self::$map[$position->y][$position->x];
        }

        if (array_key_exists(self::$lastBlockKey, self::$minHeightLossByBlock)) {
            $bestFinalHeatLoss = self::$minHeightLossByBlock[self::$lastBlockKey];
        }
        $positionsWithNotMoreHeatLoss = [];
//        $newMinFound = [];
        foreach ($positions as $position) {
            if (isset($bestFinalHeatLoss) && $position->totalHeatLoss >= $bestFinalHeatLoss) {
                continue;
            }

            $key = $position->getKey();
            if (array_key_exists($key, self::$minHeightLossByBlockAndMovesBefore)) {
                $existingHeatLoss = self::$minHeightLossByBlockAndMovesBefore[$key];
                if ($existingHeatLoss > $position->totalHeatLoss) {
                    //can be probably further optimized by stopping calculations for all the paths starting from this point
                    self::$minHeightLossByBlockAndMovesBefore[$key] = $position->totalHeatLoss;
//                    $newMinFound[$position->getKey()] = $position;
                    $positionsWithNotMoreHeatLoss[] = $position;
                }
            } else {
                self::$minHeightLossByBlockAndMovesBefore[$key] = $position->totalHeatLoss;
                $positionsWithNotMoreHeatLoss[] = $position;
            }

            $coordinatesString = $position->getCoordinatesString();
            if (array_key_exists($coordinatesString, self::$minHeightLossByBlock)) {
                self::$minHeightLossByBlock[$coordinatesString] = min(
                    self::$minHeightLossByBlock[$coordinatesString],
                    $position->totalHeatLoss
                );
            } else {
                self::$minHeightLossByBlock[$coordinatesString] = $position->totalHeatLoss;
            }
        }
        return $positionsWithNotMoreHeatLoss;
    }

}
