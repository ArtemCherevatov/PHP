<?php
require_once __DIR__ . '/Team.php';

class PromotedTeam extends Team {
    private string $previousLeague;

    public function __construct(string $name, string $city, int $points, string $previousLeague) {
        parent::__construct($name, $city, $points);
        $this->previousLeague = $previousLeague;
    }

    public function getInfo(): string {
        return parent::getInfo() . " [Підвищено з ліги: {$this->previousLeague}]";
    }
}