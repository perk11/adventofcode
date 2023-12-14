<?php

ini_set('memory_limit', '16G');
foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $total = 0;

    $rows = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $rows[] = str_split($line);
    }

    $columns = [];
    $tiltedColumns = [];
    for ($x = 0; $x < count($rows[0]); $x++) {
        $column = Column::fromRows($rows, $x);
        $columns[] = $column;
        $tiltedColumn = $column->slideNorth();
        $tiltedColumns[] = $tiltedColumn;
        $total += $tiltedColumn->calculateLoad();
    }
    for ($x = 0; $x < count($rows[0]); $x++) {
        for ($y = 0; $y < count($rows); $y++) {
            echo $tiltedColumns[$y]->characters[$x];
        }
        echo "\n";
    }


    echo $total . PHP_EOL;
}

class Column
{
    public array $characters;

    public static function fromRows(array $rows, int $x): self
    {
        $column = new Column();
        $column->characters = [];
        for ($y = 0; $y < count($rows); $y++) {
            $column->characters[] = $rows[$y][$x];
        }

        return $column;
    }

    public function slideNorth(): Column
    {
        $slidePosition = 0;
        $column = new self();
        $column->characters = [];
        $remainingDots = [];
        for ($i = 0; $i < count($this->characters); $i++) {
            $character = $this->characters[$i];
            if ($character === '.') {
                $remainingDots[] = $character;
                continue;
            }
            if ($character === 'O') {
                $column->characters[$slidePosition] = $character;
                $nextMinSlidePosition = $slidePosition +1;
                $slidePosition = null;
                for ($j = $nextMinSlidePosition; $j < count($this->characters); $j++) {
                    if ($this->characters[$j] !== '#') {
                        $slidePosition = $j;
                        break;
                    }
                }
                if ($slidePosition === null) {
                    break;
                }

                continue;
            }

            if ($character === '#') {
                $slidePosition = $i + 1;
                $column->characters[$i] = $character;
            }
            ksort($column->characters);

        }

        for ($i = 0; $i < count($this->characters); $i++) {
            if (!array_key_exists($i, $column->characters)) {
                $column->characters[$i] = '.';
            }
        }
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
