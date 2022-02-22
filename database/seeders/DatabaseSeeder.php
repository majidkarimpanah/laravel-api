<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(WatchlistSeeder::class);
        if (config('common.site.demo')) {
            $this->call(DefaultListsSeeder::class);
        }
    }
}
