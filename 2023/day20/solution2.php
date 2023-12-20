<?php

ini_set('memory_limit', '60G');

enum PulseType
{
    case Low;
    case High;
}

foreach (['input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    /** @var Module[] $modules */
    $modules = [];
    /** @var Conjunction[] $conjunctions */
    $conjunctions = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $lineParts = explode(' -> ', $line);
        switch ($lineParts[0][0]) {
            case '%':
                $module = new FlipFlop();
                $module->name = substr($lineParts[0], 1);
                break;
            case '&':
                $module = new Conjunction();
                $module->name = substr($lineParts[0], 1);
                break;
            case 'b':
                $module = new Broadcaster();
                $module->name = 'broadcaster';
                break;
            default:
                throw new \Exception("Unknown module type");
        }
        $destinationStrings = explode(', ', $lineParts[1]);
        $module->destinations = $destinationStrings;

        $modules[$module->name] = $module;
        if ($module instanceof Conjunction) {
            $conjunctions[$module->name] = $module;
        }
    }
    $modules['rx'] = new NullModule();

    foreach ($modules as $module) {
        foreach ($module->destinations as $destination) {
            if (array_key_exists($destination, $conjunctions)) {
                $conjunctions[$destination]->addInput($module->name);
            }
        }
    }

    $pulseQueue = new SplQueue();
    $broadcaster = $modules['broadcaster'];
    $buttonPulse = new Pulse();
    $buttonPulse->source = 'button';
    $buttonPulse->type = PulseType::Low;
    $buttonPulse->destination = 'broadcaster';

    for ($i = 1; ; $i++) {
        if ($i % 5000 === 0) {
            echo "Button press $i\n";
            echo "High Cycle Lengths:\n";
            foreach (Conjunction::$highCycleLengths as $name => $highCycleLength) {
                echo "$name - $highCycleLength\n";
            }
            echo "Low Cycle Lengths:\n";
            foreach (Conjunction::$lowCycleLengths as $name => $lowCycleLength) {
                echo "$name - $lowCycleLength\n";
            }
            
            //Hardcoded for this input
            if (array_key_exists('fc', Conjunction::$highCycleLengths) && array_key_exists('xp',Conjunction::$highCycleLengths) && array_key_exists('dd', Conjunction::$highCycleLengths) && array_key_exists('fh', Conjunction::$highCycleLengths)) {
                echo "Answer: " . Conjunction::$highCycleLengths['fc'] *  Conjunction::$highCycleLengths['xp'] *  Conjunction::$highCycleLengths['dd'] *  Conjunction::$highCycleLengths['fh'] . "\n";
                exit();
            }
        }
        $broadcasterPulses = $broadcaster->handlePulse($buttonPulse, $i);
        foreach ($broadcasterPulses as $pulse) {
            $pulseQueue->enqueue($pulse);
        }
        while (!$pulseQueue->isEmpty()) {
            $pulse = $pulseQueue->dequeue();
            $destination = $modules[$pulse->destination];
            $newPulses = $destination->handlePulse($pulse, $i);
            foreach ($newPulses as $newPulse) {
                $pulseQueue->enqueue($newPulse);
            }
        }
    }
}


class Pulse
{
    public string $source;

    public string $destination;

    public PulseType $type;

    public function printDebug(): void
    {
//        echo $this->source . ' -' . ($this->type === PulseType::Low ? 'low' : 'high') . '-> ' . $this->destination ."\n";
    }
}

abstract class Module
{
    public string $name;

    public array $destinations;

    abstract public function handlePulse(Pulse $pulse, int $i): array;
}

class NullModule extends Module
{
    public function __construct()
    {
        $this->destinations = [];
    }

    public function handlePulse(Pulse $pulse, int $i): array
    {
        return [];
    }
}

class Broadcaster extends Module
{
    public function handlePulse(Pulse $pulse, int $i): array
    {
        $pulses = [];
        foreach ($this->destinations as $destination) {
            $newPulse = clone $pulse;
            $newPulse->source = $this->name;
            $newPulse->destination = $destination;
            $pulses[] = $newPulse;
        }

        return $pulses;
    }
}

class FlipFlop extends Module
{
    public bool $isOn = false;

    public function handlePulse(Pulse $pulse, int $i): array
    {
        if ($pulse->type === PulseType::High) {
            return [];
        }
        $this->isOn = !$this->isOn;

        $pulses = [];
        foreach ($this->destinations as $destination) {
            $newPulse = new Pulse();
            if ($this->isOn) {
                $newPulse->type = PulseType::High;
            } else {
                $newPulse->type = PulseType::Low;
            }
            $newPulse->source = $this->name;
            $newPulse->destination = $destination;
            $pulses[] = $newPulse;
        }

        return $pulses;
    }
}

class Conjunction extends Module
{
    public ?int $lastLowPulseIteration = null;

    public ?int $latHighPulseIteration = null;

    public static array $highCycleLengths = [];

    public static array $lowCycleLengths = [];

    /** @var PulseType[] */
    public array $inputs = [];

    public function addInput(string $name): void
    {
        $this->inputs[$name] = PulseType::Low;
    }

    public function handlePulse(Pulse $pulse, int $i): array
    {
        $this->inputs[$pulse->source] = $pulse->type;
        $isLow = true;
        foreach ($this->inputs as $pulseType) {
            if ($pulseType === PulseType::Low) {
                $isLow = false;
                break;
            }
        }
        $protoPulse = new Pulse();
        $protoPulse->source = $this->name;
        $protoPulse->type = $isLow ? PulseType::Low : PulseType::High;
        if ($isLow) {
            if (!array_key_exists($this->name, self::$lowCycleLengths)) {
                if ($this->lastLowPulseIteration === null) {
                    $this->lastLowPulseIteration = $i;
                } else {
                    self::$lowCycleLengths[$this->name] = $i - $this->lastLowPulseIteration;
                }
            }
        } else {
            if (!array_key_exists($this->name, self::$highCycleLengths)) {
                if ($this->latHighPulseIteration === null) {
                    $this->latHighPulseIteration = $i;
                } else {
                    self::$highCycleLengths[$this->name] = $i - $this->latHighPulseIteration;
                }
            }
        }

        $pulses = [];
        foreach ($this->destinations as $destination) {
            $newPulse = clone $protoPulse;
            $newPulse->destination = $destination;
            $pulses[] = $newPulse;
        }

        return $pulses;
    }
}
