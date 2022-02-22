@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    @include('prerender.menu')

    <h1>{{$meta->getData('title.name')}}: {{__('Season')}} {{$meta->getData('title.season.number')}}</h1>

    @if($seasons = $meta->getData('title.seasons'))
        <div>
            <h3>{{__('Seasons')}}</h3>
            <ul>
                @foreach($seasons as $season)
                    <li><a href="{{$meta->urls->season($season->toArray())}}">{{$season['number']}}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <ul>
            @foreach($meta->getData('title.season.episodes') as $episode)
                <li>
                    <figure>
                        <img src="{{$meta->urls->mediaImage($episode)}}" alt="Episode poster" width="270px">
                        <figcaption>
                            <a href="{{$meta->urls->episode($episode)}}">{{$episode['name']}}</a>
                        </figcaption>
                    </figure>
                </li>
            @endforeach
        </ul>
    </div>
@endsection