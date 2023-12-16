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
    $beam = new Beam();
    $beam->x = 0;
    $beam->y = 0;
    $beam->direction = BeamDirection::Right;
    $beams = [$beam];

    $energizedTiles = [];
    while (count($beams) > 0) {
        $newBeams = [];
        foreach ($beams as $beam) {
            $tileKey = $beam->getTileKey();
//            echo $tileKey . "," . $beam->direction .",";
            $energizedTiles[] = $tileKey;
            $newBeams = array_merge($newBeams, $beam->processTile($rows[$beam->y][$beam->x]));
        }
//        echo "\n";
//        echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
//        sleep(1);
//        system('clear');
//        for ($y=0; $y<=Beam::$maxY; $y++) {
//            for ($x =0; $x<=Beam::$maxX; $x++) {
//                if (in_array($x. '_'. $y, $energizedTiles, true)) {
//                    echo '#';
//                } else {
//                    echo $rows[$y][$x];
//                }
//            }
//            echo "\n";
//        }
//        echo "\n";
        $beams = $newBeams;
    }

    $energizedTiles = array_unique($energizedTiles);


    echo count($energizedTiles) . PHP_EOL;
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
        return $this->getTileKey() . '_'. $this->direction;
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

        $beams =  array_filter($beams, static function (Beam $beam) {
            if ($beam->x < 0 || $beam->x > self::$maxX || $beam->y < 0 || $beam->y > self::$maxY) {
                return false;
            }

            if (in_array($beam->getBeamKey(), self::$visitedTiles ,true)) {

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
