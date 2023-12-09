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
        $predictedValue = $history->predictValue();
        echo $predictedValue . PHP_EOL;
        $sum += $predictedValue;
    }


    echo $sum . PHP_EOL;
}

class History
{
    public array $values;

    public History $difference;

    public History $parent;

    public function calculateDifferences(): History
    {
        $difference = new History();
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

    public function predictValue(): int
    {
//        if ($this->parent !== null) {
//            throw new \Exception('wrong parent');
//        }
        if ($this->isAllZeroes()) {
            return 0;
        }
        $difference = $this->calculateDifferences();
        $difference->parent = $this;

        return end($this->values) + $difference->predictValue();
    }

}
