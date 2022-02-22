<?php

namespace App\Console\Commands;

use App\Episode;
use App\Title;
use App\Video;
use App\VideoCaption;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class CreateDemoStreamLinks extends Command
{
    /**
     * @var string
     */
    protected $signature = 'demo:links';

    /**
     * @var string
     */
    protected $description = 'Create stream links for all titles and episodes in demo site.';

    /**
     * @var Title
     */
    private $title;

    /**
     * @var Episode
     */
    private $episode;

    /**
     * @var Video
     */
    private $video;

    /**
     * @var VideoCaption
     */
    private $caption;

    /**
     * @param Title $title
     * @param Episode $episode
     * @param Video $video
     * @param VideoCaption $caption
     */
    public function __construct(Title $title, Episode $episode, Video $video, VideoCaption $caption)
    {
        parent::__construct();
        $this->title = $title;
        $this->episode = $episode;
        $this->video = $video;
        $this->caption = $caption;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->createTitleLinks();
        $this->createEpisodeLinks();
        $this->info('Links created successfully.');
    }

    private function createTitleLinks()
    {
        $this->title
            ->where('is_series', false)
            ->whereDoesntHave('stream_videos')
            ->select('id')
            ->chunkById(100, function(Collection $titles) {
                $titleVideos = $titles->pluck('id')->map(function($titleId) {
                    return $this->getVideosData($titleId);
                })->flatten(1);
                $this->video->insert($titleVideos->toArray());
            });
    }

    private function createEpisodeLinks()
    {
        $this->episode
            ->whereDoesntHave('stream_videos')
            ->chunkById(100, function(Collection $episodes) {
                $episodeVideos = $episodes->map(function(Episode $episode) {
                    return $this->getVideosData($episode->title_id, $episode->season_number, $episode->episode_number);
                })->flatten(1);
                $this->video->insert($episodeVideos->toArray());
            });
    }

    private function getVideosData($titleId, $seasonNum = null, $episodeNum = null)
    {
        $sharedData = [
            'category' => 'full',
            'title_id' => $titleId,
            'season' => $seasonNum,
            'episode' => $episodeNum,
            'source' => 'local',
            'approved' => true,
            'updated_at' => Carbon::now(),
            'user_id' => 1,
        ];

        $urls = [
            'https://www.youtube.com/embed/ByXuk9QqQkk',
            'https://player.vimeo.com/video/208890816',
            'https://www.dailymotion.com/embed/video/x4qi23d',
            'https://www.youtube.com/embed/xU47nhruN-Q',
            'https://google.com',
        ];

        $languages = ['en', 'ru', 'fr', 'en', 'en'];

        $videos = [];
        for ($i = 0; $i <= 4; $i++) {
            $num = $i+1;

            $videos[] = array_merge($sharedData, [
                'name' => "Mirror $num",
                'url' => $urls[$i],
                'quality' => $i === 3 ? '4k' : 'hd',
                'language' => $languages[$i],
                'type' => $i === 4 ? 'external' : 'embed',
                'positive_votes' => rand(1, 200),
                'negative_votes' => rand(1, 30),
                'created_at' => Carbon::now()->subDay(rand(1, 20)),
            ]);
        }
        return $videos;
    }
}
