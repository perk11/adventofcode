<?php


$input = fopen('input', 'rb');
$elfs = [];
$max = 0;
$elf = new Elf();
while ($line = fgets($input)) {
    if ($line === "\n") {
        $max = max($elf->getTotalCalories(), $max);
        $elfs[] = $elf;
        $elf = new Elf();
    } else {
        $elf->calories[] = (int) $line;
    }
}
$elfs[] = $elf;
$max = max($elf->getTotalCalories(), $max);
echo $max . "\n";
usort($elfs, function (Elf $elf1, Elf $elf2) {
    return $elf2->getTotalCalories() <=> $elf1->getTotalCalories();
});

echo $elfs[0]->getTotalCalories() + $elfs[1]->getTotalCalories() + $elfs[2]->getTotalCalories() . "\n";


class Elf
{
    public array $calories = [];

    public function getTotalCalories(): int
    {
        return array_sum($this->calories);
    }

}
