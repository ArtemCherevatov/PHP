<?php
class Team {
    protected string $name;
    protected string $city;
    protected int $points;

    public function __construct(string $name, string $city, int $points) {
        $this->name = $name;
        $this->city = $city;
        $this->points = $points;
    }

    public function getInfo(): string {
        return "Команда: {$this->name} (м. {$this->city})";
    }

    public function getCity(): string { return $this->city; }
    public function getPoints(): int { return $this->points; }
    public function getName(): string { return $this->name; }
}