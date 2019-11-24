@extends('layouts.base')

@section('main')
    <nav id="nav-sub">
        <div class="container">
            <div class="row">
                <div class="w-100 d-flex flex-row-reverse flex-md-row">
                    <div class="">
                        <ul class="nav nav-sport justify-content-end justify-content-md-start" id="pills-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#pills-home"  id="pills-home-tab" data-toggle="pill" role="tab" aria-controls="pills-home" aria-selected="true">{{ __('home.home') }}</a>
                            </li>
                            @foreach ($sports as $s)
                                <li class="nav-item">
                                    <a class="nav-link" href="#pills-sport-{{$s->id}}"  id="pills-sport-{{$s->id}}-tab" data-toggle="pill" role="tab" aria-controls="pills-sport-{{$s->id}}" aria-selected="false">{{ $s->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="tools pt-md-2">
                        <a class="navbar-brand d-md-none text-dark ml-1 mr-1" href="{{ route('home', [], false) }}"><img src="{{ config('app.logo_invert') }}"  style="height:30px;"/></a>
                        <div class="btn-group">
                            <div class="dropdown ">
                                <button class="btn btn-light btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ $locales[$locale]}}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                @foreach ($locales as $k => $v)
                                    @if( $locale != $k )
                                        <a class="dropdown-item" href="{{ 'zh-cn' != $k ? '/'.$k : '/' }}">{{ $v }}</a>    
                                    @endif
                                @endforeach
                                </div>
                            </div>
                            &nbsp;
                            <a href="#" class="btn btn-light btn-sm d-none d-md-inline-block" data-toggle="modal" data-target=".event-filter">{{ __('home.event_filter') }}</a>
                            <a href="#" class="btn btn-light btn-sm d-md-none"  data-toggle="modal" data-target=".event-filter">{{ __('home.filter') }}</a>
                        </div>
                    </div>
                </div>
                <div class="tab-content w-100" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                        <ul class="nav nav-competition justify-content-center justify-content-md-start">
                            <li class="nav-item">
                                <a class="nav-link active" data-competition-id="all" href="#">{{ __('home.all') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-competition-id="hot" href="#">{{ __('home.hot') }}</a>
                            </li>
                            @foreach ($competitions as $c)
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-competition-id="{{ $c->id }}">{{ $c->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @foreach ($sports as $s)
                        <div class="tab-pane fade" id="pills-sport-{{$s->id}}" role="tabpanel" aria-labelledby="pills-sport-{{$s->id}}-tab">
                            <ul class="nav nav-competition justify-content-center justify-content-md-start">
                                <li class="nav-item">
                                    <a class="nav-link active" data-competition-id="all" data-sport-id="{{$s->id}}" href="#">{{ __('home.all') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-competition-id="hot" data-sport-id="{{$s->id}}" href="#">{{ __('home.hot') }}</a>
                                </li>
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="row py-2 text-center d-none d-md-flex event-header">
            <div class="col-2 text-left">{{ __('home.events') }}</div>
            <div class="col-1">{{ __('home.time') }}</div>
            <div class="col-1">{{ __('home.status') }}</div>
            <div class="col-2">{{ __('home.home_team') }}</div>
            <div class="col-1">{{ __('home.score') }}</div>
            <div class="col-2">{{ __('home.away_team') }}</div>
            <div class="col-2 text-left">{{ __('home.channel') }}</div>
        </div>
        <div id='events'></div>
    </div>
    <div class="refresh-bg">
        <div class="refrsh-icon"><span class="oi oi-reload"></span></div>
    </div>
    <div class="modal fade event-filter" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="myExtraLargeModalLabel">{{ __('home.event_filter') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        @foreach ($categories as $letter => $list)
                            <div class="row">
                                <div class="col-2 col-md-1 bg-light pt-3 font-weight-bold  justify-content-center text-center">{{ $letter }}</div>
                                <div class="col-10 col-md-11 d-flex flex-wrap border-bottom py-2">
                                    @foreach ($list as $c)
                                    <div class="form-check py-1" style=" width:170px;">
                                        <input class="form-check-input" data-filter-competition-id="{{ $c['id'] }}" type="checkbox" value="$c['id']" id="c-{{ $c['id'] }}">
                                        <label class="form-check-label" for="c-{{ $c['id'] }}">
                                            {{ $c['name']  }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script src="https://cdn.staticfile.org/jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
    <script src="https://cdn.staticfile.org/jsviews/1.0.5/jsviews.min.js"></script>
    <style>
        @media (max-width: 576px) {
            body {
                padding-top: 4.5rem;
            }
        }
    </style>
    <script id="eventTemplate" type="text/x-jsrender">
        [^[for e]]
            [[if start_play]]
                <div class="row bg-white border-bottom text-center py-2 [[if important]]hot[[/if]]" data-link="visible[:!isHide]" id='e-[[:id]]'>
                    <div class="col-12 d-md-none p-0">
                        [[:time]]
                    </div>
                    <div class="d-md-none col-11 text-left text-muted p-0" style="position:relative; left:6px;top:-1.4rem; ">[[:competition_name]]</div>
                    <div class="col-2 m-auto font-weight-bold text-left d-none d-md-flex">
                        [[:competition_name]]
                    </div>
                    <div class="col-1 m-auto d-none d-md-flex justify-content-center">[[:time]]</div>
                    <div class="col-1 m-auto d-none d-md-flex justify-content-center">
                        [^[if status == 'Playing']]
                            <label class="m-0 text-success e-minute">
                                [^[if minute]]
                                    [^[:minute]]<label class="m-0 e-glint">'</label>
                                [[else]]
                                    [^[:period]]
                                [[/if]]
                            </label>
                        [[else]]
                            [^[if status == 'Played']]
                                <label class="m-0 text-primary e-status">[^[:status_text]]</label>
                            [[else]]
                                <label class="m-0 text-danger e-status ">[^[:status_text]]</label>
                            [[/if]]
                        [[/if]]
                    </div>
                    <div class="col-4 col-md-2 m-auto p-0">
                        <div><img class="lazyload" src="img/football_holder.png" data-original='[[:home_team_logo]]' style="width:30px;height:30px;"/></div>
                        <div class="font-weight-bold py-1">[[:home_team]]</div>
                    </div>
                    <div class="col-4 col-md-1 m-auto p-0">
                        <div class="d-md-none pb-1">
                        [^[if status == 'Playing']]
                            <label class="m-0 text-success e-minute">
                                [^[if minute]]
                                    [^[:minute]]<label class="m-0 e-glint">'</label>
                                [[else]]
                                    [^[:period]]
                                [[/if]]
                                
                            </label>
                        [[else]]
                            [^[if status == 'Played']]
                                <label class="m-0 text-primary e-status">[^[:status_text]]</label>
                            [[else]]
                                <label class="m-0 text-danger e-status ">[^[:status_text]]</label>
                            [[/if]]
                        [[/if]]
                        </div>
                        <div class="h4">
                            <span class="e-home-score">[^[if status != 'Fixture']][^[:home_score]][[/if]]</span> - <span class="e-away-score">[^[if status != 'Fixture']][^[:away_score]][[/if]]</span>
                        </div>
                    </div>
                    <div class="col-4 col-md-2 m-auto p-0">
                        <div><img class="lazyload" src="img/football_holder.png" data-original='[[:away_team_logo]]' style="width:30px;height:30px;"/></div>
                        <div class="font-weight-bolder py-1">[[:away_team]]</div>
                    </div>
                    <div class="col-md-3 col-12 m-auto text-md-left">
                        [^[for channels]]
                            <a href="{{ route('event', '', false) }}/[[:#parent.parent.data.id]]?c=[[:id]]" target="_blank" class="btn [[if #parent.parent.data.status == 'Playing']] btn-outline-success [[else]] btn-outline-secondary [[/if]] btn-sm d-none d-md-inline-block"><span class="oi oi-play-circle" title="play-circle" aria-hidden="true"></span>&nbsp;&nbsp;[[:name]]</a>
                            <a href="{{ route('event', '', false) }}/[[:#parent.parent.data.id]]?c=[[:id]]" target="_blank" class="btn [[if #parent.parent.data.status == 'Playing']] btn-success [[else]] btn-secondary [[/if]] btn-sm d-md-none"><span class="oi oi-play-circle" title="play-circle" aria-hidden="true"></span>&nbsp;&nbsp;[[:name]]</a>
                        [[/for]]
                    </div>
                </div>
            [[else]]
                <div class="row py-1 py-md-2 pl-2 pl-md-3" data-link="visible[:!isHide]">
                    <div class="event-day">[[:day]]</div>
                </div>
            [[/if]]
        [[/for]]
        <div class="row py-4 justify-content-center text-center" data-link="visible[:!t]">
            <div class="py-4" style="font-size: 1rem;" >{{ __('home.no_events') }}</div>
        </div>
    </script>
    
    <script type="application/javascript">
        $.views.settings.delimiters("[[", "]]");
        function utcLocal(value) {
            d = new Date(value);
            return ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
        }
        $.views.tags("utcLocal", utcLocal);

        var eTemplate = $.templates("#eventTemplate");
        var data = {!! json_encode($events) !!};
        var events = [];
        var eIndex = [];
        var days = [];
        var dayCount = [];
        var totalEvents = data.length;
        for (var i = 0; i < data.length; i++){
            d = new Date(data[i]['start_play']);
            data[i].time = ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
            data[i].day =  d.getFullYear() + "-" + ("0" + (d.getMonth()+1)).slice(-2)  + '-' + ("0" + d.getDate()).slice(-2);
            if (dayCount[data[i].day]) {
                dayCount[data[i].day] =  dayCount[data[i].day] + 1;
            } else {
                dayCount[data[i].day] =  + 1;
            }
            if ($.inArray(data[i].day, days) < 0) {
                days[days.length] = data[i].day;
                events[events.length] = {'day': data[i].day,'isHide': false}
            }
            eIndex[data[i].id] = events.length;
            events[events.length] = data[i];
            
        }
        var app = {
                e: events,
                t: totalEvents
            };
        eTemplate.link("#events", app);
        $("img.lazyload").lazyload();
        var watchTimeout, glintInterval;

        var refresh = function (competitions, sport) {
            if (!$.isArray(competitions) && $.inArray(competitions, ["all", "hot"]) < 0) {
                competitions = competitions.split(",");
            }
            dayCount = [];
            totalEvents = 0;
            for (var i = 0; i < events.length; i++){
                var event = events[i];
                if (!event['id']) continue;
                var is_hide = true;
                switch(competitions) {
                    case 'all':
                        if (!sport || sport == event['sport'].toString()) {
                            is_hide = false;
                        }
                        break;
                    case 'hot':
                        if (!sport || sport == event['sport'].toString()) {
                            if (event['important']) {
                                is_hide = false;
                            } 
                        }
                        break;
                    default:
                        if ($.inArray(event['competition_id'].toString(), competitions) >= 0) {
                            is_hide = false;
                        }
                }
                $.observable(event).setProperty("isHide", is_hide);
                if (!is_hide) {
                    totalEvents = totalEvents + 1;
                    if (dayCount[event.day]) {
                        dayCount[event.day] =  dayCount[event.day] + 1;
                    } else {
                        dayCount[event.day] =  + 1;
                    }
                }
                $.observable(app).setProperty('t',totalEvents);
            }
            for (var i = 0; i < events.length; i++){
                var event = events[i];
                if (!event['sport']) {
                    if (dayCount[event['day']] > 0) {
                        $.observable(event).setProperty("isHide", false);
                    } else {
                        $.observable(event).setProperty("isHide", is_hide);
                    }
                }
            }
        }

        var watch = function() {
            ids = [];
            for (var i = 0; i < events.length; i++){
                if (events[i].id) {
                    d = new Date(events[i].start_play);
                    now = new Date();
                    if (d.getTime()-now.getTime() <= 0) {
                        ids[ids.length] = events[i].id;
                    }
                }
            }
            if (ids.length > 0)  {
                $.ajax({
                    method: "POST",
                    url: "/events",
                    data: { "_token": "{{ csrf_token() }}", ids: ids }
                })
                .done(function(data) {
                    for (var i = 0; i < data.events.length; i++){
                        j = eIndex[data.events[i].id];
                        if (data.events[i].home_score && (events[j].home_score != data.events[i].home_score)) {
                            $.observable(events[j]).setProperty("home_score", data.events[i].home_score);
                            if (parseInt(data.events[i].home_score) > 0) {
                                $('#e-' + data.events[i].id + ' .e-home-score').css('color', 'red');
                            }
                        }
                        if (data.events[i].away_score && (events[j].away_score != data.events[i].away_score)) {
                            $.observable(events[j]).setProperty("away_score", data.events[i].away_score);
                            if (parseInt(data.events[i].away_score) > 0) {
                                $('#e-' + data.events[i].id + ' .e-away-score').css('color', 'red');
                            }
                        }
                        
                        if (events[j].minute != data.events[i].minute) {
                            $.observable(events[j]).setProperty("minute", data.events[i].minute);
                        }
                        if (events[j].status != data.events[i].status) {
                            $.observable(events[j]).setProperty("status", data.events[i].status);
                            $.observable(events[j]).setProperty("status_text", data.events[i].status_text);
                        }
                    }
                });
            }
            watchTimeout = setTimeout(function(){ watch(); }, 30000);
        }

        $(function(){
            $( "a[data-competition-id]" ).on("click", function(e){  
                refresh($(this).attr('data-competition-id'), $(this).attr('data-sport-id'));
                $('.nav-competition a.active').toggleClass('active');
                $(this).toggleClass('active');
                $("html, body").scrollTop(0);
                e.preventDefault();
            });
            $( "input[data-filter-competition-id]" ).on("click", function(e){
                ids = new Array();
                $('.event-filter input[data-filter-competition-id]:checked').each(function() {
                    ids[ids.length] = $(this).attr("data-filter-competition-id");
                });;
                refresh(ids);
            });
            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                id = $(e.target).attr('aria-controls');
                el = $('#' + id).find('.nav-link:first');
                el.trigger('click');
                e.preventDefault();
            });
            $('.refrsh-icon').on('click', function(e){
                $(this).toggleClass('loading');
                window.location.reload();
            });
            glintInterval = setInterval(function(){$('.e-glint').toggleClass('text-white')}, 1000);
            setTimeout(function(){ watch(); }, 30000);
            document.addEventListener('visibilitychange', function(){  
                if( document.visibilityState == 'hidden' || document.visibilityState == 'webkitHidden' || document.visibilityState == 'mozHidden') {  
                    clearTimeout(watchTimeout);
                    clearInterval(glintInterval);
                } else {  
                    clearTimeout(watchTimeout);
                    clearInterval(glintInterval);
                    watch();
                    glintInterval = setInterval(function(){$('.e-glint').toggleClass('text-white')}, 1000);
                }
            });
        });
    </script>
@endsection