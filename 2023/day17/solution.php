<?php
//This took ~18 hours to run but outputted correct solution
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
        }
        $positions = $newPositions;
        gc_collect_cycles();
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

    public function tracePath(): string
    {
        $path = $this->getKey();
        $position =$this->previousPosition;
        while ($position->previousPosition !== null) {
            $path = $position->previousPosition->getKey() .'=>' . $path;
            $position = $position->previousPosition;
        }

        return $path;
    }

    public function getPossibleNextPositions(): array
    {
        if ($this->y === self::$maxX && $this->x === self::$maxY) {
            return [];
        }
        $positions = [];
        //go up
        if ($this->y > 0 && !($this->direction === Direction::Down) && !($this->direction === Direction::Up && $this->straightMovesBefore === 3)) {
            $position = clone $this;
            $position->previousPosition = $this;
            $position->y--;
            if ($this->direction === Direction::Up) {
                $position->straightMovesBefore++;
            } else {
                $position->straightMovesBefore = 1;
                $position->direction = Direction::Up;
            }
            $positions[] = $position;
        }

        //go down
        if ($this->y < self::$maxY && !($this->direction === Direction::Up) && !($this->direction === Direction::Down && $this->straightMovesBefore === 3)) {
            $position = clone $this;
            $position->previousPosition = $this;
            $position->y++;
            if ($this->direction === Direction::Down) {
                $position->straightMovesBefore++;
            } else {
                $position->straightMovesBefore = 1;
                $position->direction = Direction::Down;
            }
            $positions[] = $position;
        }
        //go left
        if ($this->x > 0 && !($this->direction === Direction::Right) && !($this->direction === Direction::Left && $this->straightMovesBefore === 3)) {
            $position = clone $this;
            $position->previousPosition = $this;
            $position->x--;
            if ($this->direction === Direction::Left) {
                $position->straightMovesBefore++;
            } else {
                $position->straightMovesBefore = 1;
                $position->direction = Direction::Left;
            }
            $positions[] = $position;
        }
        //go right
        if ($this->x < self::$maxX && !($this->direction === Direction::Left) && !($this->direction === Direction::Right && $this->straightMovesBefore === 3)) {
            $position = clone $this;
            $position->previousPosition = $this;
            $position->x++;
            if ($this->direction === Direction::Right) {
                $position->straightMovesBefore++;
            } else {
                $position->straightMovesBefore = 1;
                $position->direction = Direction::Right;
            }
            $positions[] = $position;
        }
        foreach ($positions as $position) {
            $position->totalHeatLoss += self::$map[$position->y][$position->x];
        }

        $positionsWithNotMoreHeatLoss = [];
        $newMinFound = [];
        foreach ($positions as $position) {
            $key = $position->getKey();
            if (array_key_exists($key, self::$minHeightLossByBlockAndMovesBefore)) {
                $existingHeatLoss = self::$minHeightLossByBlockAndMovesBefore[$key];
                if ($existingHeatLoss > $position->totalHeatLoss) {
                    //can be probably further optimized by stopping calculations for all the paths starting from this point
                    self::$minHeightLossByBlockAndMovesBefore[$key] = $position->totalHeatLoss;
                    $newMinFound[$position->getKey()] = $position;
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

        //Everything from this point until the return is meant as an optimization, but doesn't seem to work, no positions to be removed are ever found
        $positionsToRemove = [];
        foreach ($newMinFound as $newMinKey => $newMinPosition) {
            foreach ($positionsWithNotMoreHeatLoss as $index => $position) {
                if ($position === $newMinPosition) {
                    continue;
                }
                while ($position->previousPosition !== null) {
                    if ($position->previousPosition->getKey() === $newMinKey) {
                        $positionsToRemove[] = $index;
                        break;
                    }

                    if ($position->totalHeatLoss < $newMinPosition->totalHeatLoss) {
                        break;
                    }
                    $position = $position->previousPosition;
                }
            }
        }
        foreach ($positionsToRemove as $index) {
            unset($positionsWithNotMoreHeatLoss[$index]);
        }

        return $positionsWithNotMoreHeatLoss;
    }

}
