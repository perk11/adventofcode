<?php

class Vector
{
    public int $x;

    public int $y;

    public int $z;

    public function __toString(): string
    {
        return $this->x . ',' . $this->y . ',' . $this->z;
    }

    public function equalsTo(Vector $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y && $this->z === $other->z;
    }
}

class Brick
{
    public Vector $start;

    public Vector $end;

    public function __clone()
    {
        $this->start = clone $this->start;
        $this->end = clone $this->end;
    }

    public static function fromStartEndCoords(array $startCoords, array $endCoords): self
    {
        $brick = new self();
        $brick->start = new Vector();
        $brick->start->x = $startCoords[0];
        $brick->start->y = $startCoords[1];
        $brick->start->z = $startCoords[2];

        $brick->end = new Vector();
        $brick->end->x = $endCoords[0];
        $brick->end->y = $endCoords[1];
        $brick->end->z = $endCoords[2];


        return $brick;
    }

    public function makeFall(FallMap $map): void
    {
        for ($z = $map->maxZ; ; $z--) {
            $isSpotEmpty = true;
            for ($x = $this->start->x; $x <= $this->end->x; $x++) {
                for ($y = $this->start->y; $y <= $this->end->y; $y++) {
                    if (isset($map->fullBlocks[$x][$y][$z])) {
                        $isSpotEmpty = false;
                        break 2;
                    }
                }
            }

            if (!$isSpotEmpty || $z === 0) {
                break;
            }
        }
        echo "$this has fallen to ";
        $zDiff = $this->end->z - $this->start->z;
        $this->start->z = $z + 1;
        $this->end->z = $z + 1 + $zDiff;
        echo "$this\n";
//        $map->printTopDownPerspective();
    }

    public function isSafeToDisintegrate(FallMap $map): bool
    {
        /** @var Brick[] $supportedBricks */
        $supportedBricks = [];
        for ($x = $this->start->x; $x <= $this->end->x; $x++) {
            for ($y = $this->start->y; $y <= $this->end->y; $y++) {
                if (isset($map->fullBlocks[$x][$y][$this->end->z + 1])) {
                    $supportedBricks[] = $map->fullBlocks[$x][$y][$this->end->z + 1];
                }
            }
        }
        if (count($supportedBricks) === 0) {
            return true;
        }
        $pretendDisintegrateMap = clone $map;
        $pretendDisintegrateMap->removeBrick($this);
        $pretendDisintegrateMap->maxZ = $this->end->z;

        foreach ($supportedBricks as $supportedBrick) {
            $supportedBrickCopyTestedForFall = clone $supportedBrick;
            $supportedBrickCopyTestedForFall->makeFall($pretendDisintegrateMap);
            if ($supportedBrickCopyTestedForFall->start->z !== $supportedBrick->start->z) {
                return false;
            }
//            if (!$supportedBrickCopyTestedForFall->equalsTo($supportedBrick)) {
//                return false;
//            }
        }

        return true;
    }

    private function equalsTo(Brick $other): bool
    {
        return $this->start->equalsTo($other->start) && $this->end->equalsTo($other->end);
    }

    public function __toString(): string
    {
        return $this->start . '~' . $this->end;
    }
}

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    /** @var Brick[] $bricks */
    $bricks = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $lineParts = explode('~', $line);
        $startCoords = explode(",", $lineParts[0]);
        $endCoords = explode(",", $lineParts[1]);
        $brick = Brick::fromStartEndCoords($startCoords, $endCoords);
        $bricks[] = $brick;
    }

    usort($bricks, static function (Brick $a, Brick $b) {
        return $a->start->z <=> $b->start->z;
    });
    $fallMap = new FallMap();
    foreach ($bricks as $brick) {
        $brick->makeFall($fallMap);
        $fallMap->addBrick($brick);
//        $fallMap->printTopDownPerspective();
    }

    echo "Starting pretending\n";
    $total = 0;
    foreach ($bricks as $brick) {
        if ($brick->isSafeToDisintegrate($fallMap)) {
            $total++;
        } else {
            echo "Therefore $brick can not be disintegrated\n";
        }
    }

    echo $total . PHP_EOL;
}

class FallMap
{
    public array $fallenBricks = [];

    /** @var Brick[][][] */
    public array $fullBlocks = [];

    public int $maxZ = 1;

    public function addBrick(Brick $brick): void
    {
        $this->fallenBricks[(string)$brick->start] = $brick;

        $this->maxZ = max($this->maxZ, $brick->end->z);
        for ($x = $brick->start->x; $x <= $brick->end->x; $x++) {
            for ($y = $brick->start->y; $y <= $brick->end->y; $y++) {
                for ($z = $brick->start->z; $z <= $brick->end->z; $z++) {
                    $this->fullBlocks[$x][$y][$z] = $brick;
                }
            }
        }
    }

    public function removeBrick(Brick $brick): void
    {
        for ($x = $brick->start->x; $x <= $brick->end->x; $x++) {
            for ($y = $brick->start->y; $y <= $brick->end->y; $y++) {
                for ($z = $brick->start->z; $z <= $brick->end->z; $z++) {
                    unset($this->fullBlocks[$x][$y][$z]);
                }
            }
        }
        unset($this->fallenBricks[(string)$brick->start]);
    }

    public function printTopDownPerspective(): void
    {
        $twoDimPerspective = [];
        $minX = PHP_INT_MAX;
        $minY = PHP_INT_MAX;
        $maxX = 0;
        $maxY = 0;
        foreach ($this->fullBlocks as $x => $fullBlocksYZArray) {
            $minX = min($x, $minX);
            $maxX = max($x, $maxX);
            foreach ($fullBlocksYZArray as $y => $fullBlocksZArray) {
                $minY = min($y, $minY);
                $maxY = max($y, $maxY);
                foreach ($fullBlocksZArray as $z => $block) {
                    if (isset($twoDimPerspective[$x][$y])) {
                        $twoDimPerspective[$x][$y] = max($twoDimPerspective[$x][$y], $block->end->z);
                    } else {
                        $twoDimPerspective[$x][$y] = $block->end->z;
                    }
                }
            }
        }

        for ($y = $minY; $y <= $maxY; $y++) {
            for ($x = $minX; $x <= $maxX; $x++) {
                if (isset($twoDimPerspective[$x][$y])) {
                    echo str_pad($twoDimPerspective[$x][$y], 4, ' ', STR_PAD_LEFT);
                } else {
                    echo "   0";
                }
            }
            echo "\n";
        }
    }
}
