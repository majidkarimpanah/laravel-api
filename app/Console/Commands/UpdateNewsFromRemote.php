<?php

namespace App\Console\Commands;

use App\NewsArticle;
use App\Services\Data\Contracts\NewsProviderInterface;
use App\Services\News\ImportNewsFromRemoteProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateNewsFromRemote extends Command
{
    /**
     * @var string
     */
    protected $signature = 'news:update';

    /**
     * @var string
     */
    protected $description = 'Update news from currently selected 3rd party site.';


    /**
     * @return void
     */
    public function handle()
    {
        app(ImportNewsFromRemoteProvider::class)->execute();

        $this->info('News updated.');
    }
}
