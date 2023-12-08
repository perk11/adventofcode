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
    $startingNodesCounted = 0;
    $currentNodesCount = count($currentNodes);
    $startingNodes = $currentNodes;
    $iMax = count($instructions);
    $currentNodesHistory = [];
    for ($i = 0; ; $i++) {
        $instructionId = $i % $iMax;
        $instruction = $instructions[$instructionId];
        //This is not optimal and counting all nodes at the same time because it was a transition from a brute-force solution
        for ($j = 0; $j < $currentNodesCount; $j++) {
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
                if (!isset($startingNodes[$currentNodeIndex]->stepsToFinal)) {
                    $startingNodes[$currentNodeIndex]->stepsToFinal = $i + 1;
                    $startingNodesCounted++;
                }
            } else {
                $finish = false;
            }
            if ($startingNodesCounted === $currentNodesCount) {
                break;
            }
        }

        if ($finish) {
            break;
        }
    }

    $lcm = 1;
    foreach ($startingNodes as $startingNode) {
        echo $startingNode->stepsToFinal . PHP_EOL;
        $lcm = leastCommonMultiplier($startingNode->stepsToFinal, $lcm);
    }


    echo $lcm . PHP_EOL;
}

function greatestCommonDenominator(int $a, int $b)
{
    while ($b !== 0) {
        $temp = $b;
        $b = $a % $b;
        $a = $temp;
    }

    return $a;
}

function leastCommonMultiplier(int $a, int $b)
{
    return ($a * $b) / greatestCommonDenominator($a, $b);
}

class Node
{
    public string $id;

    public string $leftNode;

    public string $rightNode;

    public int $stepsToFinal;

    public bool $final;
}
