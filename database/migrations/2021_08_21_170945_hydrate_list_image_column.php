<?php

use App\ListModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

class HydrateListImageColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ListModel::with('items')
            ->whereNull('image')
            ->chunkById(50, function (Collection $lists) {
                $lists->each(function (ListModel $list) {
                    $list->updateImage();
                });
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
