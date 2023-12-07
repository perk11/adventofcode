<?php

$input = fopen('input', 'rb');
$total1 = 0;
$total2 = 0;

while ($line = fgets($input)) {
    $game = parseLine($line);
    if ($game->isValid()) {
        $total1 += $game->id;
    }

    $total2 += $game->getMinCubesPower();
}

function parseLine($line): Game
{
    $game = new Game();
    $gameString = strtok($line, ':');
    $gameStringParts = explode(' ', $gameString);
    $game->id = $gameStringParts[1];
    $game->plays = [];

    while ($playString = strtok(';')) {
        $game->plays[] = parsePlayString($playString);
    }

    return $game;
}

function parsePlayString(string $playString): Play
{
    $play = new Play();
    $playStringParts = explode(',', $playString);
    foreach ($playStringParts as $playStringPart) {
        $playStringSubParts = explode(' ', trim($playStringPart));
        switch ($playStringSubParts[1]) {
            case 'red':
                $play->red = $playStringSubParts[0];
                break;
            case 'blue':
                $play->blue = $playStringSubParts[0];
                break;
            case 'green':
                $play->green = $playStringSubParts[0];
                break;
            default:
                throw new \Exception("Unknown color " . $playStringSubParts[1]);
        }
    }

    return $play;
}


echo $total1 . "\n";
echo $total2 . "\n";

class Game
{
    public int $id;

    /** @var Play[] */
    public array $plays;

    public function isValid()
    {
        foreach ($this->plays as $play) {
            if (!$play->isValid()) {
                return false;
            }
        }

        return true;
    }

    public function getMinCubesPower(): int
    {
        $maxBlue = 0;
        $maxRed = 0;
        $maxGreen = 0;
        foreach ($this->plays as $play) {
            $maxBlue = max($play->blue, $maxBlue);
            $maxRed = max($play->red, $maxRed);
            $maxGreen = max($play->green, $maxGreen);
        }

        return $maxBlue * $maxRed * $maxGreen;
    }
}

class Play
{
    private static int $totalRedCubes = 12;

    private static int $totalGreenCubes = 13;

    private static int $totalBlueCubes = 14;

    public int $red = 0;

    public int $green = 0;

    public int $blue = 0;

    public function isValid(): bool
    {
        if ($this->red > self::$totalRedCubes) {
            return false;
        }
        if ($this->green > self::$totalGreenCubes) {
            return false;
        }
        if ($this->blue > self::$totalBlueCubes) {
            return false;
        }

        return true;
    }
}
