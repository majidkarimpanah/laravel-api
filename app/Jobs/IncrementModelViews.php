<?php namespace App\Jobs;

use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Session\Store;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class IncrementModelViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $modelId;
    /**
     * @var string
     */
    private $type;

    /**
     * @param 'person'|'title' $type
     * @param int $modelId
     */
    public function __construct($type, $modelId)
    {
        $this->type = $type;
        $this->modelId = $modelId;
    }

    /**
     * Execute the console command.
     *
     * @param Store $session
     * @return void
     */
    public function handle(Store $session)
    {
        if ( ! $this->shouldIncrement($session)) return;

        $session->put("{$this->type}-views.{$this->modelId}", Carbon::now()->timestamp);

        $this->incrementViews();
    }

    /**
     * Check if model views should be incremented.
     *
     * @param Store $session
     * @return boolean
     */
    private function shouldIncrement(Store $session)
    {
        $views = $session->get("{$this->type}-views");

        //user has not viewed this model yet
        if ( ! $views || ! isset($views[$this->modelId])) return true;

        //see if user last viewed this model over 10 hours ago
        $time = Carbon::createFromTimestamp($views[$this->modelId]);

        return Carbon::now()->diffInHours($time) > 10;
    }

    /**
     * Increment views or plays of specified model.
     */
    private function incrementViews()
    {
        $table = $this->type === 'title' ? 'titles' : 'people';
        DB::table($table)->where('id', $this->modelId)->increment('views');
    }
}
