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
        for ($z = $this->start->z - 1; ; $z--) {
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
        $zDiff = $this->end->z - $this->start->z;
        $this->start->z = $z + 1;
        $this->end->z = $z + 1 + $zDiff;
    }

    public function countBricksThatWouldFall(FallMap $map): int
    {
        $supportedBricks = $this->getDirectlySupportedBricks($map);
        if (count($supportedBricks) === 0) {
            return 0;
        }
        $supportedBricks = array_unique($supportedBricks);

        $bricksSupportedThisIteration = $supportedBricks;
        $allBricksThatCouldFall = $supportedBricks;
        while (count($bricksSupportedThisIteration) > 0) {
            $bricksDiscoveredThisIteration = [];
            foreach ($bricksSupportedThisIteration as $supportedBrick) {
                $bricksSupportedBySupportedBrick = $supportedBrick->getDirectlySupportedBricks($map);
                $bricksDiscoveredThisIteration = [
                    ...$bricksDiscoveredThisIteration,
                    ...$bricksSupportedBySupportedBrick,
                ];
            }
            $bricksSupportedThisIteration = array_unique($bricksDiscoveredThisIteration);
            $allBricksThatCouldFall = [...$allBricksThatCouldFall, ...$bricksSupportedThisIteration];
        }
        $allBricksThatCouldFall = array_unique($allBricksThatCouldFall);
        $thisBrickIndex = array_search($this, $allBricksThatCouldFall, true);
        if ($thisBrickIndex !== false) {
            unset($allBricksThatCouldFall[$thisBrickIndex]);
        }
        $countBefore = count($allBricksThatCouldFall);
        $countAfter = $countBefore;
        $definitelyNotFallingBricks = [];
        while (true) {
            $countBefore = $countAfter;
            foreach ($allBricksThatCouldFall  as $brick) {
                if ($brick->start->z === 0) {
                    $definitelyNotFallingBricks[] = $brick;
                }
                $supportingBricks = $brick->getBricksSupportingThisBrick($map);
                foreach ($supportingBricks as $supportingBrick) {
                    if ($supportingBrick === $this) {
                        continue;
                    }
                    if ($supportingBrick->start->z === 0) {
                        $definitelyNotFallingBricks[] = $brick;
                    }
                    if (!in_array($supportingBrick, $allBricksThatCouldFall, true)) {
                        $definitelyNotFallingBricks[] = $brick;
                    }
                }
            }
            $allBricksThatCouldFall = array_filter($allBricksThatCouldFall,
                static fn(Brick $brick) => !in_array($brick, $definitelyNotFallingBricks, true)
            );

            $countAfter = count($allBricksThatCouldFall);
            if ($countBefore === $countAfter) {
                break;
            }
        }

        return count($allBricksThatCouldFall);
    }

    public function __toString(): string
    {
        return $this->start . '~' . $this->end;
    }

    /**
     * @param FallMap $map
     * @return Brick[]
     */
    private function getDirectlySupportedBricks(FallMap $map): array
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

        return $supportedBricks;
    }

    /** @return Brick[] */
    private function getBricksSupportingThisBrick(FallMap $map): array
    {
        $supportingBricks = [];
        for ($x = $this->start->x; $x <= $this->end->x; $x++) {
            for ($y = $this->start->y; $y <= $this->end->y; $y++) {
                if (isset($map->fullBlocks[$x][$y][$this->start->z - 1])) {
                    $supportingBrick =  $map->fullBlocks[$x][$y][$this->start->z - 1];
                    if ($supportingBrick === $this) {
                        continue;
                    }
                    $supportingBricks[] = $supportingBrick;
                }
            }
        }

        return $supportingBricks;
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
    }

    $total = 0;
    foreach ($bricks as $brick) {
        $total += $brick->countBricksThatWouldFall($fallMap);
    }

    echo $total . PHP_EOL;
}

class FallMap
{
    public array $fallenBricks = [];

    /** @var Brick[][][] */
    public array $fullBlocks = [];

    public function addBrick(Brick $brick): void
    {
        $this->fallenBricks[(string)$brick->start] = $brick;

        for ($x = $brick->start->x; $x <= $brick->end->x; $x++) {
            for ($y = $brick->start->y; $y <= $brick->end->y; $y++) {
                for ($z = $brick->start->z; $z <= $brick->end->z; $z++) {
                    $this->fullBlocks[$x][$y][$z] = $brick;
                }
            }
        }
    }
}
