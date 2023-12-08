<?php

foreach (['input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $line = trim(fgets($input));
    $instructions = str_split($line);
    fgets($input);
    $nodes = [];

    while ($line = fgets($input)) {
        $node = new Node();
        $node->id = trim(strtok($line, '='));
        $node->leftNode = trim(trim(strtok(',')), '(');
        $node->rightNode = trim(trim(strtok(',')), ')');
        $node->final = str_ends_with($node->id, 'Z');
        $nodes[$node->id] = $node;
    }
    $currentNodes = [];
    foreach ($nodes as $node) {
        if (str_ends_with($node->id, 'A')) {
            $currentNodes[] = $node;
        }
    }
    $currentNodesCount = count($currentNodes);
    for ($i =0; $i<count($currentNodes); $i++) {
        $currentNodesHistory[$i] = [];
    }
    $iMax = count($instructions);
    $currentNodesHistory = [];
    for ($i = 0; ; $i++) {
        if ($i % 1000013 === 0) {
            echo $i . " ";
            foreach ($currentNodes as $currentNode) {
                echo $currentNode->id . ' ';
            }
            echo "\n";
        }
        $instructionId = $i % $iMax;
        $instruction = $instructions[$instructionId];
        for ($j = 0;  $j < $currentNodesCount; $j++) {
            $currentNode = $currentNodes[$j];
            $currentNodesHistory[$j][] = $currentNode;
            if ($instruction === 'L') {
                $currentNodes[$j] = $nodes[$currentNode->leftNode];
            } elseif ($instruction === 'R') {
                $currentNodes[$j] = $nodes[$currentNode->rightNode];
            } else {
                die('wrong instruction: ' . $instruction);
            }
        }
        $finish = true;

        foreach ($currentNodes as $currentNodeIndex => $currentNode) {
            if ($currentNode->final) {
                $currentNode->recordHistory($currentNodesHistory[$currentNodeIndex]);
                $currentNodesHistory[$currentNodeIndex] = [];
            } else {
                $finish = false;
            }
        }

        if ($finish) {
            break;
        }
    }


    echo $i + 1 . PHP_EOL;
}

class Node
{
    public string $id;

    public string $leftNode;

    public string $rightNode;

    public array $stepsToZFromInstructionId = [];

    public bool $final;

    /**
     * @param Node[] $nodeHistory
     * @return void
     */
    public function recordHistory(array $nodeHistory): void
    {
        $stepsCount = count($nodeHistory) - 1;
        for ($i = $stepsCount; $i>0; $i--) {
            $nodeHistory[$i]->stepsToZFromInstructionId[$stepsCount] = $i;
        }
    }
}
