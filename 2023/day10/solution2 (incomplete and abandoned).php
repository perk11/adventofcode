<?php

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

class SqueezeCoordinates
{
    public function __construct(public float $x, public float $y)
    {
    }

    public function __toString()
    {
        return $this->x . ',' . $this->y;
    }

    /**
     * @param Tile[][] $tileMaze
     */
    public function isVerticalSqueezePossible(array $tileMaze): bool
    {
        if ($this->x < 0.5) {
            throw new \Exception("Trying to squeeze to the left of the leftmost tile");
        }
        if ($this->x > count($tileMaze[0]) - 1) {
            throw new \Exception("Trying to squeeze to the right of the rightmost tile");
        }
        if ($this->x - ceil($this->x) === 0) {
            throw new \Exception("Wrong squeeze attempt");
        }
        /** @var Tile $leftTile */
        $leftTile = $tileMaze[(int)($this->x - 0.5)];
        /** @var Tile $rightTile */
        $rightTile = $tileMaze[(int)($this->x + 0.5)];
        if (!$leftTile->isMainLoop || !$rightTile->isMainLoop) {
            return true;
        }
        if ($leftTile->pipe === '0' || $rightTile->pipe === '0') {
            return true;
        }

        if ($leftTile->pipe === '|' || $rightTile->pipe === '|') {
            return true;
        }

        if ($leftTile->pipe === '7' || $leftTile->pipe === 'J') {
            return true;
        }

        if ($rightTile->pipe === 'F' || $rightTile->pipe === 'L') {
            return true;
        }


        return false;
    }

    /**
     * @param Tile[][] $tileMap
     */
    public function isHorizontalSqueezePossible(array $tileMap): bool
    {
        if ($this->y < 0.5) {
            throw new \Exception("Trying to squeeze to the top of the topmost tile");
        }
        if ($this->y > count($tileMap) - 1) {
            throw new \Exception("Trying to squeeze to the bottom of the bottommost tile");
        }
        if ($this->y - ceil($this->y) === 0) {
            throw new \Exception("Wrong squeeze attempt");
        }
        /** @var Tile $topTile */
        $topTile = $tileMap[(int)($this->y - 0.5)];
        /** @var Tile $bottomTile */
        $bottomTile = $tileMap[(int)($this->y + 0.5)];
        if (!$bottomTile->isMainLoop || !$topTile->isMainLoop) {
            return true;
        }
        if ($bottomTile->pipe === '0' || $topTile->pipe === '0') {
            return true;
        }

        if ($bottomTile->pipe === '-' || $topTile->pipe === '-') {
            return true;
        }

        if ($topTile->pipe === 'L' || $topTile->pipe === 'J') {
            return true;
        }

        if ($bottomTile->pipe === 'F' || $bottomTile->pipe === '7') {
            return true;
        }


        return false;
    }

    /**
     * @param Tile[][] $tileMaze
     * @return SqueezeCoordinates[]
     */

    public function getDirectlyReachableSqueezeCoordinates(array $tileMaze): array
    {
        $coordinates = [];
        if ($this->isVerticalSqueezePossible($tileMaze)) {
            $coordinates[] = new SqueezeCoordinates($this->x, $this->y - 1);
            $coordinates[] = new SqueezeCoordinates($this->x, $this->y + 1);
        }

        if ($this->isHorizontalSqueezePossible($tileMaze)) {
            $coordinates[] = new SqueezeCoordinates($this->x - 1, $this->y);
            $coordinates[] = new SqueezeCoordinates($this->x + 1, $this->y);
        }

        return $coordinates;
    }

    public function getFirstReachableCoordinates(array $tileMaze): array
    {
        $coordinates = [];
        foreach ($this->getDirectlyReachableSqueezeCoordinates($tileMaze) as $squeezeCoordinate) {
            if ($squeezeCoordinate)
        }
    }

}

$currentlyChecking = [];

class Tile
{
    public Coordinates $coordinates;

    public string $pipe;

    public bool $isMainLoop = false;

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
//        return 0;
        $currentlyChecking = [];
        if ($this->hasPathToOutside($tileMaze)) {
            return '0';
        }

        return 'I';
    }

    /**
     * @return Coordinates[]
     */
    public function getCoordinatesReachableBySqueezeBetweenPipes(array $tileMaze): array
    {
        $squeezableDirections = [];
        if ($this->coordinates->x > 0 && $this->coordinates->y > 0) {
            //top left

            $squeezeCoordinates = new SqueezeCoordinates($this->coordinates->x - 0.5, $this->coordinates->y - 1);
            if ($squeezeCoordinates->isVerticalSqueezePossible($tileMaze)) {
            }
        }
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
                if ($x === $this->coordinates->x && $y = $this->coordinates->y) {
                    $y++;
                    continue;
                }
                if (array_key_exists((string)new Coordinates($x, $y), $currentlyChecking)) {
                    $y++;
                    continue;
                }
                $tile = $tileMaze[$y][$x];
                if ($tile->hasPathToOutside($tileMaze)) {
                    $this->pipe = '0';

                    return true;
                }
            }
        }

        foreach ($this->getCoordinatesReachableBySqueezeBetweenPipes($tileMaze) as $coordinate) {
            if ($tileMaze[$coordinate->y][$coordinate->x]->hasPathToOutside($tileMaze)) {
                return true;
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
    foreach ($tileMaze as $row) {
        foreach ($row as $tile) {
            echo $tile->charRepresentation($tileMaze);
        }
        echo "\n";
    }
}





