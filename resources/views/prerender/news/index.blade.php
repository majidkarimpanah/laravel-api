@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    @include('prerender.menu')

    <h1>{{__('Latest News')}}</h1>

    <ul>
        @foreach($meta->getData('pagination') as $newsArticle)
            @if(isset($newsArticle['meta']['source']) && $newsArticle['meta']['source'] === 'local')
                <li>
                    <h3> <a href="{{$meta->urls->article($newsArticle)}}">{{$newsArticle['title']}}</a></h3>

                    @if(isset($newsArticle['meta']['image']))
                        <img src="{{$newsArticle['meta']['image']}}" alt="Article image">
                    @endif

                    <div>{!!$newsArticle['body']!!}</div>
                </li>
            @endif
        @endforeach
    </ul>

    {{ $meta->getData('pagination')->withPath('news')->links() }}
@endsection