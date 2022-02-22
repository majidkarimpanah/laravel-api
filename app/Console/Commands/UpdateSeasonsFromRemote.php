<?php

namespace App\Console\Commands;

use App\Season;
use App\Services\Data\Tmdb\TmdbApi;
use App\Services\Titles\Retrieve\LoadSeasonData;
use App\Services\Titles\Store\StoreSeasonData;
use App\Title;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class UpdateSeasonsFromRemote extends Command
{
    /**
     * @var string
     */
    protected $signature = 'seasons:update';

    /**
     * @var string
     */
    protected $description = 'Update all seasons via Themoviedb.';
    /**
     * @var Title
     */
    private $title;

    /**
     * @param Title $title
     */
    public function __construct(Title $title)
    {
        parent::__construct();
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $count = $this->title
            ->where('is_series', true)
            ->whereHas('seasons')
            ->count() / 50;

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $this->title
            ->where('is_series', true)
            ->whereHas('seasons')
            ->chunkById(50, function(Collection $titles) use($bar) {
                $titles->load('seasons');
                $titles->each(function(Title $title) {
                    $title->seasons->each(function(Season $season) use($title) {
                        app(LoadSeasonData::class)
                            ->execute($title, $season->number);
                    });
                });
                $bar->advance();
            });

        $bar->finish();
        $this->info('Seasons updated');
    }
}
