@if($menu = $meta->getMenu('primary'))
    <ul class="menu">
        @foreach($menu['items'] as $menuItem)
            <li><a href="{{url($menuItem['action'])}}">{{$menuItem['label']}}</a></li>
        @endforeach
    </ul>
@endif