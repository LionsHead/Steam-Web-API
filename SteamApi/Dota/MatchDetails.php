<?php

namespace SteamApi\Dota;

/**
 * Detailed information about the result of matches
 */
use SteamApi\DotaApi;

class MatchDetails extends DotaApi {

    protected $gameMode = [
        '0' => 'None',
        '1' => 'All Pick',
        '2' => 'Captains Mode',
        '3' => 'Random Draft',
        '4' => 'Single Draft',
        '5' => 'All Random',
        '6' => 'Intro',
        '7' => 'Diretide',
        '8' => 'Reverse Captains Mode',
        '9' => 'The Greeviling',
        '10' => 'Tutorial',
        '11' => 'Mid Only',
        '12' => 'Least Played',
        '13' => 'New Player Pool',
        '14' => 'Compendium Matchmaking',
        '16' => 'Captains Draft'
    ];
    protected $lobbyType = [
        '-1' => 'Invalid',
        '0' => 'Public matchmaking',
        '1' => 'Practise',
        '2' => 'Tournament',
        '3' => 'Tutorial',
        '4' => 'Co-op with bots',
        '5' => 'Team match',
        '6' => 'Solo Queue',
    ];

    /**
     * picks and bans array:
     * 'pick_radiant', 'ban_radiant', 'pick_dire', 'ban_dire'
     * @var type
     */
    public $draft = [];

    /**
     *
     */
    public $matchInfo = NULL;

    /**
     *
     * @var array
     */
    public $players = [];

    /**
     *
     * @var array
     */
    public $teams = [
        'radiant' => [],
        'dire' => []
    ];

    function __construct($json = NULL) {
        // capitans?
        if (isset($json['picks_bans'])) {
            $this->setPicksBans($json['picks_bans']);
            unset($json['picks_bans']);
        }
        $this->setMatchInfo($json);

        $this->setMatchPlayers();
    }

    /**
     *
     * @param type $json
     */
    public function setMatchInfo($json = NULL) {
        if (!is_array($json)) {
            die('Steam api return bad response: not an array');
        }
        // heresy start
        $json['league_id'] = $json['leagueid'];
        // unset($this->matchInfo['leagueid']);
        // heresy end? realy?

        // VOLVO give replay salt back
        $json['dire_win'] = ($json['radiant_win'] === TRUE) ? 0 : 1; // more info
        $this->matchInfo = $json;
    }

    /**
     * parse picks and bans array
     * @param array $draftArray
     */
    public function setPicksBans(array $draftArray = []) {

        foreach ($draftArray as $key => $value) {
            $team = ($value['team'] === 1) ? 'dire' : 'radiant';
            $type = ($value['is_pick'] == TRUE) ? 'pick' : 'ban'; // @#$%!!
            //  picks and bans will be from 1st number
            $this->draft[$type . '_' . $team][++$key] = [
                'hero_id' => (int) $value['hero_id'],
                'type' => $type,
                'team' => $team
            ];
        }
    }

    /**
     *
     * @return null\array
     */
    public function setMatchPlayers() {
        if (!isset($this->matchInfo['players'])) {
            return NULL;
        }

        foreach ($this->matchInfo['players'] as $key => $playerSlot) {
            $this->playerSlot($playerSlot);
        }

        unset($this->matchInfo['players']);
    }

    /**
     *
     * @param array $playerSlot
     */
    public function playerSlot($slot) {
        $slot['team'] = ($slot['player_slot'] < 99) ? 'radiant' : 'dire';


        $this->teams[$slot['team']]['slot_' . $slot['player_slot']] = (int) $slot['account_id'];
        $this->players['slot_' . $slot['player_slot']] = $slot;
    }

    /**
     *
     * @param int $id
     * @return string
     */
    public function getGameMode($id = 1) {
        if (isset($this->gameMode[$id])) {
            return $this->gameMode[$id];
        } else {
            return $this->gameMode[0];
        }
    }

    /**
     *
     * @param int $id
     * @return type
     */
    public function getLobbyType($id = '-1') {
        if (isset($this->lobbyType[$id])) {
            return $this->lobbyType[$id];
        } else {
            return $this->lobbyType['-1'];
        }
    }

}
