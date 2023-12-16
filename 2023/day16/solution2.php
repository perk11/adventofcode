<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $total = 0;

    $rows = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $rows[] = str_split($line);
    }
    Beam::$maxX = count($rows[0]) - 1;
    Beam::$maxY = count($rows) - 1;
    //Defining corner beams outside of the loop as I initially read the task wrong
    //and this part made more sense in that reading, but could still be reused
    $beam1 = new Beam();
    $beam1->x = 0;
    $beam1->y = 0;
    $beam1->direction = BeamDirection::Right;
    $beam2 = clone $beam1;
    $beam2->direction = BeamDirection::Down;

    $beam3 = new Beam();
    $beam3->x = Beam::$maxX;
    $beam3->y = 0;
    $beam3->direction = BeamDirection::Down;
    $beam4 = clone $beam3;
    $beam4->direction = BeamDirection::Left;

    $beam5 = new Beam();
    $beam5->x = Beam::$maxX;
    $beam5->y = Beam::$maxY;
    $beam5->direction = BeamDirection::Up;
    $beam6 = clone $beam5;
    $beam6->direction = BeamDirection::Left;

    $beam7 = new Beam();
    $beam7->x = 0;
    $beam7->y = Beam::$maxY;
    $beam7->direction = BeamDirection::Up;
    $beam8 = clone $beam7;
    $beam8->direction = BeamDirection::Right;


    $beamCombinations = [$beam1, $beam2, $beam3, $beam4, $beam5, $beam6, $beam7, $beam8];
    for ($i = 1; $i < Beam::$maxX; $i++) {
        $downwardsBeam = new Beam();
        $downwardsBeam->y = 0;
        $downwardsBeam->x = $i;
        $downwardsBeam->direction = BeamDirection::Down;
        $beamCombinations[] = $downwardsBeam;
        $upwardsBeam = new Beam();
        $upwardsBeam->y = Beam::$maxY;
        $upwardsBeam->x = $i;
        $upwardsBeam->direction = BeamDirection::Down;
        $beamCombinations[] = $upwardsBeam;
    }

    for ($y = 1; $y < Beam::$maxY; $y++) {
        $rightBeam = new Beam();
        $rightBeam->x = 0;
        $rightBeam->y = $y;
        $rightBeam->direction = BeamDirection::Right;
        $beamCombinations[] = $rightBeam;

        $leftBeam = new Beam();
        $leftBeam->x = Beam::$maxX;
        $leftBeam->y = $y;
        $leftBeam->direction = BeamDirection::Left;
        $beamCombinations[] = $leftBeam;
    }
    $max = 0;
    foreach ($beamCombinations as $beamCombination) {
        $beams = [$beamCombination];
        $energizedTiles = [];
        Beam::$visitedTiles = [];
        while (count($beams) > 0) {
            $newBeams = [];
            foreach ($beams as $downwardsBeam) {
                $tileKey = $downwardsBeam->getTileKey();
//            echo $tileKey . "," . $beam->direction .",";
                $energizedTiles[] = $tileKey;
                $newBeams = array_merge(
                    $newBeams,
                    $downwardsBeam->processTile($rows[$downwardsBeam->y][$downwardsBeam->x])
                );
            }
//            echo "\n";
//        sleep(1);
//        system('clear');
//            for ($y = 0; $y <= Beam::$maxY; $y++) {
//                for ($x = 0; $x <= Beam::$maxX; $x++) {
//                    if (in_array($x . '_' . $y, $energizedTiles, true)) {
//                        echo '#';
//                    } else {
//                        echo $rows[$y][$x];
//                    }
//                }
//                echo "\n";
//            }
//            echo "\n";
            $beams = $newBeams;
        }

        $energizedTiles = array_unique($energizedTiles);

        $max = max($max, count($energizedTiles));
        echo count($energizedTiles) . PHP_EOL;
    }
    echo $max;
}

//This was initially a Enum, but it didn't compile for some reason
class BeamDirection
{
    public const Up = 'up';
    public const Down = 'down';
    public const Left = 'left';
    public const Right = 'right';
}

class Beam
{
    public static $maxX;

    public static $maxY;

    public int $x;

    public int $y;

    public string $direction;

    public static array $visitedTiles = [];

    public function getBeamKey(): string
    {
        return $this->getTileKey() . '_' . $this->direction;
    }

    public function getTileKey(): string
    {
        return $this->x . '_' . $this->y;
    }

    public function processTile(string $tile,): array
    {
        $mainBeam = clone $this;

        if ($tile === '.' ||
            ($tile === '|' && ($this->direction === BeamDirection::Up || $this->direction === BeamDirection::Down)) ||
            ($tile === '-' && ($this->direction === BeamDirection::Left || $this->direction === BeamDirection::Right))
        ) {
            switch ($this->direction) {
                case BeamDirection::Up:
                    $mainBeam->y--;
                    break;
                case BeamDirection::Down:
                    $mainBeam->y++;
                    break;
                case BeamDirection::Left:
                    $mainBeam->x--;
                    break;
                case BeamDirection::Right:
                    $mainBeam->x++;
                    break;
            }

            $beams = [$mainBeam];
        } elseif ($tile === '|') {
            $secondBeam = clone $mainBeam;
            $mainBeam->y--;
            $mainBeam->direction = BeamDirection::Up;
            $secondBeam->y++;
            $secondBeam->direction = BeamDirection::Down;

            $beams = [$mainBeam, $secondBeam];
        } elseif ($tile === '-') {
            $secondBeam = clone $mainBeam;
            $mainBeam->x--;
            $mainBeam->direction = BeamDirection::Left;
            $secondBeam->direction = BeamDirection::Right;
            $secondBeam->x++;
            $beams = [$mainBeam, $secondBeam];
        } elseif ($tile === '/') {
            switch ($mainBeam->direction) {
                case BeamDirection::Up:
                    $mainBeam->direction = BeamDirection::Right;
                    $mainBeam->x++;
                    break;
                case BeamDirection::Down:
                    $mainBeam->direction = BeamDirection::Left;
                    $mainBeam->x--;
                    break;
                case BeamDirection::Left:
                    $mainBeam->direction = BeamDirection::Down;
                    $mainBeam->y++;
                    break;
                case BeamDirection::Right:
                    $mainBeam->direction = BeamDirection::Up;
                    $mainBeam->y--;
                    break;
            }
            $beams = [$mainBeam];
        } elseif ($tile === '\\') {
            switch ($mainBeam->direction) {
                case BeamDirection::Up:
                    $mainBeam->direction = BeamDirection::Left;
                    $mainBeam->x--;

                    break;
                case BeamDirection::Down:
                    $mainBeam->direction = BeamDirection::Right;
                    $mainBeam->x++;
                    break;
                case BeamDirection::Left:
                    $mainBeam->direction = BeamDirection::Up;
                    $mainBeam->y--;
                    break;
                case BeamDirection::Right:
                    $mainBeam->direction = BeamDirection::Down;
                    $mainBeam->y++;

                    break;
            }
            $beams = [$mainBeam];
        } else {
            throw new \Exception("Unknown tile $tile");
        }

        $beams = array_filter($beams, static function (Beam $beam) {
            if ($beam->x < 0 || $beam->x > self::$maxX || $beam->y < 0 || $beam->y > self::$maxY) {
                return false;
            }

            if (in_array($beam->getBeamKey(), self::$visitedTiles, true)) {
                return false;
            }

            return true;
        });

        foreach ($beams as $beam) {
            self::$visitedTiles[] = $beam->getBeamKey();
        }

        return $beams;
    }
}
