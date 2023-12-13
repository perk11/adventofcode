<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $i = 0;
    $total = 0;
    $pattern = null;
    /** @var Pattern[] $patterns */
    $patterns = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        if ($line === '' || $pattern === null) {
            if ($pattern !== null) {
                $patterns[] = $pattern;
            }
            $pattern = new Pattern();
            $pattern->id = $i;
            $i++;
            if ($line === '') {
                continue;
            }
        }
        $pattern->values[] = str_split($line);
    }
    $patterns[] = $pattern;


    foreach ($patterns as $pattern) {
        $verticalColumn = $pattern->findVerticalReflectionColumn();
        if ($verticalColumn !== null) {
            $total += ($verticalColumn + 1) * 100;
        } else {
            $horizontalRow = $pattern->findHorizontalReflectionRow();
            if ($horizontalRow === null) {
                throw new \Exception("Not found reflection");
            }
            $total += $horizontalRow + 1;
        }
    }

    echo $total . PHP_EOL;
}

function transpose($array)
{
    return array_map(null, ...$array);
}

class Pattern
{
    public int $id;

    public array $values;

    public function findVerticalReflectionColumn(): ?int
    {
        $pattern = new Pattern();
        $pattern->id = -$this->id;
        $pattern->values = transpose($this->values);

        return $pattern->findHorizontalReflectionRow();
    }

    public function findHorizontalReflectionRow(): ?int
    {
        $rowLength = count($this->values[0]);
        $columnLength = count($this->values);
        for ($i = 0; $i < $rowLength; $i++) {
            $reflectionLength = min($i + 1, $rowLength - $i - 1);
            if ($reflectionLength === 0) {
                return null;
            }
            for ($k = 0; $k < $columnLength; $k++) {
                for ($j = 0; $j < $reflectionLength; $j++) {
                    if ($this->values[$k][$i + $j + 1] !== $this->values[$k][$i - $j]) {
                        continue 3;
                    }
                }
            }

            return $i;
        }

        return null;
    }
}
