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
    protected $signature = 'sport';

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
        try {
            $data = $service->requestEpgs($token);
            if (!$data) {
                return;
            }
            foreach($data as $item) {
                if (is_null($item->mid)) {
                    $this->error('mid is empty');
                    Log::error('mid is empty', (array) $item);
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
                $service->syncIndexModel($event, $sport, false);

                if (isset($item->stream) && isset($item->stream->m3u8)) {
                    $service->syncChannelModel($event, [
                        'url' => $item->stream->m3u8,
                    ]);
                }
                $info = sprintf('Sync Event: %d %s %s %s (%d) VS %s (%d)',
                            $item->mid,
                            isset($item->sportName) ? $item->sportName : '*',
                            isset($item->lname) ? $item->lname : '*',
                            $item->gameTime,
                            isset($item->hname) ? $item->hname : '*',
                            isset($item->aname) ? $item->aname : '*',
                            $item->aTotalScore
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
