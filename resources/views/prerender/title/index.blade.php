@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    @include('prerender.menu')

    <h1>{{ __('Browse') }}</h1>

    <p>{{ $meta->getDescription() }}</p>

    <ul style="display: flex; flex-wrap: wrap;">
        @foreach($meta->getData('pagination') as $title)
            <li>
                <figure>
                    <img src="{{$meta->urls->mediaImage($title)}}" alt="Title poster" width="270px">
                    <figcaption>
                        <a href="{{$meta->urls->title($title)}}">{{$title['name']}}</a>
                    </figcaption>
                </figure>
            </li>
        @endforeach

        {{ $meta->getData('pagination')->withPath('browse')->links() }}
    </ul>
@endsection