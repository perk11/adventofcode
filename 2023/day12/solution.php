<?php

ini_set('memory_limit', '100G');
foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    /** @var Record[] $records */
    $records = [];
    $i = 0;
    while ($line = fgets($input)) {
        $line = trim($line);
        $record = new Record();
        $record->id = $i;
        $lineParts = explode(' ', $line);
        $record->chars = str_split($lineParts[0]);
        $record->lengths = explode(',', $lineParts[1]);
        $records[] = $record;
        $i++;
    }
    $total = 0;
    foreach ($records as $record) {
        $combinations = $record->getPossibleCombinationsForQuestionMarkReplacements();
        foreach ($combinations as $combination) {
            if ($combination->isValid()) {
                echo $combination->id . ' ';
                echo implode('', $combination->chars);
                echo ' ' . implode(',', $combination->lengths);
                echo "\n";
                $total++;
            }
        }
    }


    echo $total . PHP_EOL;
}

class Record
{
    public array $chars;

    public array $lengths;

    public function isValid(): bool
    {
        $lengthIndex = 0;

        for ($i = 0, $iMax = count($this->chars); $i < $iMax; $i++) {
            $char = $this->chars[$i];
            if ($char === '#') {
                if (!array_key_exists($lengthIndex, $this->lengths)) {
                    return false;
                }
                if ($i + $this->lengths[$lengthIndex] > $iMax) {
                    return false;
                }
                for ($j = $i + 1; $j < $i + $this->lengths[$lengthIndex]; $j++) {
                    if ($this->chars[$j] !== '#') {
                        return false;
                    }
                }
                if (($j + 1 <= $iMax) && $this->chars[$j] === '#') {
                    return false;
                }
                $i += $this->lengths[$lengthIndex] - 1;
                $lengthIndex++;
            }
        }
        if ($lengthIndex < count($this->lengths)) {
            return false;
        }

        return true;
    }

    public function getQuestionMarkPositions(): array
    {
        $offset = 0;
        $allpos = [];
        while (($pos = strpos(implode('', $this->chars), '?', $offset)) !== false) {
            $offset = $pos + 1;
            $allpos[] = $pos;
        }

        return $allpos;
    }

    /**
     * @return Record[]
     */
    public function getPossibleCombinationsForQuestionMarkReplacements(): array
    {
        $questionMarkPositions = $this->getQuestionMarkPositions();

        $records = [];
        $iMax = 2 ** count($questionMarkPositions);
        $jMax = count($questionMarkPositions);
        for ($i = 0; $i < $iMax; $i++) {
            $record = clone $this;
            for ($j = 0; $j < $jMax; $j++) {
                $tryingDamaged = $i & (2 ** $j);
                $record->chars[$questionMarkPositions[$j]] = $tryingDamaged ? '#' : '.';
            }
            $records[] = $record;
        }

        return $records;
    }
}
