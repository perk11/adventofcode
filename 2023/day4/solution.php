<?php

$input = fopen('input', 'rb');
$score = 0;
/** @var Card[] $cardsById */
$cardsById = [];
/** @var Card[] $cards */
$cards = [];
while ($line = fgets($input)) {
    $line = trim($line);
    $line = str_replace('  ', ' ', $line);
    $line = str_replace('  ', ' ', $line);
    $line = str_replace('  ', ' ', $line);
    $line = str_replace('  ', ' ', $line);
    $line = str_replace('  ', ' ', $line);
    $line = str_replace('  ', ' ', $line);
    $card = new Card();
    $cardPart = strtok($line, ':');
    $card->id = (int)trim(str_replace('Card ', '', $cardPart));
    $card->originalId = $card->id;
    $parsable = strtok(':');
    $cardParts = explode('|', $parsable);
    $card->winingNumbers = explode(' ', trim($cardParts[0]));
    $card->myNumbers = explode(' ', trim($cardParts[1]));
    $cardsById[$card->id] = $card;
    $cards[] = $card;
    $score += $card->getScore();
//    echo $line;
}
ini_set('memory_limit', '90G');
$i = 0;
while ($i < count($cards)) {
    $currentCard = $cards[$i];
    $matchesCount = $currentCard->countMatches();
    if ($matchesCount > 0) {
//        echo "Win $matchesCount matches, ". $currentCard->id . " (" . $currentCard->originalId . ')' . "\n";
        for ($cardIndex = $currentCard->id + 1; $cardIndex < $currentCard->id + 1 + $matchesCount; $cardIndex++) {
            $newCard = clone $cardsById[$cardIndex];
//            $newCard->id = $currentCard->id;
            $cards[] = $newCard;
        }
    }


    $i++;
}
//echo $score . "\n";
echo count($cards);

class Card
{
    public $id;

    public $originalId;

    public array $winingNumbers;

    public array $myNumbers;

    public $cachedCount;

    public function getMatches()
    {
        $matches = [];
        foreach ($this->myNumbers as $number) {
            if (in_array($number, $this->winingNumbers, true)) {
                $matches[] = $number;
            }
        }

        return $matches;
    }

    public function countMatches(): int
    {
        if (!isset($this->cachedCount)) {
            $this->cachedCount = count($this->getMatches());
        }

        return $this->cachedCount;
    }

    public function getScore()
    {
        $matches = $this->getMatches();
        if (count($matches) === 0) {
            return 0;
        }

        return 2 ** (count($matches) - 1);
    }
}
