<?php

namespace App\Services\Data\Contracts;

use Illuminate\Support\Collection;

interface NewsProviderInterface
{
    /**
     * @return Collection
     */
    public function getArticles();
}