<?php

class SeedNumbers
{
    public int $start;

    public int $end;

    public Range $transformRange;

    public function __toString(): string
    {
        return $this->start. "-" . $this->end;
    }

}
ini_set('memory_limit', '100G');
foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $score = 0;
    $line = fgets($input);
    $line = trim(str_replace('seeds: ', '', $line));
    $seedRanges = explode(' ', $line);
    $seedNumbersList = [];
    for ($i = 0; $i < count($seedRanges); $i++) {
        $rangeStart = (int)$seedRanges[$i];
        $i++;
        $rangeLength = (int)$seedRanges[$i];
        $seedNumbers = new SeedNumbers();
        if ( $rangeStart + $rangeLength > PHP_INT_MAX) {
            die('123');
        }
        $seedNumbers->start = $rangeStart;
        $seedNumbers->end = $rangeStart + $rangeLength;
        $seedNumbersList[] = $seedNumbers;
    }
    $map = null;
    /** @var Map[] $maps */
    $maps = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (str_contains($line, 'map:')) {
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
    usort($map->ranges, function (Range $a, Range $b) {
        return $a->sourceStart <=> $b->sourceStart;
    });
    $maps[] = $map;

    $minValue = PHP_FLOAT_MAX;
    $zeroSeedNumbers = new SeedNumbers();
    $zeroSeedNumbers->start = 35205517;
    $zeroSeedNumbers->end = 4203284203;
    $originalseedNumbersList = $seedNumbersList;
    $seedNumbersList = [$zeroSeedNumbers];
    foreach ($maps as $map) {
        echo $map->name;
        echo PHP_EOL;
        foreach ($seedNumbersList as $seedNumbers) {
            echo $seedNumbers->start . '-' . $seedNumbers->end . ',';
        }
        echo PHP_EOL;
        foreach($map->ranges as $range) {
            echo $range->sourceStart . '-' . $range->getSourceEnd() .':' . $range->getOffset() . ', ';
        }
        echo PHP_EOL;
        $seedNumbersList = $map->transformSeedNumberList($seedNumbersList);
    }
    echo PHP_EOL;
    foreach ($seedNumbersList as $seedNumbers) {
        echo $seedNumbers->start . '-' . $seedNumbers->end . ',';
    }
    echo PHP_EOL;

    foreach ($seedNumbersList as $seedNumbers)
    {
        $value = $seedNumbers->start;
        foreach ($maps as $map) {
            $value = $map->getMappedValue($value);
        }
        $minValue = min($value, $minValue);
        echo $seedNumbers->start .' ' . $value . "\n";
        $value = $seedNumbers->end;
        foreach ($maps as $map) {
            $value = $map->getMappedValue($value);
        }
        echo $seedNumbers->end .' ' . $value . "\n";

        $minValue = min($value, $minValue);
    }
//    foreach ($seedNumbersList as $seedNumbers) {
//        $minValue = min($seedNumbers->start, $minValue);
//    }
    usort($seedNumbersList, function (SeedNumbers $a,SeedNumbers $b) {
        return $a->start <=> $b->start;
    });
//        echo "$sourceSeed - $seed\n";


    echo "\n". $minValue . "\n";
}

class Map
{
    public string $name;

    /** @var Range[] */
    public array $ranges = [];

    public function getMappedValue(int $value): int
    {
        foreach ($this->ranges as $range) {
            if ($range->sourceStart <= $value && $value <= $range->getSourceEnd()) {
                return $range->getMappedValue($value);
            }
        }

        return $value;
//        throw new \Exception("$value value not found in any ranges");
    }

    /**
     * @param SeedNumbers[] $seedNumbersList
     * @return void
     */
    public function transformSeedNumberList(array $seedNumbersList): array
    {
        $mappedNumbers = [];
        $unchangedNumbers = $seedNumbersList;
        foreach ($this->ranges as $range) {
            $mappedResult = $range->transformSeedNumbers($unchangedNumbers);
            if ( count($mappedResult->mappedNumbers)>0 ) {
                $numbersChangedThisIteration = array_diff($unchangedNumbers, $mappedResult->unchangedNumbers);
                $newUnchanged = array_diff($mappedResult->unchangedNumbers, $unchangedNumbers);
                echo "Mapped ";
                foreach ($numbersChangedThisIteration as $unchangedNumber) {
                    echo $unchangedNumber .',';
                }
                echo " to ";
                foreach ($mappedResult->mappedNumbers as $mappedNumber) {
                    echo $mappedNumber . ',';
                }
                echo " and  unchanged " ;
                foreach ($newUnchanged as $mappedNumber) {
                    echo $mappedNumber . ',';
                }
                echo " using " . $range->sourceStart . '-' . $range->getSourceEnd() . ':' .$range->getOffset() . "\n";
                echo "Current unchanged: ";
                foreach ($mappedResult->unchangedNumbers as $unchangedNumber) {
                    echo $unchangedNumber . ",";
                }
                echo "\n";
            }
            $unchangedNumbers = $mappedResult->unchangedNumbers;
            $mappedNumbers = array_merge($mappedNumbers, $mappedResult->mappedNumbers);
        }
//        $newList = array_unique($newList);
        $result =  array_merge($unchangedNumbers, $mappedNumbers);
        usort($result, function (SeedNumbers $a,SeedNumbers $b) {
            return $a->start <=> $b->start;
        });

        return $result;
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
        if ($value < $this->sourceStart || $value > $this->getSourceEnd()) {
            throw new \Exception("$value not in range");
        }

        return $value - $this->sourceStart + $this->destinationStart;
    }

    public function getOffset(): int
    {
        return $this->destinationStart - $this->sourceStart;
    }

    /**
     * @param SeedNumbers[] $seedNumbersList
     */
    public function transformSeedNumbers(array $seedNumbersList): MapResult
    {
        $result = new MapResult();
        foreach ($seedNumbersList as $seedNumbers) {
            if ($seedNumbers->end <= $this->sourceStart || $seedNumbers->start >= $this->getSourceEnd()) {
                $result->unchangedNumbers[] = $seedNumbers;
                continue;
            }
            if ($seedNumbers->start >= $this->sourceStart && $seedNumbers->end <= $this->getSourceEnd()) {
                $mappedNumbers = clone $seedNumbers;
                $mappedNumbers->start += $this->getOffset();
                $mappedNumbers->end += $this->getOffset();
                $mappedNumbers->transformRange = $this;
                $result->mappedNumbers[] = $mappedNumbers;
                continue;
            }

            if ($seedNumbers->start < $this->sourceStart) {
                if ( $seedNumbers->end <= $this->getSourceEnd()) {
                    $unchangedNumbers = new SeedNumbers();
                    $unchangedNumbers->start = $seedNumbers->start;
                    $unchangedNumbers->end = $this->sourceStart - 1;
                    $result->unchangedNumbers[] = $unchangedNumbers;

                    $mappedNumbers = new SeedNumbers();
                    $mappedNumbers->start = $this->destinationStart;
                    $mappedNumbers->end = $seedNumbers->end + $this->getOffset();
                } else {
                    $unchangedNumbers1 = new SeedNumbers();
                    $unchangedNumbers1->start = $seedNumbers->start;
                    $unchangedNumbers1->end = $this->sourceStart - 1;
                    $result->unchangedNumbers[] = $unchangedNumbers1;
                    $unchangedNumbers2 = new SeedNumbers();
                    $unchangedNumbers2->start = $this->getSourceEnd()+1;
                    $unchangedNumbers2->end = $seedNumbers->end;
                    $result->unchangedNumbers[] = $unchangedNumbers2;

                    $mappedNumbers = new SeedNumbers();
                    $mappedNumbers->start = $this->destinationStart;
                    $mappedNumbers->end =  $this->destinationStart + $this->length;
                }
                $mappedNumbers->transformRange = $this;
                $result->mappedNumbers[] = $mappedNumbers;
                continue;
            }

            //seednumbers->end>=$this->sourceEnd && seedNumbers->start> $this->sourceStart

            $unchangedNumbers = new SeedNumbers();
            $unchangedNumbers->start = $this->getSourceEnd() + 1;
            $unchangedNumbers->end = $seedNumbers->end;
            $result->unchangedNumbers[] = $unchangedNumbers;

            $mappedNumbers = new SeedNumbers();
            $mappedNumbers->start = $seedNumbers->start + $this->getOffset();
            $mappedNumbers->end = $this->getSourceEnd() + $this->getOffset();
            $mappedNumbers->transformRange = $this;
            $result->mappedNumbers[] = $mappedNumbers;
        }

        return $result;
    }

}
class MapResult
{
    public array $unchangedNumbers = [];
    public array $mappedNumbers = [];
}
