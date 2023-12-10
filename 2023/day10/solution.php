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
foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $sum = 0;
    $maze = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $maze[] = str_split($line);
    }
    /** @var Tile[] $tileMaze */
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
    if ($fileName === 'testInput') {
        $nextCoordinate1->y++;
        $nextCoordinate2->x++;
    } else {
        $nextCoordinate1->x++;
        $nextCoordinate2->x--;
    }
    $possibleStartingPaths = [$nextCoordinate1, $nextCoordinate2];
    foreach ($possibleStartingPaths as $nextCoordinate) {
        $stepsFromStart = 1;
        $visited = [(string)$startingCoordinates => true];
        while (true) {
            $tile = $tileMaze[$nextCoordinate->y][$nextCoordinate->x];
            echo $stepsFromStart . ' ' . $tile->pipe . ' ' . $tile->coordinates .  "\n";
            if ($tile->stepsFromStart === null) {
                $tile->stepsFromStart = $stepsFromStart;
            } else {
                $tile->stepsFromStart = min($stepsFromStart, $tile->stepsFromStart);
            }
            $stepsFromStart++;
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
    $max = 0;
    foreach ($tileMaze as $row) {
        foreach ($row as $tile) {
            if (isset($tile->stepsFromStart)) {
                $max = max($max, $tile->stepsFromStart);
            }
        }
    }

    echo $max . PHP_EOL;
}



class Tile
{
    public Coordinates $coordinates;

    public string $pipe;

    public ?int $stepsFromStart = null;

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
}


