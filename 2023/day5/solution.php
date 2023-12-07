<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL. $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $score = 0;
    $line = fgets($input);
    $line = trim(str_replace('seeds: ', '', $line));
    $seeds = explode(' ', $line);
    $map = null;
    /** @var Map[] $maps */
    $maps = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (str_contains($line,'map:')) {
            if ($map !== null) {
                $maps[] = $map;
            }
            $map = new Map();
            $map->name = $line;
            continue;
        }

        $range = new Range();
        $rangeParts = explode(' ', trim($line));
        $range->destinationStart = $rangeParts[0];
        $range->sourceStart = $rangeParts[1];
        $range->length = $rangeParts[2];
        $map->ranges[] = $range;
    }
    $maps[] = $map;

    $minValue = PHP_FLOAT_MAX;
    foreach ($seeds as $seed) {
        $sourceSeed = $seed;
        foreach ($maps as $map) {
            $seed = $map->getMappedValue($seed);
        }
        $minValue = min($seed, $minValue);
        echo "$sourceSeed - $seed\n";
    }
echo $minValue ."\n";

}


class Map
{
    public string $name;
    /** @var Range[] */
    public array $ranges = [];

    public function getMappedValue(int $value): int
    {
        foreach ($this->ranges as $range) {
            if ($range->sourceStart<= $value && $value <= $range->getSourceEnd()) {
                return $range->getMappedValue($value);
            }

        }

        return $value;

//        throw new \Exception("$value value not found in any ranges");
    }
}

class Range
{
    public int $destinationStart;
    public int $sourceStart;
    public int $length;

    public function getSourceEnd(): int
    {
        return $this->sourceStart + $this->length;
    }

    public function getMappedValue($value): int
    {
        if ($value <$this->sourceStart || $value > $this->getSourceEnd()) {
            throw new \Exception("$value not in range");
        }

        return  $value - $this->sourceStart + $this->destinationStart;
    }
}
