<?php


foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $total = 0;

    $digCommands = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $parts = explode(' ', $line);
        $digCommand = new DigCommand();
        $digCommand->direction = $parts[0];
        $digCommand->distance = $parts[1];
        $digCommand->color = $parts[2];
        $digCommands[] = $digCommand;
    }
    $map = new Map();

    foreach ($digCommands as $digCommand) {
        $map->applyCommand($digCommand);
    }

    echo $map->countTilesInsideLagoon() . "\n";
}

class Direction
{
    public const Up = 'U';
    public const Down = 'D';
    public const Left = 'L';
    public const Right = 'R';
}

class Map
{
    public array $dugTiles = [
        0 => [
            0 => '#',
        ],
    ];

    public int $diggerX = 0;

    public int $diggerY = 0;

    public function applyCommand(DigCommand $digCommand): void
    {
        switch ($digCommand->direction) {
            case Direction::Up:
                for ($y = $this->diggerY - 1; $y >= $this->diggerY - $digCommand->distance; $y--) {
                    $this->dugTiles[$this->diggerX][$y] = '#';
                }
                $this->diggerY -= $digCommand->distance;
                break;
            case Direction::Down:
                for ($y = $this->diggerY + 1; $y <= $this->diggerY + $digCommand->distance; $y++) {
                    $this->dugTiles[$this->diggerX][$y] = '#';
                }
                $this->diggerY += $digCommand->distance;
                break;
            case Direction::Left:
                for ($x = $this->diggerX - 1; $x >= $this->diggerX - $digCommand->distance; $x--) {
                    $this->dugTiles[$x][$this->diggerY] = '#';
                }
                $this->diggerX -= $digCommand->distance;
                break;
            case Direction::Right:
                for ($x = $this->diggerX + 1; $x <= $this->diggerX + $digCommand->distance; $x++) {
                    $this->dugTiles[$x][$this->diggerY] = '#';
                }
                $this->diggerX += $digCommand->distance;
                break;
            default:
                throw new \Exception("Unknown direction");
        }
    }

    public function countTilesInsideLagoon(): int
    {
        $minX = PHP_INT_MAX;
        $maxX = PHP_INT_MIN;
        $minY = PHP_INT_MAX;
        $maxY = PHP_INT_MIN;
        foreach ($this->dugTiles as $rowIndex => $dugRow) {
            $minX = min($rowIndex, $minX);
            $maxX = max($rowIndex, $maxX);
            foreach ($dugRow as $colIndex => $dugTile) {
                $minY = min($minY, $colIndex);
                $maxY = max($maxY, $colIndex);
            }
        }

        $currentTiles = [];
        $inside = false;
        //Add +1 because otherwise we get wrong starting point and I don't want to handle all the edge cases just for this
        for ($y = $minY+1; $y <= $maxY; $y++) {
            for ($x = $minX+1; $x <= $maxX; $x++) {
                if (isset($this->dugTiles[$x][$y])) {
                    $inside = true;
                } elseif ($inside) {
                    $currentTiles = [[$x, $y]];
                    break 2;
                }
            }
        }

        while (count($currentTiles) > 0) {
            $neighbors = [];
            foreach ($currentTiles as $currentTile) {
                $neighbors = [
                    ...$neighbors,
                    ...getTileUnfilledNeighbors($this, $currentTile[0], $currentTile[1], $maxX, $maxY, $minX, $minY),
                ];
            }
            $currentTiles = [];
            foreach ($neighbors as $neighbor) {
                if (!isset($this->dugTiles[$neighbor[0]][$neighbor[1]])) {
                    $currentTiles[] = $neighbor;
                    $this->dugTiles[$neighbor[0]][$neighbor[1]] = '*';
                }
            }
        }

        $count = 0;
        foreach ($this->dugTiles as $row) {
            foreach ($row as $value) {
                $count++;
            }
        }

        for ($y = $minY; $y <= $maxY; $y++) {
            for ($x = $minX; $x <= $maxX; $x++) {
                if (isset($this->dugTiles[$x][$y])) {
                    echo $this->dugTiles[$x][$y];
                } else {
                    echo '.';
                }
            }
            echo "\n";
        }
        return $count;
    }
}

function getTileUnfilledNeighbors(Map $tileMap, int $x, int $y, int $maxX, int $maxY, int $minX, int $minY): array
{
    $neighbors = [];
    for ($neighborX = $x - 1; $neighborX <= $x + 1; $neighborX++) {
        if ($neighborX < $minX || $neighborX > $maxX) {
            continue;
        }
        for ($neighborY = $y - 1; $neighborY <= $y + 1; $neighborY++) {
            if ($neighborX === $x && $y === $neighborY) {
                continue;
            }
            if ($neighborY < $minY || $neighborY > $maxY) {
                continue;
            }

            if (isset($tileMap->dugTiles[$neighborX][$neighborY])) {
                continue;
            }
            $neighbors[] = [$neighborX, $neighborY];
        }
    }

    return $neighbors;
}

class DigCommand
{
    public string $direction;

    public int $distance;

    public string $color;
}
