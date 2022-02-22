@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    @include('prerender.menu')

    <h1>{{ $meta->getTitle() }}</h1>

    <p>{{ $meta->getDescription() }}</p>

    <h2>{{__('Movies')}}</h2>
    <ul class="movies">
        @foreach($meta->getData('results') as $title)
            <li>
                <figure>
                    <img src="{{$meta->urls->mediaImage($title)}}">
                    <figcaption><a href="{{$meta->urls->title($title)}}">{{$title['name']}}</a></figcaption>
                </figure>
            </li>
        @endforeach
    </ul>
@endsection