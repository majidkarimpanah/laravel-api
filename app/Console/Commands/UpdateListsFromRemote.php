<?php

namespace App\Console\Commands;

use App\ListModel;
use App\Services\Lists\UpdateListsContent;
use Illuminate\Console\Command;

class UpdateListsFromRemote extends Command
{
    /**
     * @var string
     */
    protected $signature = 'lists:update';

    /**
     * @var string
     */
    protected $description = 'Update marked lists from selected remote data provider.';

    /**
     * @var ListModel
     */
    private $list;

    /**
     * @param ListModel $list
     */
    public function __construct(ListModel $list) {
        parent::__construct();
        $this->list = $list;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $lists = $this->list
            ->whereNotNull('auto_update')
            ->limit(10)
            ->get();

        app(UpdateListsContent::class)->execute($lists);

        $this->info('Lists updated.');
    }
}
