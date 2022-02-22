<?php

namespace App\Services\Data\Contracts;

use App\Person;
use App\Title;
use Illuminate\Support\Collection;

interface DataProvider
{
    /**
     * @param Title $title
     * @return array
     */
    public function getTitle(Title $title);

    /**
     * @param Person $person
     * @return array
     */
    public function getPerson(Person $person);

    /**
     * @param Title $title
     * @param $seasonNumber
     * @return array
     */
    public function getSeason(Title $title, $seasonNumber);

    /**
     * @param string $query
     * @param array $params
     * @return Collection
     */
    public function search($query, $params = []);


    /**
     * @param string $titleType movie or series
     * @param string $titleCategory upcoming, popular, latest etc
     * @return Collection
     */
    public function getTitles($titleType, $titleCategory);
}