<?php

ini_set('memory_limit', '16G');
foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $total = 0;

    $line = fgets($input);
    $line = trim($line);
    $sequences = explode(',', $line);
    $lenses = [];
    /** @var Box[] $boxes */
    $boxes = [];
    $instructions = [];
    for ($i = 0; $i < 256; $i++) {
        $box = new Box();
        $box->id = $i;
        $boxes[$i] = $box;
    }

    foreach ($sequences as $sequence) {
        $instruction = new Instruction();
        $lens = new Lens();
        if (str_contains($sequence, '=')) {
            $instruction->operation = '=';
            $lens->label = strtok($sequence, '=');
            $lens->focalLength = strtok('=');
        } else {
            $instruction->operation = '-';
            $lens->label = strtok($sequence, '-');
        }
        $instruction->lens = $lens;
        $instructions[] = $instruction;
    }

    foreach ($instructions as $instruction) {
        $boxId = $instruction->lens->getBoxId();
        $box = $boxes[$boxId];
        if ($instruction->operation === '=') {
            $box->addLens($instruction->lens);
        } else {
            $box->removeLens($instruction->lens);
        }
    }
    foreach ($boxes as $box) {
        $total += $box->calculateFocusingPower();
    }

    echo $total . PHP_EOL;
}

function HASHMAP(string $string): int
{
    $chars = str_split($string);
    $currentValue = 0;
    foreach ($chars as $char) {
        $asciiCode = ord($char);
        $currentValue += $asciiCode;
        $currentValue *= 17;
        $currentValue %= 256;
    }

    return $currentValue;
}

class Lens
{
    public string $label;

    public int $focalLength;

    public function getBoxId(): int
    {
        return HASHMAP($this->label);
    }
}

class Instruction
{
    public Lens $lens;

    public string $operation;

}

class Box
{
    public int $id;

    /** @var Lens[] $lenses */
    public array $lenses = [];

    public function addLens(Lens $lens): void
    {
        $this->lenses[$lens->label] = $lens;
    }

    public function removeLens(Lens $lens): void
    {
        unset($this->lenses[$lens->label]);
    }

    public function calculateFocusingPower(): int
    {
        $i = 1;
        $focusingPower = 0;
        foreach ($this->lenses as $lens) {
            $focusingPower += ($this->id + 1) * $i * $lens->focalLength;
            $i++;
        }

        return $focusingPower;
    }
}
