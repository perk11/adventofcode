<?php

$input = fopen('input', 'rb');
$total = 0;
while ($line = fgets($input)) {
    $game = Game::fromString($line);
    $total += $game->getScore();
}
echo $total . "\n";


class Game
{
    public static function fromString(string $string): self
    {
        $game = new self();
        $lineParts = explode(' ', trim($string));
        $game->opponentsMove = $lineParts[0];
        $game->playersMove = $lineParts[1];
        return $game;
    }
    public string $opponentsMove;

    public string $playersMove;

    public function getWinStatus(): int
    {
        if ($this->opponentsMove === 'A' && $this->playersMove === 'X') {
            return 0;
        }
        if ($this->opponentsMove === 'B' && $this->playersMove === 'Y') {
            return 0;
        }
        if ($this->opponentsMove === 'C' && $this->playersMove === 'Z') {
            return 0;
        }
        if ($this->opponentsMove === 'A' && $this->playersMove === 'Y') {
            return 1;
        }
        if ($this->opponentsMove === 'A' && $this->playersMove === 'Z') {
            return -1;
        }
        if ($this->opponentsMove === 'B' && $this->playersMove === 'X') {
            return -1;
        }
        if ($this->opponentsMove === 'B' && $this->playersMove === 'Z') {
            return 1;
        }
        if ($this->opponentsMove === 'C' && $this->playersMove === 'X') {
            return 1;
        }
        if ($this->opponentsMove === 'C' && $this->playersMove === 'Y') {
            return -1;
        }
        throw new \Exception("unknown game state");
    }

    public function getWinScore(): int
    {
        switch ($this->getWinStatus()) {
            case -1:
                return 0;
            case 0:
                return 3;
            case 1:
                return 6;
        }
    }

    public function getChoiceScore(): int
    {
        switch ($this->playersMove) {
            case 'X':
                return 1;
            case 'Y':
                return 2;
            case 'Z':
                return 3;
        }
    }

    public function getScore(): int
    {
        return $this->getWinScore() + $this->getChoiceScore();
    }
}
