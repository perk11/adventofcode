<?php

foreach (['testInput', 'input'] as $fileName) {
    echo PHP_EOL . $fileName . PHP_EOL . PHP_EOL;
    $input = fopen($fileName, 'rb');

    $hands = [];
    while ($line = fgets($input)) {
        $line = trim($line);
        $lineParts = explode(' ', $line);
        $hand = new Hand();
        $hand->cards = str_split($lineParts[0]);
        $hand->bid = $lineParts[1];
        $hands[] = $hand;
    }

    usort($hands, function (Hand $a, Hand $b) {
        if ($a->isStrongerThan($b)) {
            return 1;
        }
        return -1;
    });
    $handsCSV = fopen('hands.csv', 'wb');
    $i = 1;
    foreach ($hands as $hand)
    {
        fputcsv($handsCSV, [$i,$hand->bid, $hand->jokerNumbers, implode('', $hand->cards), $hand->getStrength()]);
        $i++;
    }

    $sum = 0;
    for($i=0; $i<count($hands); $i++) {
        $sum += ($i+1) * $hands[$i]->bid;
    }
    echo $sum . PHP_EOL;
}

class Hand
{
    public array $cards;

    public int $bid;

    private int $strength;

    public int $jokerNumbers;
    public static $labels = [
        "A",
        "K",
        "Q",
        "T",
        "9",
        "8",
        "7",
        "6",
        "5",
        "4",
        "3",
        "2",
        "J",
    ];

    public function isStrongerThan(Hand $hand): bool
    {
        $thisStrength = $this->getStrength();
        $handStrength = $hand->getStrength();
        if ($thisStrength > $handStrength) {
            return true;
        }
        if ($thisStrength < $handStrength) {
            return false;
        }

        return $this->isHighestCardBetterThan($hand);
    }

    public function isHighestCardBetterThan(Hand $hand): bool
    {
        for ($i = 0; $i < 5; $i++) {
            $thisCard = $this->cards[$i];
            $thisCardIndex = array_search($thisCard, self::$labels);
            $handCard = $hand->cards[$i];
            $handCardIndex = array_search($handCard, self::$labels);
            if ($thisCardIndex < $handCardIndex) {
                return true;
            }
            if ($thisCardIndex > $handCardIndex) {
                return false;
            }
        }

        throw \Exception('draw');
    }

    public function getStrength(): int
    {
        if (!isset($this->strength)) {
            $this->strength = $this->determineStrength();
        }

        return $this->strength;
    }

    public function determineStrength(): int
    {
// 6       Five of a kind, where all five cards have the same label: AAAAA
// 5   Four of a kind, where four cards have the same label and one card has a different label: AA8AA
// 4   Full house, where three cards have the same label, and the remaining two cards share a different label: 23332
//   3 Three of a kind, where three cards have the same label, and the remaining two cards are each different from any other card in the hand: TTT98
//   2 Two pair, where two cards share one label, two other cards share a second label, and the remaining card has a third label: 23432
//  1  One pair, where two cards share one label, and the other three cards have a different label from the pair and each other: A23A4
//  0  High card, where all cards' labels are distinct: 23456

        $cardNumbers = [];
        foreach ($this->cards as $card) {
            if (array_key_exists($card, $cardNumbers)) {
                $cardNumbers[$card]++;
            } else {
                $cardNumbers[$card] = 1;
            }
        }
        $jokerNumbers = array_key_exists('J', $cardNumbers) ? $cardNumbers['J'] : 0;
        $this->jokerNumbers = $jokerNumbers;
        $maxMatches = max($cardNumbers);
        if ($maxMatches === 5) {
            return 6;
        }
        if ($maxMatches === 4) {
            if ($jokerNumbers > 0) {
                return 6;
            }
            return 5;
        }
        if ($maxMatches === 3) {
            if ($jokerNumbers === 3) { //matches are between the jokers
                $countedArray = array_count_values(array_values($cardNumbers));
                if ($countedArray[2] === 1) {
                    return 6;
                }
                return 5;
            }
            if ($jokerNumbers === 2) {
                return 6;
            }
            if ($jokerNumbers === 1) {
                return 5;
            }


            if (in_array(2, $cardNumbers, true)) {
                return 4;
            }

            return 3;
        }
        if ($maxMatches === 2) {
            $countedArray = array_count_values(array_values($cardNumbers));
            if ($jokerNumbers === 2) {
                if ($countedArray[2] === 2) {
                    return 5; //four of a kind
                }
                return 3; //three of a kind
            }

            if ($jokerNumbers === 1) {
                if ($countedArray[2] === 2) {
                    return 4; //full house
                }
                return 3; //three of a kind
            }
            if ($countedArray[2] === 2) {
                return 2;
            } elseif ($countedArray[2] === 1) {
                return 1;
            } else {
                throw \Exception('unknown value');
            }
        }

        if ($maxMatches === 1) {
            if ($jokerNumbers === 1) {
                return 1;
            }
            return 0;
        }

        throw \Exception("unknown maxmatches");
    }
}
