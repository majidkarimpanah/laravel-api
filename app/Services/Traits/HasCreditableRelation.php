<?php

namespace App\Services\Traits;

use App\Person;
use DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasCreditableRelation
{
    /**
     * @return BelongsToMany
     */
    public function credits()
    {
        $query = $this->morphToMany(Person::class, 'creditable')
            ->withPivot(['id', 'job', 'department', 'order', 'character']);

        $query = $query->select(['people.id', 'name', 'poster']);

        // order by department first, so we always get director,
        // writers and creators, even if limit is applied to this query
        $prefix = DB::getTablePrefix();
        return $query->orderBy(DB::raw("FIELD(department, 'directing', 'creators', 'writing', 'cast')"))
            ->orderBy(DB::raw("-{$prefix}creditables.order"), 'desc'); // should be "desc" and not "asc" because "minus" is added which will reverse order
    }
}