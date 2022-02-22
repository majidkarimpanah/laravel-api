<?php

namespace App\Services\People\Retrieve;

use App\Person;
use App\Title;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetPersonCredits
{
    /**
     * @var int|null
     */
    private $titleId;

    public function execute(Person $person, $options = [])
    {
        $this->titleId = Arr::get($options, 'titleId');
        $credits = $this->titleId ? [] : $this->getTitleCredits($person);
        $seasonCredits = $this->getSeasonCredits($person);
        $episodeCredits = $this->getEpisodeCredits($person);

        $mergedCredits = $this->mergeCredits(
            Arr::get($credits, 'all', []),
            $seasonCredits,
            $episodeCredits,
        );
        $mergedCredits = $this->separateSelfCreditsAndSort($mergedCredits);

        return [
            'credits' => $mergedCredits,
            'knownFor' => Arr::get($credits, 'knownFor', []),
        ];
    }

    private function mergeCredits($credits1, $credits2, $credits3): array
    {
        $mergedCredits = array_merge_recursive($credits1, $credits2, $credits3);

        return array_map(function ($titles) {
            // sort titles by year
            usort($titles, function ($a, $b) {
                return $b['year'] - $a['year'];
            });

            $unique = [];

            // if this title already exists and existing
            // title has episodes property, continue,
            // otherwise push title into 'unique' array
            foreach ($titles as $title) {
                $existing = Arr::get($unique, $title['id']);
                if ($existing) {
                    $existing['credited_episode_count'] =
                        Arr::get($existing, 'credited_episode_count', 0) +
                        Arr::get($title, 'credited_episode_count', 0);
                    $existing['episodes'] = collect(
                        array_merge(
                            Arr::get($existing, 'episodes', []),
                            Arr::get($title, 'episodes', []),
                        ),
                    )
                        ->unique('id')
                        ->toArray();
                    if (!$this->titleId) {
                        $existing['episodes'] = array_slice(
                            $existing['episodes'],
                            0,
                            5,
                        );
                    }
                    $unique[$title['id']] = $existing;
                } else {
                    $unique[$title['id']] = $title;
                }
            }

            return array_values($unique);
        }, $mergedCredits);
    }

    /**
     * @param Person $person
     * @return array
     */
    private function getTitleCredits(Person $person)
    {
        $credits = $person->credits();

        if (app(Settings::class)->get('streaming.show_label')) {
            $credits->withCount('stream_videos');
        }

        $credits = $credits->get();

        // generate known for list for actors "known_for" department.
        $allKnownFor = $credits
            ->filter(function (Title $credit) use ($person) {
                $knownFor =
                    strtolower($person->known_for) === 'acting'
                        ? 'cast'
                        : $person->known_for;
                return $credit->pivot->department === strtolower($knownFor);
            })
            ->unique();

        $knownFor = $allKnownFor->where('pivot.order', '<', 10);

        if ($knownFor->count() < 4) {
            $knownFor = $allKnownFor;
        }

        // sort by person credit "order" for title as well as title popularity
        $knownFor = $knownFor
            ->sortBy(function ($title) {
                $order = $title->pivot->order;
                $popularity = $title->popularity;
                return $order - $popularity;
            })
            ->slice(0, 4)
            ->values();

        // cast to array, so poster/backdrop is not removed later.
        $knownFor = $knownFor->toArray();

        // remove any data not needed to render person filmography
        $credits = $credits
            ->map(function (Title $credit) {
                unset($credit['backdrop']);
                return $credit;
            })
            ->groupBy('pivot.department');

        return ['all' => $credits->toArray(), 'knownFor' => $knownFor];
    }

    /**
     * Get credits for all series seasons person is attached to.
     *
     * @param Person $person
     * @return array
     */
    private function getSeasonCredits(Person $person)
    {
        $seasons = $person
            ->seasonCredits($this->titleId)
            ->with([
                'title' => function ($query) {
                    return $query->select('id', 'name', 'year', 'poster');
                },
                'episodes' => function ($query) {
                    return $query
                        ->select(
                            'id',
                            'name',
                            'year',
                            'season_id',
                            'season_number',
                            'episode_number',
                            'title_id',
                            'release_date',
                        )
                        ->orderBy('season_number', 'desc')
                        ->orderBy('episode_number', 'desc');
                },
            ])
            ->get();

        // group all seasons by department, for example "production"
        $groupedSeasons = $seasons->groupBy('pivot.department');

        return $groupedSeasons
            ->map(function (Collection $departmentGroup) {
                $seasonsGroupedByTitle = $departmentGroup->groupBy('title.id');

                // attach episodes from all seasons to title
                return $seasonsGroupedByTitle
                    ->map(function (Collection $titleSeasons) {
                        $title = $titleSeasons->first()->title->toArray();

                        //get episodes from each season and move season "pivot" data to each episode
                        $episodesFromAllSeasons = $titleSeasons
                            ->pluck('episodes')
                            ->flatten()
                            ->values()
                            ->map(function ($episode) use ($titleSeasons) {
                                $episode->pivot = $titleSeasons
                                    ->first()
                                    ->pivot->toArray();
                                return $episode;
                            });
                        $title[
                            'credited_episode_count'
                        ] = $episodesFromAllSeasons->count();
                        if (!$this->titleId) {
                            $episodesFromAllSeasons = $episodesFromAllSeasons->take(
                                5,
                            );
                        }
                        $title['episodes'] = $episodesFromAllSeasons->toArray();
                        return $title;
                    })
                    ->values();
            })
            ->toArray();
    }

    /**
     * Get all individual episodes person is credited for.
     *
     * This will return array grouped by department, and
     * series with all episodes person is credited for attached
     * to that series.
     *
     * @param Person $person
     * @return array
     */
    private function getEpisodeCredits(Person $person)
    {
        $episodes = $person
            ->episodeCredits($this->titleId)
            ->with([
                'title' => function (BelongsTo $query) {
                    $query->select('id', 'name', 'year', 'poster');
                },
            ])
            ->get();

        $groupedByDep = $episodes->groupBy('pivot.department');

        return $groupedByDep
            ->map(function (Collection $episodes) {
                return $episodes
                    ->groupBy('title.id')
                    ->map(function (Collection $episodes) {
                        if (!$episodes->first()->title) {
                            return null;
                        }
                        $title = $episodes->first()->title->toArray();
                        $episodes = $episodes->map(function ($episode) {
                            unset($episode->title);
                            return $episode;
                        });
                        $title['credited_episode_count'] = $episodes->count();
                        if (!$this->titleId) {
                            $episodes = $episodes->take(5);
                        }
                        $title['episodes'] = $episodes->toArray();
                        return $title;
                    })
                    ->filter()
                    ->values();
            })
            ->toArray();
    }

    private function separateSelfCreditsAndSort(array $credits): array
    {
        $cast = [];
        $self = [];
        foreach ($credits['cast'] as $credit) {
            $char = isset($credit['pivot']['character'])
                ? strtolower($credit['pivot']['character'])
                : null;
            if (
                $char &&
                ($char === 'self' || Str::contains($char, 'himself'))
            ) {
                $self[] = $credit;
            } else {
                $cast[] = $credit;
            }
        }
        $credits['cast'] = $cast;

        // sort before adding "self" to array as that should be last always
        uksort($credits, function ($a, $b) use ($credits) {
            return count($credits[$b]) - count($credits[$a]);
        });

        $credits['self'] = $self;
        return $credits;
    }
}
