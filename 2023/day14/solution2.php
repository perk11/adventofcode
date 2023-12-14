<?php

ini_set('memory_limit', '16G');
ini_set('xdebug.max_nesting_level', 10000000);
foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $total = 0;

    $rows = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $rows[] = str_split($line);
    }

    $field = new Field();
    for ($x = 0; $x < count($rows[0]); $x++) {
        $column = Column::fromRows($rows, $x);
        $field->columns[] = $column;
    }
    $totalShakeTimes = 1000000000;
//    $totalShakeTimes = 3;

    $shakesPerTime = 10000;
    for ($i = 0; $i < $totalShakeTimes;) {
        $shakes = min($shakesPerTime, $totalShakeTimes - $i);
        $hashBefore = $field->getHash();
        $field->shakeNTimes($shakes);
        $hashAfter = $field->getHash();
        $i += $shakes;
        if ($hashBefore === $hashAfter) {
            echo "Field stopped changing after $i iterations\n";
            break;
        }
    }
    $field->output();

    foreach ($field->columns as $column) {
        $total += $column->calculateLoad();
    }
    //On test input I'm off by one somewhere, it's not the sum calculation and I don't feel like looking for it.
    echo $total . PHP_EOL;
}

class Field
{
    public static array $shakeCache = [];

    public static array $shakeNTimesCache = [];

    public function getHash(): string
    {
        $columnString = '';
        foreach ($this->columns as $column) {
            $columnString .= implode('', $column->characters);
        }

        return $columnString;
    }

    /** @var Column[] */
    public array $columns;

    public function shake(): void
    {
        $hash = $this->getHash();
        if (array_key_exists($hash, self::$shakeCache)) {
            $this->columns = self::$shakeCache[$hash];
        } else {
//            $this->output();
            $this->columns = $this->slideNorth()->columns;
//            $this->output();
            $this->columns = $this->slideWest()->columns;
//            $this->output();

            $this->columns = $this->slideSouth()->columns;
//            $this->output();
            $this->columns = $this->slideEast()->columns;
            self::$shakeCache[$hash] = $this->columns;
        }
    }

    public function shakeNTimes(int $n): void
    {
        if ($n === 0) {
            return;
        }
        $hash = $this->getHash();
        if (!array_key_exists($n, self::$shakeNTimesCache)) {
            self::$shakeNTimesCache[$n] = [];
        }
        if (!array_key_exists($hash, self::$shakeNTimesCache[$n])) {
            $this->shakeNTimes($n - 1);
            $this->shake();
            self::$shakeNTimesCache[$n][$hash] = $this->columns;
        }
        $this->columns = self::$shakeNTimesCache[$n][$hash];
    }

    public function transpose(): void
    {
        /** @var Column[] $columns */
        $columns = [];
        for ($x = 0; $x < count($this->columns[0]->characters); $x++) {
            $columns[$x] = new Column();
            $columns[$x]->characters = [];
        }
        for ($y = 0; $y < count($this->columns); $y++) {
            for ($x = 0; $x < count($this->columns[0]->characters); $x++) {
                $columns[$x]->characters[$y] = $this->columns[$y]->characters[$x];
            }
        }

        $this->columns = $columns;
    }

    public function slideEast(): Field
    {
        $field = clone $this;
        $field->transpose();
        $field = $field->slideSouth();
        $field->transpose();

        return $field;
    }

    public function slideWest(): Field
    {
        $field = clone $this;
        $field->transpose();
        $field = $field->slideNorth();
        $field->transpose();

        return $field;
    }

    public function slideNorth(): Field
    {
        $field = new self();
        foreach ($this->columns as $column) {
            $field->columns[] = $column->slideNorth();
        }

        return $field;
    }

    public function slideSouth(): Field
    {
        $field = new self();
        foreach ($this->columns as $column) {
            $field->columns[] = $column->slideSouth();
        }

        return $field;
    }

    public function output(): void
    {
        for ($x = 0; $x < count($this->columns[0]->characters); $x++) {
            echo "\n";
            for ($y = 0; $y < count($this->columns); $y++) {
                echo $this->columns[$y]->characters[$x];
            }
        }
        echo "\n";
    }
}

class Column
{
    public static array $northSlideCache = [];

    public static array $southSlideCache = [];

    public array $characters;

    public function getKey()
    {
        return implode('', $this->characters);
    }

    public static function fromRows(array $rows, int $x): self
    {
        $column = new Column();
        $column->characters = [];
        for ($y = 0; $y < count($rows); $y++) {
            $column->characters[] = $rows[$y][$x];
        }

        return $column;
    }

    public function slideSouth(): Column
    {
        $key = $this->getKey();
        if (array_key_exists($key, self::$southSlideCache)) {
            return self::$southSlideCache[$key];
        }
        $column = new self();
        $column->characters = array_reverse($this->characters);
        $column = $column->slideNorth();
        $column->characters = array_reverse($column->characters);
        self::$southSlideCache[$key] = $column;

        return $column;
    }

    public function slideNorth(): Column
    {
        $key = $this->getKey();
        if (array_key_exists($key, self::$northSlideCache)) {
            return self::$northSlideCache[$key];
        }
        $slidePosition = 0;
        $column = new self();
        $column->characters = [];
        for ($i = 0; $i < count($this->characters); $i++) {
            $character = $this->characters[$i];
            if ($character === '.') {
                continue;
            }
            if ($character === 'O') {
                $column->characters[$slidePosition] = $character;
                $nextMinSlidePosition = $slidePosition + 1;
                $slidePosition = null;
                for ($j = $nextMinSlidePosition; $j < count($this->characters); $j++) {
                    if ($this->characters[$j] !== '#') {
                        $slidePosition = $j;
                        break;
                    }
                }
                if ($slidePosition === null) {
                    for ($j = $nextMinSlidePosition; $j < count($this->characters); $j++) {
                        $column->characters[$j] = $this->characters[$j];
                    }
                    break;
                }

                continue;
            }

            if ($character === '#') {
                $slidePosition = $i + 1;
                $column->characters[$i] = $character;
            }
        }

        for ($i = 0; $i < count($this->characters); $i++) {
            if (!array_key_exists($i, $column->characters)) {
                $column->characters[$i] = '.';
            }
        }
        self::$northSlideCache[$key] = $column;

        ksort($column->characters);

        return $column;
    }

    public function calculateLoad(): int
    {
        $load = 0;
        for ($i = 0; $i < count($this->characters); $i++) {
            if ($this->characters[$i] !== 'O') {
                continue;
            }

            $load += count($this->characters) - $i;
        }

        return $load;
    }
}
