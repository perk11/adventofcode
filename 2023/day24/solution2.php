<?php

class Vector
{
    public int $x;

    public int $y;

    public int $z;

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


foreach (['input'] as $fileName) {
//    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
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
    $hailstone0 = $hailStones[0];
    $hailstone1 = $hailStones[1];
    $hailstone2 = $hailStones[2];
    $x1 = $hailstone0->position->x;
    $y1 = $hailstone0->position->y;
    $z1 = $hailstone0->position->z;
    $a1 = $hailstone0->velocity->x;
    $b1 = $hailstone0->velocity->y;
    $c1 = $hailstone0->velocity->z;
    $x2 = $hailstone1->position->x;
    $y2 = $hailstone1->position->y;
    $z2 = $hailstone1->position->z;
    $a2 = $hailstone1->velocity->x;
    $b2 = $hailstone1->velocity->y;
    $c2 = $hailstone1->velocity->z;

    $x3 = $hailstone2->position->x;
    $y3 = $hailstone2->position->y;
    $z3 = $hailstone2->position->z;
    $a3 = $hailstone2->velocity->x;
    $b3 = $hailstone2->velocity->y;
    $c3 = $hailstone2->velocity->z;


    file_put_contents('php://stderr', "Feed the following input to \"sage\", e.g. php solution2.php|sage\n");
    file_put_contents('php://stderr', "The answer is a sum of x, y, z.\n");
    echo "var( 'x,y,z,a,b,c,t0,t1,t2' )\n";
    echo "eq1 = x + a*t0 == $x1 + $a1*t0,\n";
    echo "eq2 = y + b*t0 == $y1 + $b1*t0,\n";
    echo "eq3 = z + c*t0 == $z1 + $c1*t0,\n";

    echo "eq4 = x + a*t1 == $x2 + $a2*t1,\n";
    echo "eq5 = y + b*t1 == $y2 + $b2*t1,\n";
    echo "eq6 = z + c*t1 == $z2 + $c2*t1,\n";

    echo "eq7 = x + a*t2 == $x3 + $a3*t2,\n";
    echo "eq8 = y + b*t2 == $y3 + $b3*t2,\n";
    echo "eq9 = z + c*t2 == $z3 + $c3*t2,\n";

    echo "solve([eq1,eq2, eq3, eq4, eq5, eq6, eq7,eq8, eq9], [x,y,z,a,b,c,t0,t1,t2]),\n";



}

class Hailstone
{
    public int $id;

    public Vector $position;

    public Vector $velocity;
}

