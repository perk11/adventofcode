<?php

ini_set('xdebug.max_nesting_level', 20000);

class Coordinates
{
    public function __construct(public int $x, public int $y)
    {
    }

    public function __toString()
    {
        return $this->x . ',' . $this->y;
    }

}

$currentlyChecking = [];

class Tile
{
    public Coordinates $coordinates;

    public string $pipe;

    public bool $isMainLoop = false;

    public bool $original = true;
    public function __clone()
    {
        $this->coordinates = clone $this->coordinates;
        $this->original = false;
    }

    /**
     * @return Coordinates[]
     */
    public function getAdjacentPipeSegmentCoordinates(): array
    {
        switch ($this->pipe) {
            case '|':
                return [
                    new Coordinates($this->coordinates->x, $this->coordinates->y + 1),
                    new Coordinates($this->coordinates->x, $this->coordinates->y - 1),
                ];
            case '-':
                return [
                    new Coordinates($this->coordinates->x + 1, $this->coordinates->y),
                    new Coordinates($this->coordinates->x - 1, $this->coordinates->y),
                ];

            case 'L':
                return [
                    new Coordinates($this->coordinates->x, $this->coordinates->y - 1),
                    new Coordinates($this->coordinates->x + 1, $this->coordinates->y),
                ];
            case 'J':
                return [
                    new Coordinates($this->coordinates->x, $this->coordinates->y - 1),
                    new Coordinates($this->coordinates->x - 1, $this->coordinates->y),
                ];
            case '7':
                return [
                    new Coordinates($this->coordinates->x - 1, $this->coordinates->y),
                    new Coordinates($this->coordinates->x, $this->coordinates->y + 1),
                ];
            case 'F':
                return [
                    new Coordinates($this->coordinates->x + 1, $this->coordinates->y),
                    new Coordinates($this->coordinates->x, $this->coordinates->y + 1),
                ];
        }

        throw new \Exception("Unknown pipe: " . $this->pipe);
    }

    public function charRepresentation(array $tileMaze): string
    {
        global $currentlyChecking;
        if ($this->isMainLoop) {
            return $this->pipe;
        }
//        if (!$this->original) {
//            return ' ';
//        }
//        return 0;
        $currentlyChecking = [];
        if ($this->hasPathToOutside($tileMaze)) {
            return '0';
        }
            return 'I';

    }

    public function isAtTheEdge(array $tileMaze): bool
    {
        if ($this->coordinates->x === 0 || $this->coordinates->y === 0) {
            return true;
        }
        if ($this->coordinates->y === count($tileMaze) - 1) {
            return true;
        }
        if ($this->coordinates->x === count($tileMaze[0]) - 1) {
            return true;
        }

        return false;
    }

    /**
     * @param Tile[][] $tileMaze
     * @return bool
     */
    public function hasPathToOutside(array $tileMaze): bool
    {
        global $currentlyChecking;
//        echo "Checking " . $this->coordinates . "\n";
        if ($this->isMainLoop) {
            return false;
        }
        if ($this->pipe === '0') {
            return true;
        }
        if ($this->isAtTheEdge($tileMaze)) {
            return true;
        }

//        return false;
        $currentlyChecking[(string)$this->coordinates] = true;
        $maxX = min($this->coordinates->x + 1, count($tileMaze[0]) - 1);
        $maxY = min($this->coordinates->y + 1, count($tileMaze) - 1);
        for ($x = $this->coordinates->x - 1; $x <= $maxX; $x++) {
            for ($y = $this->coordinates->y - 1; $y <= $maxY; $y++) {
                if ($x === $this->coordinates->x && $y === $this->coordinates->y) {
//                    $y++;
                    continue;
                }
                if (array_key_exists((string)new Coordinates($x, $y), $currentlyChecking)) {
//                    $y++;
                    continue;
                }
                $tile = $tileMaze[$y][$x];
                if ($tile->hasPathToOutside($tileMaze)) {
                    $this->pipe = '0';

                    return true;
                }
            }
        }

        return false;
    }
}

foreach (['testInput2', 'testInput3', 'testInput4', 'input'] as $fileName) {
//foreach (['testInput2'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $sum = 0;
    $maze = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $maze[] = str_split($line);
    }
    /** @var Tile[][] $tileMaze */
    $tileMaze = [];
    for ($y = 0, $yMax = count($maze); $y < $yMax; $y++) {
        $row = $maze[$y];
        $tileMaze[$y] = [];
        for ($x = 0, $xMax = count($row); $x < $xMax; $x++) {
            if ($row[$x] === 'S') {
                $startingCoordinates = new Coordinates($x, $y);
            }

            $tile = new Tile();
            $tile->coordinates = new Coordinates($x, $y);
            $tile->pipe = $row[$x];
            $tileMaze[$y][$x] = $tile;
        }
    }

    $nextCoordinate1 = clone $startingCoordinates;

    $nextCoordinate2 = clone $startingCoordinates;
    $startingTile = $tileMaze[$startingCoordinates->y][$startingCoordinates->x];
    $startingTile->isMainLoop = true;

    if ($fileName === 'testInput2' || $fileName === 'testInput3') {
        $nextCoordinate1->y++;
        $nextCoordinate2->x++;
        $startingTile->pipe = 'F';
    } elseif ($fileName === 'testInput4') {
        $nextCoordinate1->y++;
        $nextCoordinate2->x--;
        $startingTile->pipe = '7';
    } else {
        $nextCoordinate1->x++;
        $nextCoordinate2->x--;
        $startingTile->pipe = '-';
    }

    $possibleStartingPaths = [$nextCoordinate1, $nextCoordinate2];
    foreach ($possibleStartingPaths as $nextCoordinate) {
        $stepsFromStart = 1;
        $visited = [(string)$startingCoordinates => true];
        while (true) {
            $tile = $tileMaze[$nextCoordinate->y][$nextCoordinate->x];
//            echo $stepsFromStart . ' ' . $tile->pipe . ' ' . $tile->coordinates .  "\n";
            $tile->isMainLoop = true;
//            $stepsFromStart++;
            $adjacentCoordinates = $tile->getAdjacentPipeSegmentCoordinates();
            $nextCoordinate = null;
            foreach ($adjacentCoordinates as $adjacentCoordinate) {
                if (!array_key_exists((string)$adjacentCoordinate, $visited)) {
                    $nextCoordinate = $adjacentCoordinate;
                }
            }
            if ($nextCoordinate === null) {
                break;
            }


            $visited[(string)$tile->coordinates] = true;
        }
    }

    $doubledMaze = [];
    for ($y = 0, $yMax = count($tileMaze); $y < $yMax; $y++) {
        for ($x = 0, $xMax = count($tileMaze[0]); $x < $xMax; $x++) {
            $tile = $tileMaze[$y][$x];
            $newTile = clone $tile;
            $newTile->original = true;
            $newTile->coordinates->x *= 2;
            $newTile->coordinates->y *= 2;
            $tileToTheRight = clone $newTile;
            $tileToTheRight->coordinates->x++;
            $tileToTheRight->pipe = '.';
            $tileToTheRight->isMainLoop = false;
            $tileToTheBottom = clone $newTile;
            $tileToTheBottom->coordinates->y++;
            $tileToTheBottom->pipe = '.';
            $tileToTheBottom->isMainLoop  = false;
            $tileToTheBottomRight = clone $newTile;
            $tileToTheBottomRight->coordinates->y++;
            $tileToTheBottomRight->coordinates->x++;
            $tileToTheBottomRight->pipe = '.';
            $tileToTheBottomRight->isMainLoop = false;

            if ($newTile->isMainLoop) {
                switch ($tile->pipe) {
                    case 'L':
                    case '-':
                        $tileToTheRight->pipe = '-';
                        $tileToTheRight->isMainLoop = true;
                        break;
                    case '7':
                    case '|';
                        $tileToTheBottom->pipe = '|';
                        $tileToTheBottom->isMainLoop = true;
                        break;
                    case 'F':
                        $tileToTheRight->pipe = '-';
                        $tileToTheRight->isMainLoop = true;
                        $tileToTheBottom->pipe = '|';
                        $tileToTheBottom->isMainLoop = true;
                        break;
                    case '.':
                    case 'I':
                    case 'J':
                        break;
                    default:
                        throw new \Exception("unknown tile: " . $tile->pipe);
                }
            }
            foreach ([$newTile, $tileToTheRight, $tileToTheBottom, $tileToTheBottomRight] as $placedTile) {
                $doubledMaze[$placedTile->coordinates->y][$placedTile->coordinates->x] = $placedTile;
            }
        }
    }

    /** @var Tile[][] $doubledMaze */
    $total = 0;
    foreach ($doubledMaze as $row) {
        foreach ($row as $tile) {
            if($tile->original) {
                echo $tile->charRepresentation($doubledMaze);
            }
            if ($tile->original && !$tile->isMainLoop && !$tile->hasPathToOutside($doubledMaze)) {
                $total++;
            }

        }
        if($tile->coordinates->y %2 ==0) {
            echo "\n";
        }
    }
    echo "\n$total\n";
}






