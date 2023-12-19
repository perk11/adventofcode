<?php

enum Operator: string
{
    case GreaterThan = '>';
    case LowerThan = '<';
}

enum DestinationType
{
    case Accept;
    case Reject;
    case Forward;
    case NextRule;
}

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $total = 0;

    /** @var Workflow[] $workflows */
    $workflows = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        if ($line === '') {
            break;
        }
        $workflow = new Workflow();
        $workflow->name = strtok($line, '{');
        $partInBrackets = strtok('{');
        $partInBrackets = trim($partInBrackets, '}');
        $ruleStrings = explode(',', $partInBrackets);
        foreach ($ruleStrings as $ruleString) {
            $rule = new Rule();
            if (str_contains($ruleString, ':')) {
                $conditionString = strtok($ruleString, ':');
                $rule->variable = $conditionString[0];
                $rule->operator = Operator::from($conditionString[1]);
                $rule->comparisonValue = substr($conditionString, 2);
                $destinationString = strtok(':');
            } else {
                $destinationString = $ruleString;
            }
            $destination = new Destination();
            switch ($destinationString) {
                case 'A':
                    $destination->type = DestinationType::Accept;
                    break;
                case 'R':
                    $destination->type = DestinationType::Reject;
                    break;
                default:
                    $destination->type = DestinationType::Forward;
                    $destination->forwardedWorkflowName = $destinationString;
            }
            $rule->destination = $destination;
            $workflow->rules[] = $rule;
        }
        $workflows[$workflow->name] = $workflow;
    }

    $startingRange = new Range();
    $startingRange->xStart = 1;
    $startingRange->xEnd = 4000;
    $startingRange->mStart = 1;
    $startingRange->mEnd = 4000;
    $startingRange->aStart = 1;
    $startingRange->aEnd = 4000;
    $startingRange->sStart = 1;
    $startingRange->sEnd = 4000;
    $startingRange->destination = new Destination();
    $startingRange->destination->type = DestinationType::Forward;
    $startingRange->destination->forwardedWorkflowName = 'in';

    $startingWorkflow = $workflows['in'];

    /** @var Range[] $acceptedPile */
    $acceptedPile = [];
    /** @var Range[] $rejectedPile */
    $rejectedPile = [];
    $unprocessedRanges = $startingWorkflow->applyToRange($startingRange);
    while (count($unprocessedRanges) > 0) {
        $newUnprocessedRanges = [];
        foreach ($unprocessedRanges as $range) {
            switch ($range->destination->type) {
                case DestinationType::Accept:
                    $acceptedPile[] = $range;
                    break;
                case DestinationType::Reject:
                    $rejectedPile[] = $range;
                    break;
                case DestinationType::Forward:
                    $targetWorkflow = $workflows[$range->destination->forwardedWorkflowName];
                    $newUnprocessedRanges = [...$newUnprocessedRanges, ...$targetWorkflow->applyToRange($range)];
                    break;
            }
        }
        $unprocessedRanges = $newUnprocessedRanges;
    }
    foreach ($acceptedPile as $range) {
        $total += $range->getSize();
    }
    $totalRejected = 0;
    foreach ($rejectedPile as $range) {
        $totalRejected += $range->getSize();
    }
    //debug
    $allRanges = array_merge($acceptedPile, $rejectedPile);
    usort($allRanges, function (Range $a, Range $b) {
        if($a->xStart < $b->xStart) {
            return -1;
        }
        if ($a->xStart>$b->xStart) {
            return 1;
        }
        if($a->xEnd < $b->xEnd) {
            return -1;
        }
        if($a->xEnd > $b->xEnd) {
            return 1;
        }
        if ($a->mStart < $b->mStart) {
            return -1;
        }
        if ($a->mStart > $b->mStart) {
            return 1;
        }
        if ($a->aStart < $b->aStart) {
            return -1;
        }
        if ($a->aStart > $b->aStart) {
            return 1;
        }
        if ($a->sStart < $b->sStart) {
            return -1;
        }
        if ($a->sStart > $b->sStart) {
            return 1;
        }
        return 0;
    });
    foreach ($allRanges as $range) {
        echo "x: ". $range->xStart . '-' . $range->xEnd;
        echo " m: ". $range->mStart . '-' . $range->mEnd;
        echo " a: ". $range->aStart . '-' . $range->aEnd;
        echo " s: ". $range->sStart . '-' . $range->sEnd;
        echo "\n";
    }
    echo "Total: " . $total+$totalRejected ."\n";
    echo "Expected Total: " . $startingRange->getSize()."\n";
    echo "Rejected: $totalRejected\n";

    //end debug
    echo "Accepted: $total \n";
}

class Range
{
    public int $xStart;

    public int $xEnd;

    public int $mStart;

    public int $mEnd;

    public int $aStart;

    public int $aEnd;

    public int $sStart;

    public int $sEnd;

    public Destination $destination;

    public function getSize(): int
    {
        return ($this->sEnd - $this->sStart + 1) *
            ($this->xEnd - $this->xStart + 1) *
            ($this->mEnd - $this->mStart + 1) *
            ($this->aEnd - $this->aStart + 1);
    }

}

class Destination
{
    public ?string $forwardedWorkflowName;

    public DestinationType $type;
}

class Rule
{
    public ?string $variable = null;

    public ?string $comparisonValue;

    public Operator $operator;

    public Destination $destination;

    public function applyToRange(Range $range): array
    {
        if ($this->variable === null) {
            return [$this->sameRangeNewDestination($range)];
        }
        $startPropertyName = $this->variable . 'Start';
        $endPropertyName = $this->variable . 'End';
        if ($this->operator === Operator::LowerThan) {
            if ($range->$startPropertyName >= $this->comparisonValue) {
                $newRange = clone $range;
                $newRange->destination = new Destination();
                $newRange->destination->type = DestinationType::NextRule;

                return [$newRange];
            }
            if ($range->$endPropertyName < $this->comparisonValue) {
                return [$this->sameRangeNewDestination($range)];
            }
            $rangeInside = clone $range;
            $rangeInside->$endPropertyName = $this->comparisonValue - 1;
            $rangeInside->destination = $this->destination;

            $rangeOutside = clone $range;
            $rangeOutside->$startPropertyName = $this->comparisonValue;

            return [$rangeInside, $rangeOutside];
        } else {
            if ($range->$endPropertyName <= $this->comparisonValue) {
                $newRange = clone $range;
                $newRange->destination = new Destination();
                $newRange->destination->type = DestinationType::NextRule;

                return [$newRange];
            }
            if ($range->$startPropertyName > $this->comparisonValue) {
                return [$this->sameRangeNewDestination($range)];
            }
            $rangeInside = clone $range;
            $rangeInside->$startPropertyName = $this->comparisonValue + 1;
            $rangeInside->destination = $this->destination;

            $rangeOutside = clone $range;
            $rangeOutside->$endPropertyName = $this->comparisonValue;

            return [$rangeInside, $rangeOutside];
        }
    }

    private function sameRangeNewDestination(Range $range): Range
    {
        $newRange = clone $range;
        $newRange->destination = $this->destination;

        return $newRange;
    }

}

class Workflow
{
    public string $name;

    /** @var Rule[] $rules */
    public array $rules;

    /** @return Range[] */
    public function applyToRange(Range $range): array
    {
        $ranges = [];
        $rangesForCurrentRule = [$range];

        foreach ($this->rules as $rule) {
            $rangesForNextRule = [];
            foreach ($rangesForCurrentRule as $rangeForCurrentRule) {
                $rangesAfterProcessingByRule = $rule->applyToRange($rangeForCurrentRule);
                foreach ($rangesAfterProcessingByRule as $rangeAfterProcessingByRule) {
                    if ($rangeAfterProcessingByRule->destination->type === DestinationType::NextRule) {
                        $rangesForNextRule[] = $rangeAfterProcessingByRule;
                    } else {
                        $ranges[] = $rangeAfterProcessingByRule;
                    }
                }
            }
            $rangesForCurrentRule = $rangesForNextRule;
        }
        if (isset($rangesForNextRule) && count($rangesForNextRule) > 0) {
            throw new \Exception("Unprocessed ranges are left");
        }

        return $ranges;
    }
}

