<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $line = fgets($input);
    $line = trim($line);
    strtok($line, ':');
    $times = trim(strtok(':'));
    $times = str_replace(' ', '', $times);

    $timesArray = explode(' ', $times);
    $line = fgets($input);
    $line = trim($line);
    strtok($line, ':');

    $distances = trim(strtok(':'));
    $distances = str_replace(' ', '', $distances);
    $distancesArray = explode(' ', $distances);
    $records = [];
    for ($i = 0; $i < count($timesArray); $i++) {
        $record = new Record();
        $record->time = $timesArray[$i];
        $record->distance = $distancesArray[$i];
        $records[] = $record;
    }

    $mult = 1;
    foreach ($records as $record) {
        for ($holdTime = 0; $holdTime <= $record->time; $holdTime++) {
            $race = new Race();
            $race->holdTime = $holdTime;
            $race->duration = $record->time;

            $distance = $race->getDistance();
            if ($distance > $record->distance) {
                $record->beatCombinations++;
            }
        }
        $mult = $record->beatCombinations * $mult;
    }
    echo $mult . PHP_EOL;
}

class Record
{
    public int $time;

    public int $distance;

    public int $beatCombinations = 0;
}

class Race
{
    public int $holdTime;

    public int $duration;

    public function getDistance(): int
    {
        $speed = $this->holdTime;
        $time = $this->duration - $this->holdTime;

        return $time * $speed;
    }
}
