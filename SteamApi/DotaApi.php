<?php

namespace SteamApi;

/**
 *
 */
use \SteamApi\Dota\TeamInfo;
use \SteamApi\Dota\MatchDetails;

class DotaApi extends Request {

    const GET_LIVE_GAMES = 'http://api.steampowered.com/IDOTA2Match_570/GetLiveLeagueGames/v1/';
    const GET_LEAGUE_LISTING = 'http://api.steampowered.com/IDOTA2Match_570/GetLeagueListing/v1/';
    const GET_MATCH_DETAILS = 'http://api.steampowered.com/IDOTA2Match_570/GetMatchDetails/v1/';
    const GET_MATCH_HISTORY = 'http://api.steampowered.com/IDOTA2Match_570/GetMatchHistory/v1/';
    const GET_TEAM_INFO = 'http://api.steampowered.com/IDOTA2Match_570/GetTeamInfoByTeamID/v1/';
    const GET_SCHEDULED_GAMES = 'http://api.steampowered.com/IDOTA2Match_570/GetScheduledLeagueGames/v1/';
    const GET_HEROES = 'http://api.steampowered.com/IEconDOTA2_570/GetHeroes/v1/';
    const GET_ITEMS = 'http://api.steampowered.com/IEconDOTA2_570/GetGameItems/v1/';
    const GET_RARITIES = 'http://api.steampowered.com/IEconDOTA2_570/GetRarities/v1/';
    const GET_PRIZE_POOL = 'http://api.steampowered.com/IEconDOTA2_570/GetTournamentPrizePool/v1/';

    /**
     * Live matches array
     * @return array
     */
    public function getLiveLeagueGames() {
        $json = $this->send(DotaApi::GET_LIVE_GAMES);
        if ($json['result']['status'] !== 200 or !isset($json['result']['games'])) {
            return NULL;
        }
        return $json['result']['games'];
    }

    /**
     * Dota 2 official leagues list
     * @return array
     *      string name -  name of the league.
     *      int leagueid -  league's unique ID.
     *      string description
     *      string tournament_url - The league's website. (http://www.domen or www.domen)
     */
    public function getLeagueListing() {
        $json = $this->send(DotaApi::GET_LEAGUE_LISTING);

        // captain obvious, no request "status"
        if (!isset($json['result']['leagues'])) {
            return NULL;
        }
        return $json['result']['leagues'];
    }

    /**
     * Get array matches. Additional options:
     *  int account_id - Selection of games for players (32 BIT account id!).
     *  int league_id - Only return matches from this league. A list of league ids can be found via the GetLeagues method.
     *  int start_at_match_id - Start searching for matches equal to or older than this match id.
     *  int matches_requested - Amount of matches to include in results (DEFAULT: 25).
     *  int hero_id - A list of hero IDs can be found via the GetHeroes method.
     *  int game_mode:
     *      0 - None??
     *      1 - All Pick
     *      2 - Captain's Mode
     *      3 - Random Draft
     *      4 - Single Draft
     *      5 - All Random
     *      6 - Intro
     *      7 - Diretide
     *      8 - Reverse Captain's Mode
     *      9 - The Greeviling
     *      10 - Tutorial
     *      11 - Mid Only
     *      12 - Least Played
     *      13 - New Player Pool
     *      14 - Compendium Matchmaking
     *      16 - Captain's Draft
     * 
     * // skill d`t work?
     *  int skill - Skill bracket for the matches (Ignored if an account ID is specified).
     *    0 - Any
     *    1 - Normal
     *    2 - High
     *    3 - Very High
     *
     *  int date_min - Minimum date range for returned matches (unix timestamp, rounded to the nearest day).
     *  int date_max - Maximum date range for returned matches (unix timestamp, rounded to the nearest day).
     *
     *  int min_players - Minimum amount of players in a match for the match to be returned.
     *  bool(int) tournament_games_only - Whether to limit results to tournament matches. (Not working??)
     *
     * @param array $params
     * @return array matches
     */
    public function getMatchHistory(array $params = []) {
        $json = $this->send(DotaApi::GET_MATCH_HISTORY, $params);

        // A message explaining the status, should status not be 1.
        if ($json['result']['status'] <> 1) {
            // доп статус: 15, Cannot get match history for a user that hasn't allowed it.
            return $json['result']['statusDetail'];
        }

        return $json['result']['matches'];
    }

    /**
     * Detailed information about the result of matches
     * @param int $match_id
     * @return array match details, null
     */
    public function getMatchDetails($match_id = 1697818230) {
        $json = $this->send(DotaApi::GET_MATCH_DETAILS, ['match_id' => (int) $match_id]);
        if (!isset($json['result']['match_id']) or is_null($json['result']['match_id'])) {
            return NULL;
        }
        return new MatchDetails($json['result']);
    }

    /**
     * information about a command or array of commands ($count > 1)
     * @param int $team_id
     * @param int $count
     * @return array, null
     */
    public function getTeamInfo($team_id = 39, $count = 1) {
        $json = $this->send(DotaApi::GET_TEAM_INFO, [
            'start_at_team_id' => $team_id,
            'teams_requested' => $count
                ]);

        // A message explaining the status, should status not be 1.
        if ($json['result']['status'] > 1 or !isset($json['result']['teams'])) {
            // доп статус: 8 - 'teams_requested' must be greater than 0.
            return (isset($json['result']['statusDetail'])) ? $json['result']['statusDetail'] : 'Bad request';
        }
        if (is_null($json['result']['teams']) or !isset($json['result']['teams'])) {
            return NULL;
        }

        $info = new TeamInfo($json['result']['teams']);
        // information about the team if need only one, else - teams array
        return $info->result(($count == 1) ? $team_id : 0);
    }

    /**
     * Return array scheduled games dota 2 leagues
     * @param type $time_min
     * @param type $time_max
     * @return array scheduled games
     *      int league_id
     *      int game_id
     *      array teams
     *           int team_id,
     *           string team_name,
     *           int logo - UGCFile id
     *      int starttime
     *      string comment
     *      bool final (false)
     */
    public function getScheduledLeagueGames($time_min = 0, $time_max = 0) {
        $params = [];
        if ($time_min > 0) {
            $params['date_min'] == $time_min;
        }
        if ($time_max > 0) {
            $params['date_max'] == $time_max;
        }
        $json = $this->send(DotaApi::GET_SCHEDULED_GAMES, $params = []);
        if (is_null($json['result']['games']) or !isset($json['result']['games'])) {
            return NULL;
        }
        return $json['result']['games'];
    }

    /**
     * @param bool $itemized - Return a list of itemized heroes only
     * @return array dota2 heroes
     *      int steam_id
     *      string steam_name
     *      string localized_name
     */
    public function getHeroes($itemized = 0) {
        $json = $this->send(DotaApi::GET_HEROES, $params = [
            'itemizedonly' => $itemized
                ]);
        if (is_null($json['result']['heroes']) or !isset($json['result']['heroes'])) {
            return NULL;
        }
        $heroes = [];
        foreach ($json['result']['heroes'] as $key => $hero) {

            $heroes[$hero['id']] = [
                'steam_id' => $hero['id'],
                'steam_name' => str_replace('npc_dota_hero_', NULL, $hero['name']),
                'localized_name' => $hero['localized_name']
            ];
        }

        return $heroes;
    }

    /**
     *  list of game items.
     * @return array items
     *      int steam_id
     *      string steam_name
     *      string localized_name
     *      int cost
     *      int secret_shop
     *      int side_shop
     *      int recipe
     */
    public function getGameItems() {
        $json = $this->send(DotaApi::GET_ITEMS);

        #return $json['result']['items'];
        $items = [];
        foreach ($json['result']['items'] as $id => $item) {
            $item['steam_id'] = $item['id'];
            $item['steam_name'] = str_replace('item_', NULL, $item['name']);

            $items[$item['id']] = $item;
        }

        return $items;
    }

    /**
     *
     * @return array
     */
    public function GetRarities() {
        $json = $this->send(DotaApi::GET_RARITIES, $params = []);
        if (!isset($json['result']['rarities'])) {
            return NULL;
        }
        $rarities = [];
        foreach ($json['result']['rarities'] as $key => $value) {
            $rarities[$value['name']] = [
                'name' => $value['localized_name'],
                'color' => $value['color']
            ];
        }
        return $rarities;
    }

    /**
     *
     * @return int
     */
    public function GetTournamentPrizePool($league_id = 600) {
        $json = $this->send(DotaApi::GET_PRIZE_POOL, ['leagueid' => $league_id]);
        $prize_pool = (isset($json['result']['prize_pool'])) ? (int) $json['result']['prize_pool'] : NULL;
        return $prize_pool;
    }

}
