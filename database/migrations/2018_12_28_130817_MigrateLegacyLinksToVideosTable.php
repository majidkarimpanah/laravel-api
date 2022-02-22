<?php

use App\Video;
use Illuminate\Support\Collection;
use Illuminate\Database\Migrations\Migration;

class MigrateLegacyLinksToVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('links')->orderBy('id')->chunk(50, function (Collection $links) {
            $videos = $links->map(function($link) {
                return [
                    'name' => $link->label,
                    'type' => $link->type,
                    'url' => $link->url,
                    'title_id' => $link->title_id,
                    'season' => $link->season,
                    'episode' => $link->episode,
                    'reports' => $link->reports,
                    'created_at' => $link->created_at,
                    'updated_at' => $link->updated_at,
                    'positive_votes' => $link->positive_votes,
                    'negative_votes' => $link->negative_votes,
                    'quality' => $link->quality,
                    'approved' => $link->approved,
                    'source' => 'local',
                ];
            });

            try {
                app(Video::class)->insert($videos->toArray());
            } catch (\Exception $e) {
                //
            }
        });
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
