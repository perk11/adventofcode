<?php

ini_set('memory_limit', '512M');

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $total = 0;

    $rows = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $rows[] = str_split($line);
    }
    gc_disable(); //this saves ~0.7 seconds

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
    $priorityQueue = new SplPriorityQueue();
    $priorityQueue->insert($startingPosition1, 0);
    $priorityQueue->insert($startingPosition2, 0);

    $visitedPositions = [];
    while (!$priorityQueue->isEmpty()) {
        /** @var Position $currentPosition */
        $currentPosition = $priorityQueue->extract();
        $positionKey = $currentPosition->getKey();
        if (array_key_exists($positionKey, $visitedPositions)) {
            continue;
        }

        $visitedPositions[$positionKey] = $currentPosition;
        if ($currentPosition->x === Position::$maxX && $currentPosition->y === Position::$maxY) {
            echo $currentPosition->tracePath() . "\n";
            echo "\nFound min heat loss: " . $currentPosition->totalHeatLoss . "\n";
            break;
        }


        foreach ($currentPosition->getPossibleNextPositions() as $nextPosition) {
            $priorityQueue->insert($nextPosition, -$nextPosition->totalHeatLoss);
        }
    }
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
        return $this->x . ',' . $this->y . ',' . $this->straightMovesBefore . ',' . $this->direction;
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
            ($this->direction !== Direction::Right && $this->direction !== Direction::Left && $this->x >= 4)) {
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
            ($this->direction !== Direction::Left && $this->direction !== Direction::Right && self::$maxX - $this->x >= 4)) {
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

        return $positions;
    }

    /** This method is for debug only */
    public function tracePath(): string
    {
        $path = $this->getKey() . ',' . $this->totalHeatLoss . "\n";
        $position = $this;
        while ($position->previousPosition !== null) {
            $xDifference = $position->x - $position->previousPosition->x;
            $yDifference = $position->y - $position->previousPosition->y;
            if (abs($xDifference) > 1 || abs($yDifference) > 1) {
                $heatDifference = 0;
                if (abs($xDifference) > 1) {
                    if ($xDifference > 0) {
                        $range = range($position->x - 1, $position->previousPosition->x);
                    } else {
                        $range = range($position->x + 1, $position->previousPosition->x);
                    }
                    foreach ($range as $x) {
                        $position2 = clone $position;
                        $position2->x = $x;
                        $heatDifference += self::$map[$position2->y][$x];
                        $position2->totalHeatLoss -= $heatDifference;

                        $path = $position2->getKey() . ',' . $position2->totalHeatLoss . ",surrogate\n" . $path;
                    }
                } else {
                    if ($yDifference > 0) {
                        $range = range($position->y - 1, $position->previousPosition->y);
                    } else {
                        $range = range($position->y + 1, $position->previousPosition->y);
                    }
                    foreach ($range as $y) {
                        $position2 = clone $position;
                        $position2->y = $y;
                        $heatDifference += self::$map[$y][$position2->x];
                        $position2->totalHeatLoss -= $heatDifference;
                        $path = $position2->getKey() . ',' . $position2->totalHeatLoss . ",surrogate\n" . $path;
                    }
                }
            } else {
                $path = $position->previousPosition->getKey(
                    ) . ',' . $position->previousPosition->totalHeatLoss . "\n" . $path;
            }
            $position = $position->previousPosition;
        }

        return $path;
    }
}
