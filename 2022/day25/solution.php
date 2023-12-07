<?php

$inputHandle = fopen('input', 'rb');
$sum =0;
while ($line = fgets($inputHandle)) {
    echo $line;
    echo snafuToDec(trim($line)) ."\n";
    $sum+= snafuToDec(trim($line));
}
echo $sum . "\n";
echo decToSnafu($sum);

assert(decToSnafu($sum) === snafuToDec(decToSnafu($sum)));
function snafuToDec(string $snafu): int
{
    $total = 0;
    for ($i = 1, $iMax = strlen($snafu); $i <= $iMax; $i++) {
        $char = $snafu[-$i];
        $decChar = snafuDigitToDec($char);
        $total += $decChar * (5 ** ($i - 1));
    }

    return $total;
}

function decToSnafu(int $dec): string
{
    $base5Number = base_convert($dec, 10, 5);
    $overflow = 0;
    $snafuNumber = '';
    for ($digitIndex = strlen($base5Number)-1; $digitIndex>=0; $digitIndex--) {
        $snafuDigitArray = base5DigitToSnafu($base5Number[$digitIndex] + $overflow);
        $digit = $snafuDigitArray[0];
        $overflow = $snafuDigitArray[1];
        $snafuNumber = $digit . $snafuNumber;
    }
    if ($overflow > 0) {
        $snafuNumber = $overflow .  $snafuNumber;
    }

    return $snafuNumber;
}

function base5DigitToSnafu(int $base5Digit): array
{


    switch ($base5Digit) {
        case 0:
            return [0, 0];
        case 1:
            return [1, 0];
        case 2;
            return [2, 0];
        case 3:
            return ['=', 1];
        case 4:
            return ['-', 1];
        case 5:
            return [0, 1];
        case 6:
            return [1, 1];
        case 7:
            return [2, 1];
        case 8:
            return ['=', 2];
        case 9:
            return ['-', 2];
        case 10:
            return [0, 2];
    }

}

function snafuDigitToDec(string $snafuDigit): int
{
    switch ($snafuDigit) {
        case 1:
            return 1;
        case 2:
            return 2;
        case 0:
            return 0;
        case '-':
            return -1;
        case '=':
            return -2;
    }
    throw new \Exception("Unknown digit: $snafuDigit");
}
