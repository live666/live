@extends('layouts.base')

@section('main')
    <div class="container">
        <div class="row" style="display:none;" id="cover">
            <div class="text-center bg-dark text-white w-100">
                <div class="mx-auto py-5" id="countdown">&nbsp;</div>
            </div>
        </div>
		<div class="row">
			<div class="col-12 m-auto px-0">
				<video id="v-player" class="video-js vjs-fluid vjs-big-play-centered" playsinline  webkit-playsinline controls>
					<p class="vjs-no-js">
						To view this video please enable JavaScript, and consider upgrading to a
						web browser that
						<a href="https://videojs.com/html5-video-support/" target="_blank">
							supports HTML5 video
						</a>
					</p>
				</video>
			</div>
        </div>
        <div class="row pt-2">
            <div class="col-4">
                <span class="font-weight-bold">{{ $index->event->competition->name }}</span>
            </div>
            <div class="col-4 text-center">
                <span class="text-danger" id="e-status" @if ($index->event->status_string == 'Playing') style="display:none" @endif>{{ __('home.' . mb_strtolower($index->event->status_string)) }}</span>
                <span class="text-success" id="e-minute" @if ($index->event->status_string != 'Playing') style="display:none" @endif>
                    <span id="e-minute-time">@if($index->event->minute) {{ $index->event->minute }} @else {{ $index->event->period}} @endif</span><label class="m-0 e-glint" @if(!$index->event->minute) style="display:none;" @endif>'</label>
                </span>
            </div>
            <div class="col-4 text-right text-muted" id="e-time"></div>
        </div>
        <div class="row pt-2">
            <div class="col-2 text-center"><img src="{{ $index->event->homeTeam->logo }}" style="width:30px;height:30px;" /></div>
            <div class="col-3 m-auto font-weight-bold text-left p-0">{{ $index->event->homeTeam->name }}</div>
            <div class="col-2 m-auto h4 text-center p-0">
                <span id="e-home-score">{{ ($index->event->home_score) }}</span> - <span id="e-away-score">{{ ($index->event->away_score) }}</span>
            </div>
            <div class="col-3 m-auto font-weight-bold text-right p-0">{{ $index->event->awayTeam->name}}</div>
            <div class="col-2 text-center"><img src="{{ $index->event->awayTeam->logo }}" style="width:30px;height:30px;" /></div>
        </div>
        <div class="row">
            <div class="col-6 p-0 pr-1"><hr style="border-top: 2px solid #e94a50;" /></div>
            <div class="col-6 p-0 pl-1"><hr  style="border-top: 2px solid #3683cb;"/></div>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                @foreach ($index->event->channels as $channel)
                    <a href="{{ route('event', $index->id) }}?c={{ $channel->id }}" class="btn @if ($channel->id == $c) {{ $index->event->status_string == 'Playing' ? 'btn-success' : 'btn-secondary' }} @else {{ $index->event->status_string == 'Playing' ? 'btn-outline-success' : 'btn-outline-secondary' }} @endif btn-sm d-none d-md-inline-block play-live"  @if ($channel->id == $c) data-src="{{ $channel->url }}" @endif><span class="oi oi-play-circle" title="play-circle" aria-hidden="true"></span>&nbsp;&nbsp;{{ $channel->name }}</a>
                    <a href="{{ route('event', $index->id) }}?c={{ $channel->id }}" class="btn @if ($channel->id == $c) {{ $index->event->status_string == 'Playing' ? 'btn-success' : 'btn-secondary' }} @else {{ $index->event->status_string == 'Playing' ? 'btn-outline-success' : 'btn-outline-secondary' }} @endif btn-sm d-md-none play-live"  @if ($channel->id == $c) data-src="{{ $channel->url }}" @endif><span class="oi oi-play-circle" title="play-circle" aria-hidden="true"></span>&nbsp;&nbsp;{{ $channel->name }}</a>
                @endforeach
            </div>
        </div>
	</div>
@endsection

@section('footer')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/6.7.3/video-js.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.5.4/video.min.js"></script>
    <style>
        .video-js .vjs-big-play-button {
            border: none;
            -webkit-border-radius: .1em;
            -moz-border-radius: .1em;
            border-radius: .1em;
        }
    </style>
    <script>
        var id = {{ $index->event->id }};
        var status = '{{ $index->event->status_string }}';
        var status_text = '{{ __('home.' . mb_strtolower($index->event->status_string)) }}';
        var minute = '{{ $index->event->minute }}';
        var period = '{{ $index->event->period }}';
        var d = new Date('{{ $index->event->start_play->toJSON() }}');
        var homeScore = {{ ($index->event->home_score) ?: 0 }};
        var awayScore = {{ ($index->event->away_score) ?: 0 }};
        $('#e-time').html(("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2));

        var watchTimeout, glintInterval;

        var watch = function() {
            if (status == 'Playing')  {
                $.ajax({
                    method: "POST",
                    url: "/events",
                    data: { "_token": "{{ csrf_token() }}", ids: id }
                })
                .done(function(data) {
                    if (data.events.length > 0) {
                        el = data.events[0];
                        if (el.home_score && el.home_score != homeScore) {
                            homeScore = el.home_score;
                            $("#e-home-score").html(homeScore);
                            if (parseInt(homeScore) > 0 ) {
                                $("#e-home-score").css('color', 'red');
                            }
                        }
                        if (el.away_score && el.away_score != awayScore) {
                            awayScore = el.away_score;
                            $("#e-away-score").html(awayScore);
                            if (parseInt(awayScore) > 0 ) {
                                $("#e-away-score").css('color', 'red');
                            }
                        }
                        if (el.minute != minute) {
                            minute = el.minute;   
                        }
                        if (el.period != period) {
                            period = el.period;   
                        }
                        if (el.minute) {
                            $("#e-minute-time").html(minute);
                            $('.e-glint').show();
                        }  else {
                            $("#e-minute-time").html(period);
                            $('.e-glint').hide();
                        }
                        if (el.status != status) {
                            status = el.status;
                            $("#e-status").html(status_text);
                            if (status != 'Playing') {
                                $("#e-minute").hide();
                                $("#e-status").show();
                            }
                        }
                    }
                });
                watchTimeout = setTimeout(function(){ watch(); }, 20000);
            }
        }

        function countDown(until){
            var date = new Date();
            if (parseInt(until - date)<=0) {
                return null;
            }
            var days = (until - date)/1000/3600/24;
            var day = Math.floor(days);
            var hours = (days - day) * 24;
            var hour = Math.floor(hours);
            var minutes = (hours - hour) * 60;
            var minute = Math.floor(minutes);
            var seconds = (minutes - minute) * 60;
            var second = Math.floor(seconds);
            return  day + ' {{ __("home.days") }} ' + prefixInteger(hour,2) + ':' + prefixInteger(minute,2) + ':' + prefixInteger(second,2);
        }

        function prefixInteger(num, length) {
            return (Array(length).join('0') + num).slice(-length);
        }
        
        function start() {
            var player = videojs('v-player', {
                    responsive: true,
                    autoplay: true,
                    textTrackSettings: false,
                    liveui: false,
                    liveTracker: false,
                    html5: {
                        nativeTextTracks: false
                    },
                    controlBar: {
                        liveDisplay: false,
                        volumeMenuButton: true,
                    }
                });
                watch();
                glintInterval = setInterval(function(){$('.e-glint').toggleClass('text-white')}, 1000);
                $('.play-live').on('click', function(){
                    player.src($(this).attr('data-src'));
                    player.play();
                });
                if (status == 'Playing') {
                    player.src($('.play-live[data-src]').attr('data-src'));
                    player.play();
                }
        }
        $(function(){
            $('#nav-main').removeClass('d-none');
            if (status == 'Played')  {
                $('#e-status').removeClass('text-danger');
                $('#e-status').addClass('text-primary');
            } else if (status == 'Playing') {
                start();
            } else {
                var counted = false;
                var timeInterval = setInterval(function(){
                    text = countDown(d);
                    if (text) {
                        counted = true;
                        $('#cover').show();
                        $('#countdown').html(' @lang("home.countdown_text", ["countdown" => "' + text + '"]) ').show();
                    } else {
                        clearInterval(timeInterval);
                        if (counted) {
                            $('#cover').hide();
                            setTimeout(function(){ window.location.reload(); }, 3000);
                        }
                    }
                }, 1000);
            }
            document.addEventListener('visibilitychange', function(){  
                if( document.visibilityState == 'hidden' || document.visibilityState == 'webkitHidden' || document.visibilityState == 'mozHidden') {  
                    clearTimeout(watchTimeout);
                    clearInterval(glintInterval);
                } else {  
                    if (status == 'Playing') {
                        clearTimeout(watchTimeout);
                        clearInterval(glintInterval);
                        watch();
                        glintInterval = setInterval(function(){$('.e-glint').toggleClass('text-white')}, 1000);
                    }
                }
            });
        });
    </script>
@endsection