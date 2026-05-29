<?php

delcare(strict_types=1);

namespace travel_page\logic;

use travel_page\model\cityModel;
use travel_page\builder\Builder;

final class SearchCity{
    private const base_url = 'https://nominatim.openstreetmap.org/search';
    private const user_agent = 'Pulpoire-PHP/1.0 (contact@example.com)';
    private timeout = 8;

    public function __construct(
        private readonly int $limit = 5,
        private readonly string $acceptLang ='fr',
    ) {}

    public function search() : array{
        return [];
    }
}