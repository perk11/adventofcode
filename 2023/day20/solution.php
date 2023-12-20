<?php

ini_set('memory_limit', '60G');

enum PulseType
{
    case Low;
    case High;
}

foreach (['testInput', 'testInput2', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $totalLow = 0;
    $totalHigh = 0;

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
    $modules['output'] = new NullModule();
    $modules['rx'] = new NullModule();

    foreach ($modules as $module) {
        foreach ($module->destinations as $destination) {
            if (array_key_exists($destination, $conjunctions)) {
                $conjunctions[$destination]->addInput($module->name);
            }
        }
    }

    $pulseQueue = new SplQueue();
//    $pulseQueue->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
    $broadcaster = $modules['broadcaster'];
    $buttonPulse = new Pulse();
    $buttonPulse->source ='button';
    $buttonPulse->type = PulseType::Low;
    $buttonPulse->destination = 'broadcaster';
    for ($i = 0; $i < 1000; $i++) {
        echo "Button press $i\n";
        countPulses([$buttonPulse]);
        $broadcasterPulses = $broadcaster->handlePulse($buttonPulse);
        countPulses($broadcasterPulses);
        foreach ($broadcasterPulses as $pulse) {
            $pulseQueue->enqueue($pulse);
        }
        while (!$pulseQueue->isEmpty()) {
            $pulse = $pulseQueue->dequeue();
            $destination = $modules[$pulse->destination];
            $newPulses = $destination->handlePulse($pulse);
            countPulses($newPulses);
            foreach ($newPulses as $newPulse) {
                $pulseQueue->enqueue($newPulse);
            }
        }
    }

    echo $totalLow . "\n";
    echo $totalHigh . "\n";
    echo $totalLow * $totalHigh . "\n";
}

function countPulses(array $pulses): void
{
    global $totalLow;
    global $totalHigh;
    foreach ($pulses as $pulse) {
        if ($pulse->type === PulseType::Low) {
            $totalLow++;
        } else {
            $totalHigh++;
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
        echo $this->source . ' -' . ($this->type === PulseType::Low ? 'low' : 'high') . '-> ' . $this->destination ."\n";
    }
}

abstract class Module
{
    public string $name;

    public array $destinations;

    /**
     * @return Pulse[]
     */
    public function handlePulse(Pulse $pulse): array
    {
        $pulse->printDebug();

        return [];
    }
}

class NullModule extends Module
{
    public function __construct()
    {
        $this->destinations = [];
    }
}

class Broadcaster extends Module
{
    public function handlePulse(Pulse $pulse): array
    {
        parent::handlePulse($pulse);
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

    public function handlePulse(Pulse $pulse): array
    {
        parent::handlePulse($pulse);
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
    /** @var PulseType[] */
    public array $inputs = [];

    public function addInput(string $name): void
    {
        $this->inputs[$name] = PulseType::Low;
    }

    public function handlePulse(Pulse $pulse): array
    {
        parent::handlePulse($pulse);
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

        $pulses = [];
        foreach ($this->destinations as $destination) {
            $newPulse = clone $protoPulse;
            $newPulse->destination = $destination;
            $pulses[] = $newPulse;
        }

        return $pulses;
    }
}
