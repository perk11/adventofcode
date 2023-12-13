<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $x = 0;
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
            $pattern->id = $x;
            $x++;
            if ($line === '') {
                continue;
            }
        }
        $pattern->values[] = str_split($line);
    }
    $patterns[] = $pattern;


    foreach ($patterns as $pattern) {
        $originalVerticalColumn = $pattern->findVerticalReflectionColumn();
        $originalHorizontalRow = $pattern->findHorizontalReflectionRow();
        $maxX = count($pattern->values[0]);
        $maxY = count($pattern->values);

        if ($originalHorizontalRow === null && $originalVerticalColumn === null) {
            throw new \Exception("no original reflection found for pattern " . $pattern->id);
        }
        for ($x = 0; $x < $maxX; $x++) {
            for ($y = 0; $y < $maxY; $y++) {
                $proposedPattern = clone $pattern;
                $proposedPattern->swapPosition($x, $y);

                $horizontalRow = $proposedPattern->findHorizontalReflectionRow($originalHorizontalRow);
                if ($horizontalRow !== null) {
                    echo "Smudge: " . $pattern->id . ": $x,$y\n";
                    echo "New reflection: h$horizontalRow\n";
                    $total += $horizontalRow + 1;
                    continue 3;
                }

                $verticalColumn = $proposedPattern->findVerticalReflectionColumn($originalVerticalColumn);
                if ($verticalColumn !== null) {
                    echo "Smudge " . $pattern->id . ": $x,$y. ";
                    echo "New reflection: v$verticalColumn\n";
                    $total += ($verticalColumn + 1) * 100;
                    continue 3;
                }
            }
        }
        throw new \Exception("no smudge found for pattern " . $pattern->id);
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

    public function findVerticalReflectionColumn(?int $exclude = null): ?int
    {
        $pattern = new Pattern();
        $pattern->id = -$this->id;
        $pattern->values = transpose($this->values);

        return $pattern->findHorizontalReflectionRow($exclude);
    }

    public function findHorizontalReflectionRow(?int $exclude = null): ?int
    {
        $rowLength = count($this->values[0]);
        $columnLength = count($this->values);
        for ($i = 0; $i < $rowLength; $i++) {
            if ($i === $exclude) {
                continue;
            }
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

    public function swapPosition(int $x, int $y): void
    {
        $currentValue = $this->values[$y][$x];
        if ($currentValue === '.') {
            $this->values[$y][$x] = '#';
        } else {
            $this->values[$y][$x] = '.';
        }
    }
}
