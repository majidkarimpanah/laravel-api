<?php

namespace App\Console\Commands;

use App\Episode;
use App\Title;
use App\Video;
use App\VideoCaption;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CreateDemoStreamVideos extends Command
{
    /**
     * @var string
     */
    protected $signature = 'demo:videos';

    /**
     * @var string
     */
    protected $description = 'Create stream videos for all titles and episodes in demo site.';

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
        $this->createTitleVideos();
        $this->createEpisodeVideos();
        $this->createCaptions();
        $this->info('Videos and captions created successfully.');
    }

    private function createCaptions()
    {
        $this->video
            ->where('name', 'Sintel - (Demo Open Movie)')
            ->orderBy('created_at', 'desc')
            ->whereDoesntHave('captions')
            ->chunkById(100, function(Collection $videos) {
                $enCaptions = $videos->pluck('id')->map(function($videoId) {
                    return $this->getCaptionData('English', 'en', $videoId);
                });
                $ruCaptions = $videos->pluck('id')->map(function($videoId) {
                    return $this->getCaptionData('Russian', 'ru', $videoId);
                });
                $frCaptions = $videos->pluck('id')->map(function($videoId) {
                    return $this->getCaptionData('French', 'fr', $videoId);
                });
                $this->caption->insert($enCaptions->merge($ruCaptions)->merge($frCaptions)->toArray());
            });
    }

    private function createEpisodeVideos()
    {
        $this->episode
            ->whereDoesntHave('stream_videos')
            ->chunkById(100, function(Collection $episodes) {
                $episodeVideos = $episodes->map(function(Episode $episode) {
                    return $this->getVideoData($episode->title_id, $episode->season_number, $episode->episode_number);
                });
                $this->video->insert($episodeVideos->toArray());

            });
    }

    private function createTitleVideos()
    {
        $this->title
            ->where('is_series', false)
            ->whereDoesntHave('stream_videos')
            ->select('id')
            ->chunkById(100, function(Collection $titles) {
                $titleVideos = $titles->map(function($title) {
                    return $this->getVideoData($title->id);
                });
                $this->video->insert($titleVideos->toArray());
            });
    }

    private function getVideoData($titleId, $seasonNum = null, $episodeNum = null)
    {
        $videoUrl = url('storage/sintel/video.mp4');

        return [
            'name' => 'Sintel - (Demo Open Movie)',
            'url' => $videoUrl,
            'type' => 'video',
            'category' => 'full',
            'quality' => 'hd',
            'title_id' => $titleId,
            'season' => $seasonNum,
            'episode' => $episodeNum,
            'source' => 'local',
            'approved' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'user_id' => 1,
            'language' => 'en'
        ];
    }

    private function getCaptionData($name, $lang, $videoId)
    {
        return [
            'name' => $name,
            'language' => $lang,
            'hash' => Str::random(36),
            'url' => url("storage/sintel/sintel_$lang.srt"),
            'user_id' => 1,
            'video_id' => $videoId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
