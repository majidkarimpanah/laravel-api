@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    <h1>{{ $meta->getTitle() }}</h1>

    <p>{{ $meta->getDescription() }}</p>

    @include('prerender.menu')

    <ul>
        @foreach($meta->getData('lists') as $list)
            <li>
                <h2>{{$list['name']}}</h2>
                <p>{{$list['description']}}</p>
                <ul style="display: flex; flex-wrap: wrap">
                    @foreach($list['items'] as $listItem)
                        <figure>
                            <img src="{{$listItem->poster}}" alt="List item poster" width="270px">
                            <figcaption>
                                <a href="{{$meta->urls->title($listItem)}}">{{$listItem['name']}}</a>
                            </figcaption>
                        </figure>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
@endsection