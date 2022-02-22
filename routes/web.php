<?php

use App\Http\Controllers\UserProfileController;

Route::group(['prefix' => 'secure'], function () {
    // titles
    Route::get('movies/{id}', 'TitleController@show');
    Route::get('series/{id}', 'TitleController@show');
    Route::get('titles/{id}', 'TitleController@show');
    Route::get('titles/{id}/related', 'RelatedTitlesController@index');
    Route::get('titles', 'TitleController@index');
    Route::post('titles', 'TitleController@store');
    Route::post('titles/credits', 'TitleCreditController@store');
    Route::post('titles/credits/reorder', 'TitleCreditController@changeOrder');
    Route::put('titles/credits/{id}', 'TitleCreditController@update');
    Route::delete('titles/credits/{id}', 'TitleCreditController@destroy');
    Route::put('titles/{id}', 'TitleController@update');
    Route::delete('titles', 'TitleController@destroy');

    // seasons
    Route::post('titles/{titleId}/seasons', 'SeasonController@store');
    Route::delete('seasons/{seasonId}', 'SeasonController@destroy');

    // episodes
    Route::get('episodes/{id}', 'EpisodeController@show');
    Route::post('seasons/{seasonId}/episodes', 'EpisodeController@store');
    Route::put('episodes/{id}', 'EpisodeController@update');
    Route::delete('episodes/{id}', 'EpisodeController@destroy');

    // people
    Route::get('people', 'PersonController@index');
    Route::get('people/{id}', 'PersonController@show');
    Route::get('people/{personId}/full-credits/{titleId}/{department}', 'PersonCreditsController@fullTitleCredits');
    Route::post('people', 'PersonController@store');
    Route::put('people/{id}', 'PersonController@update');
    Route::delete('people', 'PersonController@destroy');

    // search
    Route::get('search/{query}', 'SearchController@index');

    // lists
    Route::get('lists', 'ListController@index');
    Route::post('lists/auto-update-content', 'ListController@autoUpdateContent');
    Route::get('lists/{id}', 'ListController@show');
    Route::post('lists', 'ListController@store');
    Route::put('lists/{id}', 'ListController@update');
    Route::post('lists/{id}/reorder', 'ListOrderController@changeOrder');
    Route::delete('lists/{id}', 'ListController@destroy');
    Route::post('lists/{id}/add', 'ListItemController@add');
    Route::post('lists/{id}/remove', 'ListItemController@remove');

    // homepage
    Route::get('homepage/lists', 'HomepageContentController@show');

    // related videos
    Route::get('related-videos', 'RelatedVideosController@index');

    // images
    Route::post('images', 'ImagesController@store');
    Route::delete('images', 'ImagesController@destroy');
    Route::post('titles/{id}/images/change-order', 'ImageOrderController@changeOrder');

    // reviews
    Route::get('reviews', 'ReviewController@index');
    Route::post('reviews', 'ReviewController@store');
    Route::put('reviews/{id}', 'ReviewController@update');
    Route::delete('reviews/{id}', 'ReviewController@destroy');

    // news
    Route::get('news', 'NewsController@index');
    Route::post('news/import-from-remote-provider', 'NewsController@importFromRemoteProvider');
    Route::get('news/{id}', 'NewsController@show');
    Route::post('news', 'NewsController@store');
    Route::put('news/{id}', 'NewsController@update');
    Route::delete('news', 'NewsController@destroy');

    // videos
    Route::get('videos', 'VideosController@index');
    Route::post('videos', 'VideosController@store');
    Route::put('videos/{id}', 'VideosController@update');
    Route::delete('videos/{ids}', 'VideosController@destroy');
    Route::post('videos/{id}/rate', 'VideoRatingController@rate');
    Route::post('videos/{video}/approve', 'VideoApproveController@approve');
    Route::post('videos/{video}/disapprove', 'VideoApproveController@disapprove');
    Route::post('videos/{video}/report', 'VideoReportController@report');
    Route::post('videos/{video}/log-play', 'VideosController@logPlay');
    Route::post('titles/{video}/videos/change-order', 'VideoOrderController@changeOrder');

    // title tags
    Route::post('titles/{titleId}/tags', 'TitleTagsController@store');
    Route::delete('titles/{titleId}/tags/{type}/{tagId}', 'TitleTagsController@destroy');

    // import
    Route::post('media/import', 'ImportMediaController@importMediaItem');
    Route::get('tmdb/import', 'ImportMediaController@importViaBrowse');

    // CAPTIONS
    Route::apiResource('caption', 'CaptionController');
    Route::post('caption/{videoId}/order', 'CaptionOrderController@changeOrder');

    // USER PROFILE
    Route::get('user-profile/{user}', [UserProfileController::class, 'show']);
    Route::get('user-profile/{user}/lists', [UserProfileController::class, 'loadLists']);
    Route::get('user-profile/{user}/ratings', [UserProfileController::class, 'loadRatings']);
    Route::get('user-profile/{user}/reviews', [UserProfileController::class, 'loadReviews']);
    Route::get('user-profile/{user}/comments', [UserProfileController::class, 'loadComments']);
});

// FRONT-END ROUTES THAT NEED TO BE PRE-RENDERED
$homeController = '\Common\Core\Controllers\HomeController@show';
Route::get('/', 'HomepageContentController@show')->middleware('prerenderIfCrawler');
Route::get('browse', 'TitleController@index')->middleware('prerenderIfCrawler');

// TITLE SHOW
Route::get('titles/{id}', 'TitleController@showWithoutNameParam')->middleware('prerenderIfCrawler');
Route::get('titles/{id}/{name}', 'TitleController@show')->middleware('prerenderIfCrawler');

// EPISODE SHOW
Route::get('titles/{id}/season/{season}/episode/{episode}', 'TitleController@showWithoutNameParam')->middleware('prerenderIfCrawler');
Route::get('titles/{id}/{name}/season/{season}/episode/{episode}', 'TitleController@show')->middleware('prerenderIfCrawler');

// SEASON SHOW
Route::get('titles/{id}/season/{season}', 'TitleController@showWithoutNameParam')->middleware('prerenderIfCrawler');
Route::get('titles/{id}/{name}/season/{season}', 'TitleController@show')->middleware('prerenderIfCrawler');

Route::get('people', 'PersonController@index')->middleware('prerenderIfCrawler');
Route::get('people/{id}', 'PersonController@show')->middleware('prerenderIfCrawler');
Route::get('people/{id}/{name}', 'PersonController@show')->middleware('prerenderIfCrawler');
Route::get('news', 'NewsController@index')->middleware('prerenderIfCrawler');
Route::get('news/{id}', 'NewsController@show')->middleware('prerenderIfCrawler');
Route::get('lists/{id}', 'ListController@show')->middleware('prerenderIfCrawler');

// CATCH ALL ROUTES AND REDIRECT TO HOME
Route::get('{all}', $homeController)->where('all', '.*');
