<?php

namespace SteamApi\Dota;

use SteamApi\DotaApi;

class TeamInfo extends DotaApi {

    public $teams = [];

    function __construct($array = []) {
        $this->teams = $this->setTeams($array);
    }

    /**
     * 
     * @param array $data
     * @return array
     */
    public function setTeams(array $data) {
        $data['name'] = (SteamApi::FILTER) ? SteamApi::filter($data['name']) : $data['name'];

        $teams = [];
        foreach ($data as $key => $team) {
            $keys = array_keys($team);
            foreach ($keys as $akey) {
                if (preg_match('/^player_\d+_account_id$/', $akey)) {
                    $team['players'][] = $team[$akey];
                    unset($team[$akey]);
                } elseif (preg_match('/^league_id_\d+$/', $akey)) {
                    $team['leagues'][] = $team[$akey];
                    unset($team[$akey]);
                }
            }
            $teams['id' . $team['team_id']] = $team;
        }
        return $teams;
    }

    public function result($team_id = 0) {
        if ($team_id > 0) {

            return isset($this->teams['id' . $team_id]) ? $this->teams['id' . $team_id] : NULL;
        }
        return $this->teams;
    }

}
