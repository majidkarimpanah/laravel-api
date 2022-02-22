<?php

use App\ListModel;
use Illuminate\Database\Migrations\Migration;

class UpdateListMediaView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ListModel::where('style', 'portrait-grid')->update([
            'style' => 'portrait',
        ]);
        ListModel::where('style', 'landscape-grid')->update([
            'style' => 'landscape',
        ]);
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
