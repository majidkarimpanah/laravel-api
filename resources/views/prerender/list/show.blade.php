@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    @include('prerender.menu')

    <h1>{{ $meta->getData('list.name') }}</h1>

    <ul style="display: flex; flex-wrap: wrap;">
        @foreach($meta->getData('items') as $item)
            <li>
                <figure>
                    <img src="{{$meta->urls->mediaImage($item)}}" alt="List item poster" width="270px">
                    <figcaption>
                        <a href="{{$meta->urls->title($item)}}">{{$item['name']}}</a>
                    </figcaption>
                </figure>
            </li>
        @endforeach
    </ul>

    {{ $meta->getData('items')->withPath($meta->getData('list.id'))->links() }}
@endsection