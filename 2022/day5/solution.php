<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $line = fgets($input);
    $cratesNumber = ceil(strlen($line) / 4);
    /** @var Stack[] $stacks */
    $stacks = [];
    $id = 1;
    while ($line !== "\n") {
        $line = trim($line);
        $stack = new Stack();
        $stack->id = $id;
        for ($i = 1; $i <= $cratesNumber; $i++) {
            $currentCrate = substr($line, ($i - 1) * 4 + 1, 1);
            if (trim($currentCrate) !== '') {
                array_unshift($stack->crates, $currentCrate);
            }
        }
        $stacks[$id] = $stack;

        $line = fgets($input);
        $id++;
    }
    $moves = [];
    //parse moves;
    while ($line = fgets($input)) {
        $line = trim($line);
        $line = str_replace('move ', '', $line);
        $move = new Move();
        $move->numberOfCrates = strtok($line, ' ');
        strtok(' ');
        $move->from = strtok(' ');
        strtok(' ');
        $move->to = strtok(' ');
        $moves[] = $move;
    }

    foreach ($moves as $move) {
        $fromStack = $stacks[$move->from];
        $toStack = $stacks[$move->to];
        $cratesToMove = array_slice($fromStack->crates, -$move->numberOfCrates);
        $cratesToMove = array_reverse($cratesToMove);
        array_push($toStack->crates, ...$cratesToMove);
        $createsLeft = count($fromStack->crates) - count($cratesToMove);
        if ($createsLeft < 0) {
            throw new \Exception("Negative crates left");
        }
        $fromStack->crates = array_slice($fromStack->crates, 0, $createsLeft);
    }

    foreach ($stacks as $stack) {
        echo end($stack->crates);
    }
    echo "\n";
}

class Stack
{
    public $id;

    public array $crates = []; //lower index = lower position in the stack
}

class Move
{
    public int $numberOfCrates;

    public int $from;

    public int $to;
}
