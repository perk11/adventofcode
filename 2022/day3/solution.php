<?php

$input = fopen('input', 'rb');
$total = 0;
$rucksacks = [];
$groups = [];
$i = 0;
while ($line = fgets($input)) {

    $rucksack = new Rucksack();
    $rucksack->contents = trim($line);

    $duplicates = $rucksack->findDuplicateItemTypes();
    $rucksacks[] = $rucksack;
    if (count($duplicates) === 0) {
        continue;
    }
    if (count($duplicates) > 1) {
        throw new \Exception('more than one dup: '. $rucksack->contents);
    }
    $groupId = floor($i/3);
    $groups[$groupId][] = $rucksack;
    $total += $rucksack->charToPriority($duplicates[0]);
    $i++;
}
echo $total . "\n";

$badgeTotal = 0;
foreach ($groups as $group)
{
    $charCounts=[];
    foreach ($group as $ruksack) {
        $chars = str_split($ruksack->contents);
        $chars = array_unique($chars);
        foreach ($chars as $char) {
            if (array_key_exists($char, $charCounts)) {
                $charCounts[$char]++;
            } else {
                $charCounts[$char] = 1;
            }
        }
    }

    foreach ($charCounts as $char => $count) {
        if ($count === 3) {
            $badgeTotal += $ruksack->charToPriority($char);
            break;
        }
    }
}

echo $badgeTotal . PHP_EOL;
class Rucksack
{

    public $contents;

    private array $map;

    public function __construct()
    {
        $this->map = array_merge([''], range('a', 'z'), range('A', 'Z'));
    }

    public function getCompartmentSize()
    {
        $totalItems = strlen($this->contents);
        if ($totalItems % 2 !== 0) {
            throw new \Exception("Odd number found! " . $this->contents);
        }

        return $totalItems / 2;
    }

    public function getCompartmentOneContents(): string
    {
        return substr($this->contents, 0, $this->getCompartmentSize());
    }

    public function getCompartmentTwoContents(): string
    {
        return substr($this->contents, $this->getCompartmentSize());
    }

    public function findDuplicateItemTypes(): array
    {
        $compartmentOneArray = str_split($this->getCompartmentOneContents());
        $compartmentTwoArray = str_split($this->getCompartmentTwoContents());

        $duplicates = [];
        foreach ($compartmentOneArray as $itemType) {
            if (in_array($itemType, $compartmentTwoArray, true)) {
                $duplicates[] = $itemType;
            }
        }

        return array_unique($duplicates);
    }

    public function charToPriority(string $char)
    {
        if (!in_array($char, $this->map, true)) {
            throw new \Exception("Unknown char $char");
        }

        return array_search($char, $this->map, true);
    }
}
