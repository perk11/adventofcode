<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $histories = [];
    $sum = 0;
    while ($line = fgets($input)) {
        $line = trim($line);
        $history = new History();
        $history->values = explode(' ', $line);
        $history->values = array_map('intval', $history->values);
        $previousValue = $history->calculatePreviousValue();
        echo $previousValue . "\n";
        $sum += $previousValue;
    }


    echo $sum . PHP_EOL;
}

class History
{
    public array $values;

    public History $difference;

    public ?History $parent = null;

    public function calculateDifferences(): History
    {
        $difference = new History();
        $difference->parent = $this;
        for ($i = 0; $i < count($this->values) - 1; $i++) {
            $difference->values[] = $this->values[$i + 1] - $this->values[$i];
        }

        return $difference;
    }

    public function isAllZeroes(): bool
    {
        if (count($this->values) === 0) {
            throw new \Exception("empty history");
        }
        foreach ($this->values as $value) {
            if ($value !== 0) {
                return false;
            }
        }

        return true;
    }

    public function addDifference(History $difference): History
    {
        $history = new History();
        for ($i = 0, $iMax = count($this->values); $i < $iMax; $i++) {
            $history->values[$i] = $this->values[$i] - $difference->values[$i];
        }
        $history->values[] = $this->values[$iMax - 1];

        return $history;
    }

    public function calculatePreviousValue(): int
    {
        $difference = $this->calculateDifferences();
        if ($difference->isAllZeroes()) {
            $previousDifference = null;
            $assumedDifference = clone $difference;
            array_unshift($assumedDifference->values, 0 );
            while ($assumedDifference->parent !== null) {
                $previousDifference = $assumedDifference;
                $assumedDifference = $previousDifference->parent->addDifference($assumedDifference);
                $assumedDifference->parent = $previousDifference->parent->parent;
            }

            if ($previousDifference->values[0] === $assumedDifference->values[1] - $assumedDifference->values[0]) {
                return $assumedDifference->values[0];
            }
        }

        return $difference->calculatePreviousValue();
    }

}
