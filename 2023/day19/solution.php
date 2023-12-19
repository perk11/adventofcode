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
    /** @var Part[] $parts */
    $parts = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $line = trim($line, '{}');
        $part = new Part();
        $partStrings = explode(',', $line);
        foreach ($partStrings as $partString) {
            $variableName = strtok($partString, '=');
            $part->$variableName = strtok('=');
        }
        $parts[] = $part;
    }
    $startingWorkflow = $workflows['in'];
    $acceptedPile = [];
    foreach ($parts as $part) {
        $destination = $startingWorkflow->getDestinationForPart($part);
        while ($destination->type === DestinationType::Forward) {
            $workflow = $workflows[$destination->forwardedWorkflowName];
            $destination = $workflow->getDestinationForPart($part);
        }

        switch ($destination->type) {
            case DestinationType::Accept:
                $acceptedPile[] = $part;
                break;
            case DestinationType::Reject:
                //do nothing;
                break;
            case DestinationType::Forward:
                throw new \Exception('To be implemented');
        }
    }

    foreach ($acceptedPile as $part) {
        $total += $part->getRating();
    }

    echo $total . "\n";


}

class Part
{
    public int $x;

    public int $m;

    public int $a;

    public int $s;

    public function getKey(): string
    {
        return "$this->x$this->m$this->a$this->s";
    }

    public function getRating(): int
    {
        return $this->x + $this->m + $this->a + $this->s;
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

    public function appliesToPart(Part $part): bool
    {
        if ($this->variable === null) {
            return true;
        }

        $valueOfVariableInThePart = $part->{$this->variable};
        if ($this->operator === Operator::GreaterThan) {
            return $valueOfVariableInThePart > $this->comparisonValue;
        } elseif ($this->operator === Operator::LowerThan) {
            return $valueOfVariableInThePart < $this->comparisonValue;
        }

        throw new \Exception("unknown operator");
    }
}

class Workflow
{
    public string $name;

    /** @var Rule[] $rules */
    public array $rules;

    public function getDestinationForPart(Part $part): Destination
    {
        foreach ($this->rules as $rule) {
            if ($rule->appliesToPart($part)) {
                return $rule->destination;
            }
        }

        throw new \Exception("Workflow without end");
    }
}

