@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    @include('prerender.menu')

    <h1>{{ $meta->getData('person.name') }}</h1>

    <img src="{{$meta->urls->mediaImage($meta->getData('person'))}}" alt="Person poster" width="270px">

    <div>
        <h3>{{__('Biography')}}</h3>
        <p>{{$meta->getData('person.description')}}</p>
    </div>

    <div>
        <h3>{{__('Personal Facts')}}</h3>
        <dl>
            <dt>{{__('Known For')}}</dt>
            <dd>{{$meta->getData('person.known_for')}}</dd>

            <dt>{{__('Gender')}}</dt>
            <dd>{{$meta->getData('person.gender')}}</dd>

            <dt>{{__('Known Credits')}}</dt>
            <dd>{{count($meta->getData('person.credits'))}}</dd>

            @if($meta->getData('person.birth_date'))
                <dt>{{__('Birth Date')}}</dt>
                <dd>{{$meta->getData('person.birth_date')}}</dd>
            @endif

            @if($meta->getData('person.birth_place'))
                <dt>{{__('Birth Place')}}</dt>
                <dd>{{$meta->getData('person.birth_place')}}</dd>
            @endif
        </dl>
    </div>

    <div>
        <h3>{{__('Known For')}}</h3>
        <ul style="display: flex; flex-wrap: wrap;">
            @foreach($meta->getData('knownFor') as $credit)
                <li>
                    <figure>
                        <img src="{{$meta->urls->mediaImage($credit)}}" alt="Credit poster" width="270px">
                        <figcaption>
                            <a href="{{$meta->urls->title($credit)}}">{{$credit['name']}}</a>
                        </figcaption>
                    </figure>
                </li>
            @endforeach
        </ul>
    </div>

    <div>
        <h3>{{__('Credits')}}</h3>
        <ul>
            @foreach($meta->getData('credits') as $groupName => $creditGroup)
                <li style="margin-bottom: 15px;">
                    <h4 style="text-transform: capitalize">{{$groupName}} ({{count($creditGroup)}} credits)</h4>
                    <ul>
                        @foreach($creditGroup as $credit)
                            <li style="margin-bottom: 15px;">
                                <div class="meta">
                                    <a href="{{$meta->urls->title($credit)}}">{{$credit['name']}}</a>
                                    @if(isset($credit['pivot']))
                                        <div>{{$credit['pivot']['character']}}</div>
                                        <div>{{$credit['pivot']['job']}}</div>
                                        <div>{{$credit['pivot']['department']}}</div>
                                    @endif

                                    @if(isset($credit['episodes']))
                                        <div class="episode-list">
                                            @foreach($credit['episodes'] as $episodeCredit)
                                                <div class="episode-credit">
                                                    <div class="episode-name">
                                                        <span>- </span>
                                                        <a href="{{$meta->urls->episode($episodeCredit)}}">{{$episodeCredit['name']}}</a>
                                                        <span> ({{$episodeCredit['year']}})</span>
                                                        <span class="episode-separator"> ... </span>
                                                        <span>
                                                <span>{{$episodeCredit['pivot']['character']}}</span>
                                                <span>{{$episodeCredit['pivot']['job']}}</span>
                                                    <span>{{$episodeCredit['pivot']['department']}}</span>
                                            </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="year">{{$credit['year']}}</div>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </div>
@endsection