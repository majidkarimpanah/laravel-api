<?php

namespace App\Services\People\Store;

use App\Person;
use App\Services\Titles\Store\StoreCredits;
use App\Services\Traits\StoresMediaImages;

class StorePersonData
{
    use StoresMediaImages;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var array
     */
    private $data;

    /**
     * @param Person $person
     * @param array $data
     * @return Person
     */
    public function execute(Person $person, $data)
    {
        $this->person = $person;
        $this->data = $data;

        $this->persistData();
        $this->persistRelations();

        return $this->person;
    }

    private function persistData()
    {
        $personData = array_filter($this->data, function ($value) {
            // make sure we don't overwrite existing values with null
            return !is_array($value) && !is_null($value);
        });

        $this->person->fill($personData)->save();
    }

    private function persistRelations()
    {
        $relations = array_filter($this->data, function ($value) {
            return is_array($value);
        });

        foreach ($relations as $name => $values) {
            switch ($name) {
                case 'credits':
                    app(StoreCredits::class)->execute($this->person, $values);
                    break;
                case 'images':
                    $this->storeImages($values, $this->person);
                    break;
            }
        }
    }

}
