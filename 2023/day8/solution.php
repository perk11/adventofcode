<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $line = trim(fgets($input));
    $instructions = str_split($line);
    fgets($input);
    $nodes = [];

    while ($line = fgets($input)) {
        $node = new Node();
        $node->id = trim(strtok( $line, '='));
        $node->leftNode = trim(trim( strtok(',')), '(');
        $node->rightNode = trim(trim(strtok(',')),')');
        $nodes[$node->id] = $node;
    }
    $currentNode = $nodes['AAA'];
    $iMax = count($instructions);
    for ($i=0;; $i++) {
        $instructionId = $i % $iMax;
        $instruction = $instructions[$instructionId];
        if ($instruction === 'L') {
            $currentNode = $nodes[$currentNode->leftNode];
        } elseif ($instruction === 'R') {
            $currentNode = $nodes[$currentNode->rightNode];
        } else {
            die('wrong instruction: ' . $instruction);
        }
        if ($currentNode->id === 'ZZZ') {
            break;
        }
    }


    echo $i +1 . PHP_EOL;
}
class Node
{
    public string $id;
    public string $leftNode;
    public string $rightNode;
}
