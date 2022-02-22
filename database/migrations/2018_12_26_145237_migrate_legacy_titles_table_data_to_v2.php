<?php

use App\Image;
use App\Person;
use App\Title;
use App\Video;
use Common\Tags\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class MigrateLegacyTitlesTableDataToV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (app(Title::class)->with('seasons')->cursor() as $title) {
            $updatedTitleData = [
                'fully_synced' => false,
                'tmdb_vote_average' => $title->tmdb_vote_average ?: null,
                'budget' => $title->budget ?: null,
                'revenue' => $title->revenue ?: null,
                'imdb_id' => $title->imdb_id ?: null,
                'tmdb_id' => $title->tmdb_id ?: null,
                'release_date' => $title->getOriginal('release_date') ?: null,
                'is_series' => $title->getOriginal('type') === 'series' ? 1 : 0,
                'season_count' => $title->seasons->count(),
            ];

            // trailer
            if ($title->trailer) {
                try {
                    app(Video::class)->create([
                        'name' => $title->name . ' - Trailer',
                        'url' => $title->trailer,
                        'type' => 'embed',
                        'source' => 'tmdb',
                        'title_id' => $title->id,
                    ]);
                } catch (QueryException $e) {
                    // catch video exists errors
                }
            }

            // genres
            $legacyGenres = trim(str_replace(' ', '', $title->genre));
            if ($legacyGenres) {
                $separator = str_contains($legacyGenres, '|') ? '|' : ',';
                $genreNames = explode($separator, $legacyGenres);

                $values = collect($genreNames)->map(function($genreName) {
                    $name = $this->getGenreName($genreName);
                    return [
                        'name' => $name,
                        'display_name' => ucwords($name),
                        'type' => 'genre'
                    ];
                });

                $tags = app(Tag::class)->insertOrRetrieve($values);
                $title->genres()->syncWithoutDetaching($tags->pluck('id'));
            }

            $updatedTitleData['genre'] = null;
            $title->fill($updatedTitleData)->save();
        }

        // images
        $cursor = app(Image::class)
            ->where('model_id', '<', 1)
            ->cursor();

        foreach ($cursor as $image) {
            $image->update([
                'model_type' => Title::class,
                'model_id' => $image->title_id,
                'url' => $image->web,
                'web' => null,
                'type' => $image->type === 'external' ? 'tmdb' : 'local',
            ]);
        }

        // cast
        DB::table('creditables')
            ->whereNull('creditable_type')
            ->update([
                'creditable_type' => Title::class,
                'department' => 'cast',
                'job' => 'cast'
            ]);
    }

    private function getGenreName($originalName)
    {
        $name = strtolower($originalName);
        if ($name === 'sciencefiction') return 'science fiction';
        if ($name === 'action&adventure') return 'action & adventure';
        if ($name === 'sci-fi&fantasy') return 'sci-fi & fantasy';
        if ($name === 'war&politics') return 'war & politics';
        return $name;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
