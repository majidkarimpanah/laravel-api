@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    @include('prerender.menu')

    <h1>{{ $meta->getTitle() }}</h1>

    <img src="{{url($meta->get('og:image'))}}" alt="Title poster" width="270px">

    @if($seasons = $meta->getData('title.seasons'))
        <div>
            <h3>{{__('Seasons')}}</h3>
            <ul>
                @foreach($seasons as $season)
                    <li><a href="{{$meta->urls->season($season)}}">{{$season['number']}}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($genres = $meta->getData('title.genres'))
        <div>
            <h3>{{__('Genres')}}</h3>
            <ul>
                @foreach($genres as $genre)
                    <li><a href="{{$meta->urls->genre($genre)}}">{{$genre['name']}}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    <dl>
        <dt>{{__('User Rating')}}</dt>
        <dd>{{$meta->getData('title.rating')}}</dd>

        <dt>{{__('Running Time')}}</dt>
        <dd>{{$meta->getData('title.runtime')}}</dd>

        @if($epCount = $meta->getData('title.episode_count'))
            <dt>{{__('Episodes')}}</dt>
            <dd>{{$epCount}}</dd>
        @endif

        @if($cert = $meta->getData('title.certification'))
            <dt>{{__('Certification')}}</dt>
            <dd>{{$cert}}</dd>
        @endif

        @if($tagline = $meta->getData('title.tagline'))
            <dt>{{__('Tagline')}}</dt>
            <dd>{{$tagline}}</dd>
        @endif

        @if($originalTitle = $meta->getData('title.original_title'))
            <dt>{{__('Original Title')}}</dt>
            <dd>{{$originalTitle}}</dd>
        @endif

        <dt>{{__('Release Date')}}</dt>
        <dd>{{$meta->getData('title.release_date')}}</dd>

        @if( ! $meta->getData('title.is_series'))
            <dt>{{__('Budget')}}</dt>
            <dd>{{$meta->getData('title.budget')}}</dd>

            <dt>{{__('Revenue')}}</dt>
            <dd>{{$meta->getData('title.revenue')}}</dd>
        @endif
    </dl>

    <p>{{ $meta->getDescription() }}</p>

    @if($credits = $meta->getData('title.credits'))
        <div>
            <h3>{{__('Credits')}}</h3>
            <ul style="display: flex; flex-wrap: wrap;">
                @foreach($credits as $credit)
                    <li>
                        <figure>
                            <img src="{{$meta->urls->mediaImage($credit)}}" alt="Credit poster" width="270px">
                            <figcaption>
                                <dl>
                                    <dt>{{__('Job')}}</dt>
                                    <dd>{{$credit['pivot']['job']}}</dd>
                                    <dt>{{__('Department')}}</dt>
                                    <dd>{{$credit['pivot']['department']}}</dd>
                                    @if($credit['pivot']['character'])
                                        <dt>{{__('Character')}}</dt>
                                        <dd>{{$credit['pivot']['character']}}</dd>
                                    @endif
                                </dl>
                                <a href="{{$meta->urls->person($credit)}}">{{$credit['name']}}</a>
                            </figcaption>
                        </figure>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($videos = $meta->getData('title.videos'))
        <div>
            <h3>{{__('Videos')}}</h3>
            <ul style="display: flex; flex-wrap: wrap">
                @foreach($videos as $video)
                    <li>
                        <figure>
                            <img src="{{$meta->urls->mediaImage($video['thumbnail'] ?: $meta->get('og:image'))}}" alt="Video thumbnail" width="180px">
                            <figcaption>{{$video['name']}}</figcaption>
                        </figure>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($reviews = $meta->getData('title.reviews'))
        <div>
            <h3>{{__('Reviews')}}</h3>
            <ul style="display: flex; flex-wrap: wrap">
                @foreach($reviews as $review)
                    @if($review['type'] === 'user')
                        <li>
                            <h4>{{$review['author']}}</h4>
                            <p>{{$review['body']}}</p>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if($images = $meta->getData('title.images'))
        <div>
            <h3>{{__('Images')}}</h3>
            <ul style="display: flex; flex-wrap: wrap">
                @foreach($images as $image)
                    <li><img src="{{$meta->urls->mediaImage($image)}}" alt="Media image" width="270px"></li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
