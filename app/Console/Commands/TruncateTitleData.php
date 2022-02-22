<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class TruncateTitleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'titles:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all title related database tables.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm('Are you sure?')) {
            DB::table('creditables')->truncate();
            DB::table('episodes')->truncate();
            DB::table('images')->truncate();
            DB::table('listables')->truncate();
            DB::table('people')->truncate();
            DB::table('reviews')->truncate();
            DB::table('seasons')->truncate();
            DB::table('taggables')->truncate();
            DB::table('tags')->truncate();
            DB::table('titles')->truncate();
            DB::table('videos')->truncate();
            DB::table('video_ratings')->truncate();
            DB::table('video_captions')->truncate();
            DB::table('video_reports')->truncate();
        }
    }
}
