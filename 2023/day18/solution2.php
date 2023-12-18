<?php

class Point implements Stringable
{
    public int $x;

    public int $y;

    public function applyCommand(DigCommand $digCommand): Point
    {
        $point = clone $this;
        switch ($digCommand->direction) {
            case Direction::Up:
                $point->y -= $digCommand->distance;
                break;
            case Direction::Down:
                $point->y += $digCommand->distance;
                break;
            case Direction::Right:
                $point->x += $digCommand->distance;
                break;
            case Direction::Left:
                $point->x -= $digCommand->distance;
                break;
            default:
                throw new \Exception("Unknown direction");
        }

        return $point;
    }

    public function __toString(): string
    {
        return $this->x . ',' . $this->y;
    }
}

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');
    $total = 0;

    $digCommands = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $parts = explode(' ', $line);
        $digCommand = new DigCommand();
        $distanceHex = substr($parts[2], 2, 5);
        $digCommand->direction = $parts[2][7];
        $digCommand->distance = hexdec($distanceHex);
        $digCommands[] = $digCommand;
    }
    $point = new Point();
    $point->x = 0;
    $point->y = 0;
    $points = [$point];
    $perimeter = 0;
    foreach ($digCommands as $digCommand) {
        $perimeter += $digCommand->distance;
        $point = $point->applyCommand($digCommand);
        $points[] = $point;
    }

    $points = array_unique($points);
    $area = 0;
    for ($i = 0; $i < count($points) - 1; $i += 2) {
        $area += $points[$i]->x * $points[$i + 1]->y - $points[$i + 1]->x * $points[$i]->y;
    }
    $area += $points[count($points) - 1]->x * $points[0]->y;
    $area += $points[count($points) - 1]->y * $points[0]->x;
    $area = abs(0.5 * $area);

    //I don't know why "total" is right, derived it from the test input
    echo "Perimeter: $perimeter. Area: $area. Total: " . ($area * 2 + $perimeter / 2) + 1;
    echo "\n";
}

class Direction
{
    public const Up = '3';
    public const Down = '1';
    public const Left = '2';
    public const Right = '0';
}

class DigCommand
{
    public string $direction;

    public int $distance;

    public string $color;
}
