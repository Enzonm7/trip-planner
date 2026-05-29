<?php

declare(strict_types=1);

use travel_page\Model\cityModel;

namespace travel_page\builder;

final class Builder{
    private string $name = "";
    private float $latitude = 0.0;
    private float $longitude = 0.0;
    private string $displayname = "";
    private string $region = "";

    public function Fromnominatim(array $data): self
    {
        $builder = new self();
        $builder->name = $data['name'] ??  ($data['address']['city'] ?? ($data['address']['town'] ?? ''));
        $builder->latitude = (float) ($data['lat'] ?? 0.0);
        $builder->longitude = (float) ($data['lon'] ?? 0.0);
        $builder->displayname = $data['display_name'] ?? '';
        $builder->region = $data['address']['county'] ?? ''; 
        return $builder;
    }

    public function Withname(string $name): self
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function build() : 

}