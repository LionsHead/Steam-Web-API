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
        '14' => 'Compendium Matchmaking', // ?
        '16' => 'Captains Draft',
        '17' => 'Balanced Draft',
        '18' => 'Ability Draft',
        '19' => 'Unknow',
        '20' => 'All Random Death Match',
        '21' => 'Solo Mid 1vs1',
        '22' => 'Ranked All Pick'
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
        '7' => 'Ranked matchmaking',
        '8' => 'Solo Mid 1vs1'
    ];
    // max id of regions
    protected $maxReqions = [
        '270' => 'India',
        '260' => 'Peru',
        '250' => 'Chile',
        '240' => 'China',
        '220' => 'South Africa',
        '210' => 'South America',
        '200' => 'Europe East',
        '190' => 'Russia',
        '180' => 'Australia',
        '170' => 'China',
        '160' => 'Southeast Asia',
        '150' => 'South Korea',
        '140' => 'Europe West',
        '130' => 'US East',
        '120' => 'US West'
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
        
        // heresy end? realy?
        // VOLVO give replay salt back
        $json['dire_win'] = ($json['radiant_win'] === TRUE) ? 0 : 1; // more info
        $json['radiant_win'] = (int) (bool) $json['radiant_win'];
        $json['region'] = $this->getRegion($json['cluster']);
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

    /**
     *  definition of server region
     *
     * @param int cluster id
     * @return string server reqion name
     */
    public function getRegion($id = 322) {
        $region = 'Unknow';

        foreach ($this->maxReqions as $r_id => $r_name) {
            // assigned region if  cluster less max region id
            if ($id < $r_id) {
                $region = $r_name;
            }
        }
        return $region;
    }

}
