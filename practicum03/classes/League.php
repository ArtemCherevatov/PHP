<?php
class League {
    private array $teams = [];

    public function addTeam(Team $team): void {
        $this->teams[] = $team;
    }

    public function standings(): array {
        $sortedTeams = $this->teams;
        // Сортування за кількістю очок за спаданням
        usort($sortedTeams, function($a, $b) {
            return $b->getPoints() <=> $a->getPoints();
        });
        return $sortedTeams;
    }

    public function findByCity(string $city): array {
        return array_filter($this->teams, function($team) use ($city) {
            return mb_strtolower($team->getCity()) === mb_strtolower($city);
        });
    }
}