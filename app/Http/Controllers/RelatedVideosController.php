<?php

namespace App\Http\Controllers;

use App\Episode;
use App\Services\Titles\Retrieve\GetRelatedTitles;
use App\Title;
use App\Video;
use Common\Core\BaseController;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RelatedVideosController extends BaseController
{
    /**
     * @var Title
     */
    private $title;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Video
     */
    private $video;
    /**
     * @var Episode
     */
    private $episode;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Request $request
     * @param Title $title
     * @param Video $video
     * @param Episode $episode
     * @param Settings $settings
     */
    public function __construct(
        Request $request,
        Title $title,
        Video $video,
        Episode $episode,
        Settings $settings
    ) {
        $this->title = $title;
        $this->request = $request;
        $this->video = $video;
        $this->episode = $episode;
        $this->settings = $settings;
    }

    public function index()
    {
        $seasonNum = $this->request->get('season');
        $episodeNum = $this->request->get('episode');
        $titleId = $this->request->get('titleId');

        if ($seasonNum && $episodeNum && app(Settings::class)->get('player.show_next_episodes')) {
            $videos = $this->videosByEpisode($titleId, $seasonNum, $episodeNum);
        } else {
            $videos = $this->videosByTags($titleId);
        }

        return $this->success(['videos' => $videos]);
    }

    private function videosByEpisode($titleId, $seasonNum, $episodeNum)
    {
        list($col, $direction) = explode(':', app(Settings::class)->get('streaming.default_sort'));

        $prevSeasonNum = $seasonNum - 1;
        $nextSeasonNum = $seasonNum + 1;

        $videos = $this->video
            ->with('title', 'captions', 'episode')
            ->where('title_id', $titleId)
            ->where('approved', true)
            ->where('category', 'full')
            ->whereNotNull('episode_id')
            ->whereIn('season_num', [$seasonNum, $nextSeasonNum, $prevSeasonNum])
            ->orderBy($col, $direction)
            ->groupBy(['season_num', 'episode_num'])
            ->get();

        if ($videos->isEmpty()) {
            return [];
        }

        $grouped = $videos->groupBy('season_num')->map(function(Collection $videos, $seasonNum) use($prevSeasonNum, $nextSeasonNum) {
            if ($seasonNum === $prevSeasonNum) {
                return $videos->sortByDesc('episode_num')->first();
            } else if ($seasonNum === $nextSeasonNum) {
                return $videos->sortByDesc('episode_num')->last();
            } else {
                // current season episodes
                return $videos->sortBy('season')->sortBy('episode_num');
            }
        });

        $videos = $grouped->get($seasonNum)->values();

        // make sure prev season last episode appears first
        if ($prevSeason = $grouped->get($prevSeasonNum)) {
            $videos->prepend($prevSeason);
        }

        // make sure next season first episode appears last
        if ($nextSeason = $grouped->get($nextSeasonNum)) {
            $videos->push($nextSeason);
        }

        $videos->transform(function(Video $video) {
            $episode = $video->episode;
            $sNum = $episode->season_number < 10 ? ('0' . $episode->season_number) : $episode->season_number;
            $eNum = $episode->episode_number < 10 ? ('0' . $episode->episode_number)  : $episode->episode_number;
            $video->name = "(s{$sNum}e{$eNum}) - {$episode->name}";
            $video->description = Str::limit($episode->description, 35);
            $video->thumbnail = $episode->poster;
            $video->setRelation('episode', null);
            return $video;
        });

        return $videos->values();
    }

    private function videosByTags($titleId)
    {
        $title = $this->title
            ->with('keywords', 'genres')
            ->findOrFail($titleId);

        $related = app(GetRelatedTitles::class)->execute($title);
        $videos = [];

        if ($related->isNotEmpty()) {
            $related->load(['videos' => function(HasMany $builder) {
                $contentType = $this->settings->get('streaming.video_panel_content');
                if ($contentType === 'all') return;
                if ($contentType === 'full') {
                    $builder->where('category', 'full');
                } else if ($contentType === 'short') {
                    $builder->where('category', '!=', 'full');
                } else {
                    $builder->where('category', $contentType);
                }
            }]);
            $videos = $related->map(function(Title $title) {
                if ($video = $title->videos->first()) {
                    $video = $title->videos->first();
                    $title->setRelation('videos', []);
                    $video->title = $title;
                    return $video;
                }
            })->filter()->values();
        }

        return $videos;
    }
}
