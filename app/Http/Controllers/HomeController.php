<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Sport;
use App\Competition;
use App\Event;
use App\Index;

class HomeController extends Controller
{
    public function index()
    {
        $homeSportsId = explode(',', Config::get('sport.home_sports_id'));
        $homeCompetitionsId = explode(',', Config::get('sport.home_competitions_id'));
        $homeOnlyLive = Config::get('sport.home_only_live');

        // Basketball Competitions Filter
        $basketballSportId = Config::get('sport.basketball_sport_id');
        $basketballCompetitionsId = Config::get('sport.basketball_competitions_id') ? explode(',', Config::get('sport.basketball_competitions_id')) : null;

        $data = [];
        $locale = empty(App::getLocale()) ? Config::get('app.default_locale') : App::getLocale();
        $data['locales'] = Config::get('app.locales');
        $data['locale'] = $locale;

        $competitions = Competition::whereIn('id', $homeCompetitionsId)->orderBy('sid')->get();
        $data['competitions'] = $competitions;
        $all = Competition::whereIn('sport_id', [1])
                ->whereNotNull('letter')
                ->orderBy('letter')
                ->get();
        $categories = [];
        foreach($all as $a) {
            $categories[$a->letter][] = [
                'id' => $a->id,
                'name' => $a->name
            ];
        }
        $data['categories'] = $categories;
        $sports = Sport::whereIn('id', $homeSportsId)
                    ->orderBy('id')
                    ->get();
        $data['sports'] = $sports;
        $indexes = Index::with('event', 'event.competition', 'event.homeTeam', 'event.awayTeam')
                    ->with(['event.channels' => function($query){
                        $query->orderByRaw(DB::raw("FIELD(`key`, 'stream', 'streamNa', 'streamAmAli')"));
                    }])
                    ->where(function($query) use ($basketballSportId, $basketballCompetitionsId){
                        if ($basketballCompetitionsId) {
                            $query->where('sport_id', $basketballSportId)->whereIn('competition_id', $basketballCompetitionsId)->orWhere('sport_id', '!=', $basketballSportId);
                        }
                    })
                    ->where(function($query){
                        $query->where('status', 'Playing')->orWhereNull('status');
                    })
                    ->where(function($query) use ($homeOnlyLive){
                        if ($homeOnlyLive) {
                           $query->where('has_live', $homeOnlyLive);
                        }
                    })
                    ->where('hide', false)
                    ->where('start_play', '>', Carbon::now()->subHours(2)->toDateTimeString())
                    ->orderByRaw(DB::raw("DATE_FORMAT(`start_play`, '%Y-%m-%d')"))
                    ->orderBy('important','desc')
                    ->orderBy('start_play','asc')
                    ->get();
        if ($indexes) {
            $events = [];
            foreach($indexes as $index) {
                $item = [];
                if ($index->event) {
                    $item = [
                        'sport' => $index->event->competition->sport_id,
                        'important' => $index->important ? true : false,
                        'start_play' => $index->event->start_play,
                        'competition_id' => $index->event->competition->id,
                        'competition_name' => $index->event->competition->name,
                        'id' => $index->event->id,
                        'status' => $index->event->status_string,
                        'status_text' => __('home.' . mb_strtolower($index->event->status_string)),
                        'home_team' => $index->event->homeTeam->name,
                        'home_team_logo' => $index->event->homeTeam->logo,
                        'away_team' => $index->event->awayTeam->name,
                        'away_team_logo' => $index->event->awayTeam->logo,
                        'home_score' => $index->event->home_score ?: 0,
                        'away_score' => $index->event->away_score ?: 0,
                        'minute' => $index->event->minute ?: 0,
                        'minute_extra' => $index->event->minute_extra ?: 0,
                        'period' => $index->event->period,
                    ];
                    $channels = [];
                    foreach ($index->event->channels as $i => $channel) {
                        $channels[] = [
                            'index' => $i,
                            'id' => $channel->id,
                            'name' => $channel->name,
                        ];
                    }
                    $item['channels'] = $channels;
                }
                $events[] = $item;
            }
        }
        $data['events'] = $events;
        return view('home', $data);
    }

    public function events(Request $request)
    {
        $ids = $request->get('ids');
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        $indexes = Index::with('event')
                    ->whereIn('id', $ids)
                    ->where('hide', false)
                    ->where('start_play', '>', Carbon::now()->subHours(2)->toDateTimeString())
                    ->orderBy('start_play','asc')
                    ->get();
        if ($indexes) {
            $events = [];
            foreach($indexes as $index) {
                $item = [];
                if ($index->event) {
                    $item = [
                        'id' => $index->event->id,
                        'status' => $index->event->status_string,
                        'status_text' => __('home.' . mb_strtolower($index->event->status_string)),
                        'home_score' => $index->event->home_score,
                        'away_score' => $index->event->away_score,
                        'minute' => $index->event->minute ?: 0,
                        'minute_extra' => $index->event->minute_extra ?: 0,
                        'period' => $index->event->period,
                    ];
                    $channels = [];
                    foreach ($index->event->channels as $i => $channel) {
                        $channels[] = [
                            'index' => $i,
                            'id' => $channel->id,
                            'name' => $channel->name,
                        ];
                    }
                    $item['channels'] = $channels;
                }
                $events[] = $item;
            }
        }
        $data['events'] = $events;
        return $data;
    }

    public function event(Request $request, $id)
    {
        $c = $request->get('c');
        $data = ['c' => $c];
        $index = Index::with('event')
            ->with(['event.channels' => function($query){
                $query->orderByRaw(DB::raw("FIELD(`key`, 'stream', 'streamNa', 'streamAmAli')"));
            }])->where('id', $id)->first();
        $data['index'] = $index;
        return view('event', $data);
    }
}
