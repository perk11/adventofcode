<?php
foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    //start parsing
    $input = fopen($fileName, 'rb');
    /** @var Component[] $components */
    $components = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $lineParts = explode(': ', $line);

        $component = new Component();
        $component->id = $lineParts[0];
        $connections = explode(' ', $lineParts[1]);
        foreach ($connections as $connection) {
            $component->connections[] = $connection;
        }
        $components[$component->id] = $component;
    }
    foreach ($components as $component) {
        foreach ($component->connections as $connection) {
            if (array_key_exists($connection, $components)) {
                $otherComponent = $components[$connection];
            } else {
                $otherComponent = new Component();
                $otherComponent->id = $connection;
                $components[$connection] = $otherComponent;
            }
            if (!in_array($component->id, $otherComponent->connections, true)) {
                $otherComponent->connections[] = $component->id;
            }
        }
    }
    //end parsing

    //Initially put all components into group 1
    $group1Components = $components;
    $outsideConnectionsFromGroup1 = 0;
    while ($outsideConnectionsFromGroup1 !== 3) {
        if (count($group1Components) === 0) {
            echo "No solution found!\n";
            break;
        }
        $componentToRemove = findComponentWithMaxConnectionsOutsideArray($group1Components);
//        echo "Removing " . $componentToRemove->id . " from group 1\n";
        unset($group1Components[$componentToRemove->id]);

        //Recalculate the number of outside connections to make sure it's not yet 3
        $outsideConnectionsFromGroup1 = 0;
        foreach ($group1Components as $countedComponent) {
            $outsideConnectionsFromGroup1 +=  $countedComponent->countConnectionsExcept($group1Components);
        }
    }

    $group1ComponentsCount = count($group1Components);
    $group2ComponentsCount = count($components) - $group1ComponentsCount;
    echo "Group 1: $group1ComponentsCount. Group 2: $group2ComponentsCount \n";
    echo $group1ComponentsCount * $group2ComponentsCount . "\n";
}

/**
 * @param Component[] $components
 */
function findComponentWithMaxConnectionsOutsideArray(array $components): ?Component
{
    usort($components, static function (Component $a, Component $b) use ($components) {
        return $a->countConnectionsExcept($components) <=> $b->countConnectionsExcept($components);
    });

    return end($components);
}

class Component
{
    public string $id;

    public array $connections = [];

    public function countConnectionsExcept(array $excludedConnections): int
    {
        $connectionsNumber = 0;
        foreach ($this->connections as $connection) {
            if (array_key_exists($connection, $excludedConnections)) {
                continue;
            }
            $connectionsNumber++;
        }

        return $connectionsNumber;
    }
}
