<?php
class Position
{
    public static int $maxX;
    public static int $maxY;
    public int $y;

    public int $x;
    public function __toString()
    {
        return $this->x .','. $this->y;
    }
    public function getAvailablePositions(array $map): array
    {
        $positions = [];
        $candidates = [
            [$this->x + 1, $this->y],
            [$this->x - 1, $this->y],
            [$this->x, $this->y + 1],
            [$this->x, $this->y - 1],
        ];
        foreach ($candidates as $candidate) {
            [$x, $y] = $candidate;
            if ($x < 0 || $y <0 || $x>self::$maxX || $y>self::$maxY) {
                continue;
            }
            $tileValue = $map[$y][$x];
            if ($tileValue === '.' || $tileValue === 'O' || $tileValue  === 'S') {
                $position = new Position();
                $position->x = $x;
                $position->y = $y;
                $positions[] = $position;
            }
        }

        return $positions;
    }

    public static function debugMap(array $map): void
    {

        for ($y=0; $y<Position::$maxY; $y++) {
            for($x=0; $x<Position::$maxX; $x++) {
                echo $map[$y][$x];
            }
            echo "\n";
        }
        echo "\n";
    }
}


foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $map = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $map[] = str_split($line);
    }
    Position::$maxY = count($map) -1;
    Position::$maxX = count($map[0]) -1;
    foreach ($map as $y => $row) {
        if (array_search('S', $row, true) !== false) {
            $startingPosition = new Position();
            $startingPosition->x = array_search('S', $row, true);
            $startingPosition->y = $y;
            break;
        }
    }
     if(!isset($startingPosition)) {
         die("Start not found");
     }

     $positions= [$startingPosition];
     if ($fileName === 'testInput') {
         $maxI = 6;
     } else {
         $maxI = 64;
     }
     for ($i=1; $i<=$maxI; $i++) {
         echo "Step $i\n";
         $availablePositions = [];
         foreach ($positions as $position) {
             $newPositions = [];
             $availablePositions = [...$availablePositions,...$position->getAvailablePositions($map)];
             $mapClone = $map;
         }
         $availablePositions = array_unique($availablePositions);
         foreach ($availablePositions as $availablePosition) {
             $mapClone[$availablePosition->y][$availablePosition->x] = 'O';
         }
//        Position::debugMap($mapClone);
        $positions = $availablePositions;
     }


    echo count($positions) . PHP_EOL;
}
