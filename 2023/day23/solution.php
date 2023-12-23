<?php

ini_set('memory_limit', '60G');

enum TileType: string
{
    case Path = '.';
    case Forest = '#';
    case Slope_Up = '^';
    case Slope_Down = 'v';
    case Slope_Left = '<';
    case Slope_Right = '>';
}

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');


    $map = new Map();
    $y = 0;
    while ($line = fgets($input)) {
        $line = trim($line);
        $chars = str_split($line);
        $x = 0;
        foreach ($chars as $char) {
            $tile = new Tile();
            $tile->x = $x;
            $tile->y = $y;
            $tile->type = TileType::from($char);

            $map->addTile($tile);
            $x++;
        }
        $y++;
    }
    Tile::$maxX = count($map->tiles[0]) - 1;
    Tile::$maxY = count($map->tiles) - 1;

    foreach ($map->tiles[0] as $tile) {
        if ($tile->type === TileType::Path) {
            $startingTile = $tile;
            break;
        }
    }
    $lastRow = end($map->tiles);
    foreach ($lastRow as $tile) {
        if ($tile->type === TileType::Path) {
            $endTile = $tile;
            break;
        }
    }
    if (!isset($startingTile, $endTile)) {
        die("Could not find start or end tile");
    }
    $path = new Path();
    $path->visitTile($startingTile);
    $currentPaths = $path->nextTilePaths($map);
    /** @var Path[] $finishedPaths */
    $finishedPaths = [];

    while (count($currentPaths) > 0) {
        $nextPaths = [];
        foreach ($currentPaths as $currentPath) {
            $newPaths = $currentPath->nextTilePaths($map);
            if (count($newPaths) === 1) {
                $currentPaths[] = $newPaths[0];
            } else {
                //we found a branch
                $targetTile = $currentPath->currentTile;
            }
            if ($currentPath->hasEnded()) {
                $finishedPaths[] = $currentPath;
            } else {
                $nextPaths = [...$nextPaths, ...$currentPath->nextTilePaths($map)];
            }
        }

        $currentPaths = $nextPaths;
    }
    $max = 0;
    foreach ($finishedPaths as $finishedPath) {
//        echo $finishedPath->length();
//        echo "\n";
//        $finishedPath->printDebug($map);
//        echo "\n";
        $max = max($max, $finishedPath->length());
    }

    echo $max . PHP_EOL;
}

class Path
{
    /** @var Tile[][] */
    public array $visitedTiles;

    public Tile $currentTile;

    public function visitTile(Tile $tile): void
    {
        $this->currentTile = $tile;
        $this->visitedTiles[$tile->y][$tile->x] = $tile;
    }

    /** @return Path[] */
    public function nextTilePaths(Map $map): array
    {
        $neighbourTiles = $this->currentTile->findNeighbourTiles($map, $this);
        if (count($neighbourTiles) === 0) {
            return [];
        }

        $paths = [];
        foreach ($neighbourTiles as $tile) {
            $clonedPath = clone $this;
            $clonedPath->visitTile($tile);
            $paths[] = $clonedPath;
        }

        return $paths;
    }

    public function length(): int
    {
        $total = -1;
        foreach ($this->visitedTiles as $row) {
            $total += count($row);
        }

        return $total;
    }

    public function hasEnded(): bool
    {
        return $this->currentTile->y === Tile::$maxY;
    }

    public function printDebug(Map $map)
    {
        for ($y = 0; $y <= Tile::$maxY; $y++) {
            for ($x = 0; $x <= Tile::$maxX; $x++) {
                if (isset($this->visitedTiles[$y][$x])) {
                    echo 'O';
                } else {
                    echo $map->tiles[$y][$x]->type->value;
                }
            }
            echo "\n";
        }
    }
}

class Map
{
    /** @var Tile[][] $tiles */
    public array $tiles = [];

    public function addTile(Tile $tile): void
    {
        $this->tiles[$tile->y][$tile->x] = $tile;
    }
}

class Tile
{
    public static int $maxX;

    public static int $maxY;

    public int $x;

    public int $y;

    public TileType $type;

    /** @return Tile[] */
    public function findNeighbourTiles(Map $map, Path $path): array
    {
        switch ($this->type) {
            case TileType::Slope_Up:
                $oneNeighbor = $map->tiles[$this->y - 1][$this->x];
                break;
            case TileType::Slope_Down:
                $oneNeighbor = $map->tiles[$this->y + 1][$this->x];
                break;
            case TileType::Slope_Left:
                $oneNeighbor = $map->tiles[$this->y][$this->x - 1];
                break;
            case TileType::Slope_Right:
                $oneNeighbor = $map->tiles[$this->y][$this->x + 1];
                break;
        }
        if (isset($oneNeighbor)) {
            if (isset($path->visitedTiles[$oneNeighbor->y][$oneNeighbor->x])) {
                return [];
            }

            return [$oneNeighbor];
        }
        $up = [$this->x, $this->y - 1];
        $down = [$this->x, $this->y + 1];
        $left = [$this->x - 1, $this->y];
        $right = [$this->x + 1, $this->y];
        $neighbours = [$up, $down, $left, $right];
        $tiles = [];
        foreach ($neighbours as $neighbour) {
            $x = $neighbour[0];
            $y = $neighbour[1];
            if ($x < 0 || $y < 0 || $x > Tile::$maxX || $y > Tile::$maxY) {
                continue;
            }
            if ($x === $this->x && $y === $this->y) {
                continue;
            }
            if (isset($path->visitedTiles[$y][$x])) {
                continue;
            }
            if ($x === $this->x && $y === $this->y) {
                continue;
            }
            $tile = $map->tiles[$y][$x];
            if (isset($path->visitedTiles[$y][$x])) {
                continue;
            }

            switch ($tile->type) {
                case TileType::Forest:
                    continue 2;
                case TileType::Path:
                case TileType::Slope_Up:
                case TileType::Slope_Down:
                case TileType::Slope_Left:
                case TileType::Slope_Right:
                    $tiles[] = $tile;
                    break;
            }
        }

        return $tiles;
    }
}
