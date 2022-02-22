@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    @include('prerender.menu')

    <h1>{{$meta->getData('article.title')}}</h1>

    @if($image = $meta->getData('article.meta.image'))
        <img src="{{$image}}" alt="Article image">
    @endif

    <article>
        {!!$meta->getData('article.body')!!}
    </article>
@endsection