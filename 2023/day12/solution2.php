<?php

ini_set('xdebug.max_nesting_level', '100000');
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
        for ($j=0; $j<count($record->lengths); $j++) {
            //otherwise they will be strings and type-strict comparisons WILL fail
            $record->lengths[$j] = (int) $record->lengths[$j];
        }
        $records[] = $record;
        $i++;
    }
    $total = 0;
    foreach ($records as $record) {
        $record->chars = array_merge(
            $record->chars,
            ['?'],
            $record->chars,
            ['?'],
            $record->chars,
            ['?'],
            $record->chars,
            ['?'],
            $record->chars
        );
        $record->lengths = array_merge(
            $record->lengths,
            $record->lengths,
            $record->lengths,
            $record->lengths,
            $record->lengths
        );
        echo $record->id;
        $valid = $record->countValidCombinationsWithCache();
        echo " $valid\n";
        $total += $valid;
    }


    echo $total . PHP_EOL;
}

class Record
{

    public int $id;

    public array $chars;

    public array $lengths;

    private static array $cacheByCharsAndLengths = [];

    public function getKey(): string
    {
        return implode('', $this->chars) . '_' . implode(',', $this->lengths);
    }

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

    public function countValidCombinationsWithCache(): int
    {
        $key = $this->getKey();
        if (!array_key_exists($key, self::$cacheByCharsAndLengths)) {
            $result = $this->countValidCombinationsForQuestionMarkReplacements();

////          uncomment for debug
//
//            if ($result === 0 && !array_key_exists($key, self::$cacheByCharsAndLengths) && $this->isValid(
//                ) && !in_array('?', $this->chars, true)) {
//                    throw new \Exception("Valid value is treated as invalid: " . $this->getKey());
//                }
//            if ($result === 1 && !array_key_exists($key, self::$cacheByCharsAndLengths) && !$this->isValid(
//                ) && !in_array('?', $this->chars, true)) {
//                    throw new \Exception("Invalid value is treated as valid: " . $this->getKey());
//                }


            self::$cacheByCharsAndLengths[$key] = $result;
        }

        return self::$cacheByCharsAndLengths[$key];
    }

    public function countValidCombinationsForQuestionMarkReplacements(): int
    {
        if (count($this->chars) === 0) {
            return count($this->lengths) === 0;
        }

        $firstChar = $this->chars[0];
        if (count($this->chars) === 1) {
            if ($firstChar === '#') {
                if (count($this->lengths) > 1) {
                    return 0;
                }
                if (count($this->lengths) === 0) {
                    return 0;
                }

                if ($this->lengths[0] !== 1) {
                    return 0;
                }

                return 1;
            }

            if ($firstChar === '.') {
                if (count($this->lengths) === 0) {
                    if (!$this->isValid()) {
                        throw new \Exception(
                            "invalid " . implode('', $this->chars) . ' ' . implode(',', $this->lengths)
                        );
                    }

                    return 1;
                }

                return 0;
            }
        }

        if ($firstChar === '.') {
            $record = new Record();
            $record->chars = array_slice($this->chars, 1);
            $record->lengths = $this->lengths;

            return $record->countValidCombinationsWithCache();
        }
        if ($firstChar === '#') {
            if (count($this->lengths) === 0) {
                return 0;
            }

            for ($i = 0; $i < $this->lengths[0]; $i++) {
                if (!array_key_exists($i, $this->chars)) {
                    //Characters ended before length was reached
                    return 0;
                }
                if ($this->chars[$i] === '.') {
                    //. encountered before length was reached
                    return 0;
                }
                if ($this->chars[$i] === '?') {
                    $record = clone $this;
                    $record->chars[$i] = '#';

                    return $record->countValidCombinationsWithCache();
                }
            }
            if (array_key_exists($this->lengths[0], $this->chars)) {
                if ($this->chars[$this->lengths[0]] === '#') {
                    //next character is also #
                    return 0;
                }
                //remove this sequence of # from the beginning of the string as it doesn't matter
                $record = new Record();
                $record->lengths = array_slice($this->lengths, 1);
                //cutting one extra character for delimiter, doesn't matter if it's '?' or '.'
                $record->chars = array_slice($this->chars, $this->lengths[0] + 1);

                return $record->countValidCombinationsWithCache();
            }

            if (count($this->lengths) > 1) {
                //there are sequences remaining but we reached the end of the string
                return 0;
            }

            //This was the last character
            return 1;
        }

        if ($firstChar === '?') {
            $record1 = clone $this;
            $record1->chars[0] = '#';

            $record2 = clone $this;
            $record2->chars[0] = '.';

            return $record1->countValidCombinationsWithCache() + $record2->countValidCombinationsWithCache();
        }

        throw new \Exception("Invalid first char: $firstChar");
    }
}
