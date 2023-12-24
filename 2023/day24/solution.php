<?php

class Vector
{
    public float $x;

    public float $y;

    public float $z;

    public function __construct(float $x, float $y, float $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    public function __toString(): string
    {
        return $this->x . ',' . $this->y . ',' . $this->z;
    }
}


foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    /** @var Hailstone[] $hailStones */
    $hailStones = [];
    $i = 0;
    while ($line = fgets($input)) {
        $line = trim($line);
        $lineParts = explode('@', $line);

        $positionStringArray = explode(',', $lineParts[0]);
        $hailStone = new Hailstone();
        $hailStone->id = $i;
        $hailStone->position = new Vector($positionStringArray[0], $positionStringArray[1], $positionStringArray[2]);

        $velocityStringArray = explode(',', $lineParts[1]);
        $hailStone->velocity = new Vector($velocityStringArray[0], $velocityStringArray[1], $velocityStringArray[2]);

        $hailStones[] = $hailStone;
        $i++;
    }
    $total = 0;
    $pairsChecked = [];
    foreach ($hailStones as $hailStone1) {
        foreach ($hailStones as $hailStone2) {
            if ($hailStone1 === $hailStone2) {
                continue;
            }
            if ($hailStone1->id > $hailStone2->id) {
                $pairKey = $hailStone2->id . '_' . $hailStone1->id;
            } else {
                $pairKey = $hailStone1->id . '_' . $hailStone2->id;
            }

            if (array_key_exists($pairKey, $pairsChecked)) {
                continue;
            }
            $pairsChecked[$pairKey] = true;
            $intersectionPoint = $hailStone1->getTrajectory2D()->getIntersectionPoint($hailStone2->getTrajectory2D());
            if ($intersectionPoint === null) {
                continue;
            }
            $time1 = $hailStone1->getTimeByPosition($intersectionPoint);
            if ($time1 < 0) {
                continue;
            }
            $time2 = $hailStone2->getTimeByPosition($intersectionPoint);
            if ($time2 < 0) {
                continue;
            }
            if ($fileName === 'testInput') {
                if ($intersectionPoint->x >= 7 && $intersectionPoint->y >= 7 && $intersectionPoint->x <= 27 && $intersectionPoint->y <= 27) {
                    $total++;
                }
            } elseif ($intersectionPoint->x >= 200000000000000 && $intersectionPoint->y >= 200000000000000 && $intersectionPoint->x <= 400000000000000 && $intersectionPoint->y <= 400000000000000) {
                $total++;
            }
        }
    }

    echo $total . PHP_EOL;
}

class Hailstone
{
    public int $id;

    public Vector $position;

    public Vector $velocity;

    public function getTrajectory2D(): Trajectory2D
    {
        $trajectory = new Trajectory2D();

        $trajectory->x0 = $this->position->x;
        $trajectory->y0 = $this->position->y;

        $trajectory->a = $this->velocity->x;
        $trajectory->b = $this->velocity->y;

        return $trajectory;
    }

    public function getTimeByPosition(Vector $vector): float
    {
        //count only x
        $difference = ($vector->x - $this->position->x);

        return $difference / $this->velocity->x;
    }
}

class Trajectory2D
{
    //x=x0+a*t
    //y=y0+b*t
    public float $x0;

    public float $a;

    public float $y0;

    public float $b;

    public function getIntersectionPoint(Trajectory2D $trajectory2D): ?Vector
    {

        // this->x0 + this->a * t1 = trajectory2D->x0 + trajectory2D->a * t2
        // this->y0 + this->b * t1 = trajectory2D->y0 + trajectory2D->b * t2

        // Solve for t1 and t2
        $denominator = $this->a * $trajectory2D->b - $this->b * $trajectory2D->a;
        if (abs($denominator) <= 0.0000000000001) {
            // No intersection
            return null;
        }

        $t1 = ($trajectory2D->b * ($trajectory2D->x0 - $this->x0) + $trajectory2D->a * ($this->y0 - $trajectory2D->y0)) / $denominator;

        $x = $this->x0 + $this->a * $t1;
        $y = $this->y0 + $this->b * $t1;

        return new Vector($x, $y, 0);
    }
}

