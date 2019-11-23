<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Services\SportDataService;
use Carbon\Carbon;
use App\Event;

class SportSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sport {days?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync sport data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $service = new SportDataService();
        $token = Cache::get('api_token', function () use ($service) {
            $data = $service->requestToken();
            $expired = new Carbon($data->tokenExpired);
            Cache::put('api_token', $data->token, ($expired->timestamp - Carbon::now()->timestamp));
            return $data->token;
        });
        $days = $this->argument('days') ? :1;
        try {
            Log::info('Sync data: '.$days.' Days');
            $data = $service->requestEpgs($token, null, Carbon::now()->addDays(intval($days))->toDateString());
            if (!$data) {
                return;
            }
            foreach($data as $item) {
                if (is_null($item->mid)) {
                    $this->error('mid is empty');
                    Log::error('mid is empty', (array) $item);
                    continue;
                }
                $startPlay = (new Carbon($item->gameTime))->setTimezone(Config::get('app.timezone'));
                if ((time() - $startPlay->timestamp) > (60*60*4) ) {
                    continue;
                }
                $lastUpdate = (new Carbon($item->updated_at))->setTimezone(Config::get('app.timezone'));
                $event = Event::where('sid', $item->mid)->first();
                if ($event && $event->last_update && $event->last_update->timestamp == $lastUpdate->timestamp) {
                    continue;
                }
                $sport = $service->syncSportModel([
                    'sid' => $item->sportId,
                    'name' => $item->sportName,
                ]);
                $competition = $service->syncCompetitionModel($sport, [
                    'sid' => $item->lid,
                    'name' => $item->lname,
                    'name_en' => $item->lnameEN,
                    'logo' => $item->licon,
                ]);
                if (!$competition) {
                    $this->error('competition is null ' . $item->mid);
                    Log::error('competition is null', (array) $item);
                    continue;
                }
                $homeTeam = $service->syncTeamModel($sport, [
                    'name' => $item->hname,
                    'name_en' => $item->hnameEN,
                    'logo' => $item->hicon,
                ]);
                $awayTeam = $service->syncTeamModel($sport, [
                    'name' => $item->aname,
                    'name_en' => $item->anameEN,
                    'logo' => $item->aicon,
                ]);
                $status = null;
                if ($item->gameStage) {
                    if ($item->gameStage == '完场') {
                       $status = 'Played';
                    } else if ($item->gameStage == '推迟') {
                        $status = 'Postponed';
                    } else if (in_array($item->gameStage, ['上半场', '下半场']) || preg_match('/第.*节/i', $item->gameStage)) {
                        $status = 'Playing';
                    }
                }
                if (!$event) {
                    $event = new Event();
                    $event->sid = $item->mid;
                    $event->competition_id = $competition->id;
                    $event->start_play = $startPlay;
                    $event->home_team_id = $homeTeam->id;
                    $event->home_score = $item->hTotalScore;
                    $event->away_team_id = $awayTeam->id;
                    $event->away_score = $item->aTotalScore;
                    $event->status = $status;
                    if ($item->gameProgress) {
                        $event->minute = explode(":", $item->gameProgress)[0];
                    }
                    if ($item->gameStage) {
                        $event->period = $item->gameStage;
                    }
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
                    if ($event->status != $status) {
                        $event->status = $status;
                    }
                    if ($item->gameProgress) {
                        $minute = intval(explode(":", $item->gameProgress)[0]);
                        if ($event->minute != $minute) {
                            $event->minute = $minute;
                        }
                    }
                    if ($event->period != $item->gameStage) {
                        $event->period = $item->gameStage;
                    }
                }
                $event->last_update = $lastUpdate;
                $event->save();

                $hasLive = false;
                // Live 1
                if (isset($item->stream) && count(array_keys((array)$item->stream))) {
                    $hasLive = true;
                    $service->syncChannelModel($event, [
                        'key' => 'stream',
                        'live' => (array) $item->stream,
                        'status' => $item->stream->status ?: false
                    ]);
                }
                // Live 2
                if (isset($item->streamNa) && isset($item->streamNa->live) && count(array_keys((array)$item->streamNa->live))) {
                    $hasLive = true;
                    $service->syncChannelModel($event, [
                        'key' => 'streamNa',
                        'live' => (array) $item->streamNa->live,
                        'status' => $item->streamNa->live->status ?: false
                    ]);
                }
                // Live 3
                if (isset($item->streamAmAli) && count(array_keys((array)$item->streamAmAli))) {
                    $hasLive = true;
                    $service->syncChannelModel($event, [
                        'key' => 'streamAmAli',
                        'live' => (array) $item->streamAmAli ,
                        'status' => $item->streamAmAli->status ?: false
                    ]);
                }

                $important = false;
                if ($item->hot) {
                    $important = true;
                }
                $service->syncIndexModel($event, $sport, $important, $hasLive);
                
                $info = sprintf('Sync Event: %d %s %s %s %s (%d) VS %s (%d) | %d | %s %s(%s) %d | %s | %s',
                            $item->mid,
                            $item->gameTime,
                            isset($item->sportName) ? $item->sportName : '*',
                            isset($item->lname) ? $item->lname : '*',
                            isset($item->hname) ? $item->hname : '*',
                            $item->hTotalScore,
                            isset($item->aname) ? $item->aname : '*',
                            $item->aTotalScore,
                            $hasLive,
                            $item->gameProgress ?: '-', 
                            $item->gameStage ?: '-',
                            $status ?: '-',
                            $item->hot,
                            $lastUpdate,
                            time() - $startPlay->timestamp
                        );
                $this->info($info);
            }
        } catch (\Exception $e) {
            Log::error('Caught exception: ' . $e->getMessage());
            Log::error($e);
            $this->error($e->getMessage());
            
        }
    }
}
