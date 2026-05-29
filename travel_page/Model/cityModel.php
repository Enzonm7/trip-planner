<?php

declare(strict_types=1);

namespace travel_page\builder;

final class cityModel{

    public function __construct(
        private readonly string $name,
        private float $latitude,
        private float $longitude,
        private string $displayname,
        private string $country
    ){}

    public function getName(): string
    {
        return $this->name;
    }
    public function getLatitude(): float
    {
        return $this->latitude;
    }
    public function getLongitude(): float
    {
        return $this->longitude;
    }
    public function getDisplayname(): string
    {
        return $this->displayname;
    }
    public function getCountry(): string
    {
        return $this->country;
    }

    public function toArray(): array
    {
        return [
            'name'=> $this->name,
            'latitude'=> $this->latitude,
            'longitude'=> $this->longitude,
            'displayname'=> $this->displayname,
            'country'=> $this->country,
        ];
    }
}