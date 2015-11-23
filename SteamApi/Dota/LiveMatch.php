<?php

namespace SteamApi\Dota;

use SteamApi\SteamApi;
use SteamApi\DotaApi;

class LiveMatch extends DotaApi {
    public $players = [
        // teams
        'radiant' => [],
        'dire' => [],
        // admins, commentators
        'lobby' => []
    ];
    // match info
    public $info = [];
    // pick and bans
    public $draft = [];

    function __construct($data = []) {
        // players list
        $this->players = $this->setPlayersArray($data['players']);
        unset($data['players']);
        
        // players statistics
        if (isset($data['scoreboard'])) {
            $this->setScoreboardArray($data['scoreboard']);
            unset($data['scoreboard']);
        }
        
        // team name filter
        if (isset($data['radiant_team']['team_name']) && SteamApi::FILTER) {
            SteamApi::filter($data['radiant_team']['team_name']);
        }
        if (isset($data['dire_team']['team_name']) && SteamApi::FILTER) {
            SteamApi::filter($data['dire_team']['team_name']);
        }
        
        // other match info
        $this->info += $data;
    }

    public function setPlayersArray(array $data = []) {
        $players = [];
        foreach ($data as $player) {
            $player['name'] = (SteamApi::FILTER) ? SteamApi::filter($player['name']) : $player['name']; // filter
            $player['steam_id'] = SteamApi::convertUserId($player['account_id']);
            $players[$this->getTeam((int) $player['team'])][$player['account_id']] = $player;
        }
        return $players;
    }

    /**
     *  snapshot match
     * @param array $data
     * @return array
     */
    public function setScoreboardArray(array $data = []) {
        $this->info['duration'] = $data['duration'];
        unset($data['duration']);
        $this->info['roshan_respawn_timer'] = $data['roshan_respawn_timer'];
        unset($data['roshan_respawn_timer']);

        $board = [];
        foreach ($data as $key => $value) {
            unset($value['abilities']); // usless array

            $this->info[$key . '_score'] = $value['score'];
            unset($value['score']);
            $this->info[$key . '_tower_state'] = $value['tower_state'];
            unset($value['tower_state']);
            $this->info[$key . '_barracks_state'] = $value['barracks_state'];
            unset($value['barracks_state']);

            $this->setScorePLayersArray($key, $value['players']);
            unset($value['players']);

            // if mode: cm, rd
            if (isset($value['picks']) && isset($value['bans'])) {
                $value['picks'] = $this->setScoreDraft($value['picks']);
                $value['bans'] = $this->setScoreDraft($value['bans']);
            }

            $board[$key] = $value;
        }
        $this->draft = $board;
    }

    public function setScorePLayersArray($team, array $data = []) {

        foreach ($data as $player) {
            $this->players[$team][$player['account_id']]['stat'] = $player;
        }
    }

    public function setScoreDraft(array $data = []) {
        $array = [];
        foreach ($data as $pick) {
            $array[] = $pick['hero_id'];
        }
        return $array;
    }

    public function getTeam($id) {
        switch ($id) {
            case 0:
                return 'radiant';
                break;
            case 1:
                return 'dire';
                break;
            default:
                return 'lobby';
                break;
        }
    }

}
