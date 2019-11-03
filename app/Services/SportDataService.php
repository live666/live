<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Requests;
use App\Sport;
use App\Competition;
use App\Team;
use App\Event;
use App\Index;
use App\Channel;

class SportDataService
{
    private $apiBaseUrl;

    private $apiProject;

    private $apiSecret;

    private $requestOptions;
    
    public function __construct()
    {
        $this->apiBaseUrl = Config('sport.api.base_url');
        $this->apiProject = Config('sport.api.project');
        $this->apiSecret = Config('sport.api.secret');
        $this->requestOptions = [
            'timeout' => Config('sport.api.timeout')
        ];
    }

    public function requestToken()
    {
        $url = $this->apiBaseUrl . '/api/v1/login';
        $data = [
            'project' => $this->apiProject,
            'secret' => $this->apiSecret
        ];
        $response = Requests::post($url, [], $data, $this->requestOptions);
        $json = json_decode($response->body);
        if ($json->code== 0) {
           return $json->data;
        }
        return null;
    }

    public function requestEpgs($token, $startDate = null, $endDate = null, $lid = null, $mid = null, $bet = false, $filter = null)
    {
        $url = $this->apiBaseUrl . '/api/v1/epgs';
        $data = [
            'token' => $token,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'lid' => $lid,
            'mid' => $mid,
            'bet' => $bet,
            'filter' => $filter
        ];
        $response = Requests::post($url, [], $data, $this->requestOptions);
        $json = json_decode($response->body);
        if ($json->code== 0) {
            return $json->data;
        }
        return null;
    }

    public function syncSportModel($item)
    {
        $sport = Sport::where('sid', $item['sid'])->first();
        if (!$sport) {
            $sport = new Sport();
            $sport->sid = $item['sid'];
            $sport->name = $item['name'];
            $sport->name_i18n = [
              'zh-cn' => $item['name']
            ];
        } else {
            if (($sport->name != $item['name'])){
                $sport->name = $item['name'];
            }
        }
        $sport->save();
        return $sport;
    }

    public function syncCompetitionModel($sport, $item)
    {
        if (is_null($item['name']) && is_null($item['name_en']) && is_null($item['logo'])) {
            return null;
        }
        $name = is_null($item['name_en']) ? $item['name'] : $item['name_en'];
        $competition = Competition::where(function($query) use ($item, $name){
            if ($item['sid']) {
                $query->where('sid', $item['sid']);
            } else if ($name) {
                $query->where('name', $name);
            } elseif ($item['logo']) {
                $query->where('logo', $item['logo']);
            }
        })->first();
        if (!$competition) {
            $competition = new Competition();
            $competition->sport_id = $sport->id;
            $competition->sid = $item['sid'];
            if ($name) {
                $competition->name = $name;
                $competition->letter = mb_strtoupper(mb_substr(trim($name), 0, 1));
            }
            $i18n = [];
            if ($item['name_en']) {
                $i18n['en'] = $item['name_en'];
            }
            if ($item['name']) {
                $i18n['zh-cn'] = $item['name'];
            }
            if (count($i18n)) {
                $competition->name_i18n = $i18n;
            }
            $competition->logo = $item['logo'];
        } else {
            if ($item['sid']) {
                if (($competition->name != $name)){
                    $competition->name = $name;
                    if ($name) {
                        $competition->letter = mb_strtoupper(mb_substr(trim($name), 0, 1));
                    }
                }
                if (($competition->logo != $item['logo'])){
                    $competition->logo = $item['logo'];
                }
            }
        } 
        $competition->save();
        return $competition;
    }

    public function syncTeamModel($sport, $item)
    {
        if (is_null($item['name']) && is_null($item['name_en']) && is_null($item['logo'])) {
            return null;
        }
        $name = is_null($item['name_en']) ? $item['name'] : $item['name_en'];
        $team = Team::where('sport_id', $sport->id)->where(function($query) use ($item, $name){
            if ($name) {
                $query->where('name', $name);
            } elseif ($item['logo']) {
                $query->where('logo', $item['logo']);
            }
        })->first();
        if (!$team) {
            $team = new Team();
            $team->sport_id = $sport->id;
            $team->name = $name;
            $team->logo = $item['logo'];
            $i18n = [];
            if ($item['name_en']) {
                $i18n['en'] = $item['name_en'];
            }
            if ($item['name']) {
                $i18n['zh-cn'] = $item['name'];
            }
            if (count($i18n)) {
                $team->name_i18n = $i18n;
            }
        }
        $team->save();
        return $team;
    }

    public function syncIndexModel(Event $event, $sport, $important = false, $hasLive = false)
    {
        if(!$event->index) {
            $index = new Index();
            $index->id = $event->id;
            $index->sport_id = $sport->id;
            $index->competition_id = $event->competition_id;
            $index->start_play = $event->start_play;
            $index->status = $event->status;
            $index->important = $important;
            $index->has_live = $hasLive;
            $index->save();
        } else {
            if ($event->index->status != $event->status) {
                $event->index->status = $event->status;
            }
            if ($event->index->start_play->timestamp != $event->start_play->timestamp) {
                $event->index->start_play = $event->start_play;
            }
            if ($event->index->important != $important) {
                $event->index->important = $important;
            }
            if ($hasLive && !$event->index->has_live) {
                $event->index->has_live = true;
            }
            $event->index->save();
        }
    }

    public function syncChannelModel($event, $item)
    {
        if (is_null($item['url'])) return null;
        $channel = Channel::where('event_id', $event->id)->where('url', $item['url'])->first();
        if (!$channel) {
            $channel = new Channel();
            $channel->event_id = $event->id;
            $channel->url = $item['url'];
        }
        $channel->save();
        return $channel;
    }

    public function sync()
    {
        $token = Cache::get('api_token', function () {
            $data = $this->requestToken();
            $expired = new Carbon($data->tokenExpired);
            Cache::put('api_token', $data->token, ($expired->timestamp - Carbon::now()->timestamp));
            return $data->token;
        });
        $data = $this->requestEpgs($token);
        if (!$data) {
            return;
        }
        foreach($data as $item) {
            if (is_null($item->mid)) {
                Log::error('mid is empty', (array) $item);
                continue;
            }
            $sport = $this->syncSportModel([
                'sid' => $item->sportId,
                'name' => $item->sportName,
            ]);
            $competition = $this->syncCompetitionModel($sport, [
                'sid' => $item->lid,
                'name' => $item->lname,
                'name_en' => $item->lnameEN,
                'logo' => $item->licon,
            ]);
            $homeTeam = $this->syncTeamModel($sport, [
                'name' => $item->hname,
                'name_en' => $item->hnameEN,
                'logo' => $item->hicon,
            ]);
            $awayTeam = $this->syncTeamModel($sport, [
                'name' => $item->aname,
                'name_en' => $item->anameEN,
                'logo' => $item->aicon,
            ]);
            $event = Event::where('sid', $item->mid)->first();
            $startPlay = (new Carbon($item->gameTime))->setTimezone(Config::get('app.timezone'));
            if (!$event) {
                $event = new Event();
                $event->sid = $item->mid;
                $event->competition_id = $competition->id;
                $event->start_play = $startPlay;
                $event->home_team_id = $homeTeam->id;
                $event->home_score = $item->hTotalScore;
                $event->away_team_id = $awayTeam->id;
                $event->away_score = $item->aTotalScore;
            } else {
                if ($event->start_play->timestamp != $startPlay->timestamp) {
                    $event->start_play = $startPlay;
                } 
                if ($event->home_score != $item->hTotalScore) {
                    $event->home_score = $item->hTotalScore;
                }
                if ($event->away_score != $item->aTotalScore) {
                    $event->away_score = $item->aTotalScore;
                }
            }
            $event->save();
            $this->syncIndexModel($event, $sport, false);

            if (isset($item->stream) && isset($item->stream->m3u8)) {
                $this->syncChannelModel($event, [
                    'url' => $item->stream->m3u8,
                ]);
            }
            
        }
    }
}