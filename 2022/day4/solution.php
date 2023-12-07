<?php

$input = fopen('input', 'rb');
$total = 0;
$overlapTotal = 0;

$i = 0;
while ($line = fgets($input)) {
    $line = trim($line);
    $assignmentPair = explode(',', $line);
    $firstAssignmentRange = explode('-', $assignmentPair[0]);
    $assignment1 = new Assignment();
    $assignment1->startId = $firstAssignmentRange[0];
    $assignment1->endId = $firstAssignmentRange[1];
    $secondAssignmentRange = explode('-', $assignmentPair[1]);
    $assignment2 = new Assignment();
    $assignment2->startId = $secondAssignmentRange[0];
    $assignment2->endId = $secondAssignmentRange[1];
    if ($assignment1->isFullyContainingAssignment($assignment2) || $assignment2->isFullyContainingAssignment(
            $assignment1
        )) {
        $total++;
    }
    echo $assignment1->startId . '-' . $assignment1->endId . ' ' . $assignment2->startId . '-' . $assignment2->endId;
    if ($assignment1->overlaps($assignment2) || $assignment2->overlaps($assignment1)) {
        echo ' overlap';
        $overlapTotal++;
    } else {
        echo ' no lap';
    }
    echo PHP_EOL;
}
echo $total . "\n";
echo $overlapTotal . "\n";

class Assignment
{
    public $startId;

    public $endId;

    public function isFullyContainingAssignment(Assignment $assignment): bool
    {
        return $this->startId <= $assignment->startId && $this->endId >= $assignment->endId;
    }

    public function overlaps(Assignment $assignment)
    {
        if ($this->startId >= $assignment->startId) {
//            if ($this->endId <= $assignment->startId) {
//                return true;
//            }
            if ($this->endId <= $assignment->endId) {
                return true;
            }
        } else {
            if ($this->endId >= $assignment->startId) {
                return true;
            }
        }

        return false;
    }
}
