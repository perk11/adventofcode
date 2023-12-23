<?php

ini_set('memory_limit', '60G');
//gc_disable();

enum TileType: string
{
    case Path = '.';
    case Forest = '#';
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
            $tile->type = $char === '#' ? TileType::Forest : TileType::Path;

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
    $currentPaths = [$path];
    /** @var Path[] $finishedPaths */
    $finishedPaths = [];
    $max = 0;

    $i = 0;
    $timeBefore = microtime(true);
    $queue = new SplStack();
    $queue[] = $path;
    while (!$queue->isEmpty()) {
        $currentPath = $queue->pop();
        if ($i % 100000 === 0) {
            echo $i . '/' . $queue->count() . '/' . $max . '/' . (microtime(true) - $timeBefore) . "s\n";
            $timeBefore = microtime(true);
//            gc_collect_cycles();
        }
        if ($currentPath->hasEnded()) {
            $max = max($max, $currentPath->length());
        } else {
            foreach ($currentPath->nextTilePaths($map) as $path) {
                $queue[] = $path;
            }
        }
//        unset($currentPath);


        $i++;
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
            //dead end
            return [];
        }
        $clonedPath = clone $this;
        while (count($neighbourTiles) === 1) {
            $clonedPath->visitTile($neighbourTiles[0]);
            if ($clonedPath->hasEnded()) {
                return [$clonedPath];
            }
            $neighbourTiles = $clonedPath->currentTile->findNeighbourTiles($map, $clonedPath);
        }


        $paths = [];
        foreach ($neighbourTiles as $tile) {
            $clonedPath2 = clone $clonedPath;
            $clonedPath2->visitTile($tile);
            $paths[] = $clonedPath2;
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

    public function printDebug(Map $map): void
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
        $tiles = [];
        $neighbours = [];

        if ($this->x < self::$maxX) {
            $neighbours[] = [$this->x + 1, $this->y];
        }
        if ($this->y < self::$maxY) {
            $neighbours[] = [$this->x, $this->y + 1];
        }
        if ($this->x > 0) {
            $neighbours[] = [$this->x - 1, $this->y];
        }
        if ($this->y > 0) {
            $neighbours[] = [$this->x, $this->y - 1];
        }
        foreach ($neighbours as $neighbour) {
            $x = $neighbour[0];
            $y = $neighbour[1];
            if (isset($path->visitedTiles[$y][$x])) {
                continue;
            }
            $tile = $map->tiles[$y][$x];
            if ($tile->type === TileType::Forest) {
                continue;
            }
            $tiles[] = $tile;
        }

        return $tiles;
    }
}
