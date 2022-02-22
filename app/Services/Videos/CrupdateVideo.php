<?php

namespace App\Services\Videos;

use App\Episode;
use App\Season;
use App\Video;
use Auth;
use Common\Settings\Settings;
use Illuminate\Support\Arr;

class CrupdateVideo
{
    /**
     * @var Video
     */
    private $video;

    /**
     * @var Episode
     */
    private $episode;

    /**
     * @var Season
     */
    private $season;

    /**
     * @param Video $video
     * @param Episode $episode
     * @param Season $season
     */
    public function __construct(Video $video, Episode $episode, Season $season)
    {
        $this->video = $video;
        $this->episode = $episode;
        $this->season = $season;
    }

    /**
     * @param array $params
     * @param int|null $videoId
     * @return Video
     */
    public function execute($params, $videoId = null)
    {
        if (Arr::get($params, 'season')) {
            $episode = $this->getOrCreateEpisode($params);
            $params['episode_id'] = $episode->id;
        }

        $params['positive_votes'] = 0;
        $params['negative_votes'] = 0;
        $params['source'] = 'local';

        if ($videoId) {
            $video = $this->video->findOrFail($videoId);
            $video->fill($params)->save();
        } else {
            $params['approved'] = $this->shouldAutoApprove() ? true : false;
            $params['user_id'] = Auth::id();
            $video = $this->video->create($params);
        }

        return $video;
    }

    private function shouldAutoApprove()
    {
        return app(Settings::class)->get('streaming.auto_approve') || Auth::user()->hasPermission('admin');
    }

    private function getOrCreateEpisode($params)
    {
        $seasonNumber = Arr::get($params, 'season');
        $titleId = Arr::get($params, 'title_id');
        $episodeNumber = Arr::get($params, 'episode');

        $episode = $this->episode
            ->where('title_id', $titleId)
            ->where('episode_number', $episodeNumber)
            ->where('season_number', $seasonNumber)
            ->first();

        if ( ! $episode) {
            $season = $this->season
                ->where('number', $seasonNumber)
                ->where('title_id', $titleId)
                ->first();

            $episode = $season->episodes()->create([
                'title_id' => $titleId,
                'episode_number' => $episodeNumber,
                'season_number' => $seasonNumber,
            ]);
        }

        return $episode;
    }
}
