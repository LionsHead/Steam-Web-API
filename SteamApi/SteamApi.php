<?php

namespace SteamApi;

use SteamApi\Steam\SteamUser;

class SteamApi extends Request {

    const FILTER = TRUE; // pre-filtering (htmlentities) of user data (nicknames, teams name)
    //
    const GET_APP_LIST = 'http://api.steampowered.com/ISteamApps/GetAppList/v2/';
    const GET_PLAYERS = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    const GET_FRIENDS = 'http://api.steampowered.com/ISteamUser/GetFriendList/v1/';
    const GET_USER_GROUP = 'http://api.steampowered.com/ISteamUser/GetUserGroupList/v1/';
    const GET_ACHIVEMENTS = 'http://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1/';
    const GetUGCFileDetails = 'http://api.steampowered.com/ISteamRemoteStorage/GetUGCFileDetails/v1/';
    const GET_APP_NEWS = 'http://api.steampowered.com/ISteamNews/GetNewsForApp/v2';
    const GET_BANS = 'http://api.steampowered.com/ISteamUser/GetPlayerBans/v1/';
    const GET_STEAM_LVL = 'http://api.steampowered.com/IPlayerService/GetSteamLevel/v1/';
    const GET_PLAYED_GAMES = 'http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v1/';
    const GET_API_LIST = 'http://api.steampowered.com/ISteamWebAPIUtil/GetSupportedAPIList/v1/';
    /**
     * ID account with a hidden public histories matches:
     */
    const ANONYMOUS = 4294967295;

    /**
     *  The function used to convert 64bit Steam ID to 32bit and the other way around.
     *  thx https://gist.github.com/almirsarajcic/4664387
     * @param int $id
     * @return int Steam ID
     */
    public static function convertUserId($id) {
        if (strlen($id) === 17) {
            $converted = substr($id, 3) - 61197960265728;
        } else {
            $converted = '765' . ($id + 61197960265728);
        }
        return (string) $converted;
    }

    /**
     * all supported api interfaces/methods
     * @return type
     */
    public function getSupportedAPIList() {
        $json = $this->send(SteamApi::GET_API_LIST);
        return $json['apilist']['interfaces'];
    }

    /**
     *
     * @return array
     *      int app_id  => string app_name
     */
    public function getAppList() {
        $json = $this->send(SteamApi::GET_APP_LIST);

        $apps = [];
        foreach ($json['applist']['apps'] as $data) {
            $apps[$data['appid']] = $data['name'];
        }

        return $apps;
    }

    /**
     *
     * @param array $steamids
     * @return array steam users info
     *      int steamid - the user's 64 bit ID
     *      int communityvisibilitystate - An integer that describes the access setting of the profile
     *           1 = Private
     *           2 = Friends only
     *           3 = Friends of Friends
     *           4 = Users Only
     *           5 = Public
     *      int profilestate - If set to 1 the user has configured the profile.
     *      string personaname - User's display name.
     *      int lastlogoff - A unix timestamp of when the user was last online.
     *      string(url) profileurl - The url to the user's Steam Community profile.
     *      string(url) avatar - 32x32 image url
     *      string(url) avatarmedium - 64x64 image url
     *      string(url)avatarfull - 184x184 image url
     *      int personastate - The user's status
     *           0 = Offline (Also set when the profile is Private)
     *           1 = Online
     *           2 = Busy
     *           3 = Away
     *           4 = Snooze
     *           5 = Looking to trade
     *           6 = Looking to play
     */
    public function getPlayerSummaries(array $steamids = []) {
        $ids = '';
        foreach ($steamids as $id) {
            if ($id <> SteamApi::ANONYMOUS) {
                $ids .= $id . ',';
            }
        }
        $json = $this->send(SteamApi::GET_PLAYERS, [
            'steamids' => $ids
                ]);
        $users = new SteamUser($json['response']['players']);
        return $users->getUsers();
    }

    /**
     *
     * @param int $steamid
     * @param int $appid
     * @return array playerstats
     *      int steamID 64 bit
     *      string gameName - the game title
     *      array achievements - List of achievement objects
     *          int apiname - String containing the ID of the achievement.
     *          bool achieved - Integer to be used as a boolean value indicating whether or not the achievement has been unlocked by the user.
     *          string name - String containing the localized title of the achievement.
     *          string description - String containing the localized string or requirements of the achievement.
     *      int success - Boolean value indicating if the request was successful.
     */
    public function getPlayerAchievements($steamid = 64, $appid = 570) {
        $json = $this->send(SteamApi::GET_ACHIVEMENTS, [
            'steamid' => $steamid,
            'appid' => $appid,
            'l' => $this->STEAM_API_LNG
                ]);
        if (!isset($json['playerstats'])) {
            return NULL;
        }

        return $json['playerstats'];
    }

    /**
     *
     * @param int $steamid 64 BIT
     * @return int steam lvl
     */
    public function getSteamLevel($steamid = 64) {
        $json = $this->send(SteamApi::GET_STEAM_LVL, [
            'steamid' => $steamid
                ]);
        if (!isset($json['response']['player_level'])) {
            return 0;
        }
        return (int) $json['response']['player_level'];
    }

    /**
     *
     * @param array $steamids
     * @return array
     */
    public function getPlayerBans(array $steamids = []) {
        $json = $this->send(SteamApi::GET_BANS, [
            'steamids' => implode(',', $steamids)
                ]);
        $players = [];
        foreach ($json['players'] as $value) {
            $players[$value['SteamId']] = [
                'steam_id' => $value['SteamId'],
                'CommunityBanned' => (int) $value['CommunityBanned'],
                'VACBanned' => (int) $value['VACBanned'],
                'NumberOfVACBans' => (int) $value['NumberOfVACBans'],
                'DaysSinceLastBan' => (int) $value['DaysSinceLastBan'],
                'NumberOfGameBans' => (int) $value['NumberOfGameBans'],
                'EconomyBan' => $value['EconomyBan'] == 'none' ? 0 : 1
            ];
        }
        return $players;
    }

    /**
     *
     * @param int $steamid 64 bit
     * @param int $count
     * @return null|array
     */
    public function getRecentlyPlayedGames($steamid = 64, $count = 25) {
        $json = $this->send(SteamApi::GET_PLAYED_GAMES, [
            'steamid' => $steamid,
            'count' => $count
                ]);
        if (!isset($json['response']['games'])) {
            return NULL;
        }
        $games = [];
        foreach ($json['response']['games'] as $data) {
            $data['img_icon_url'] = 'http://media.steampowered.com/steamcommunity/public/images/apps/' . $data['appid'] . '/' . $data['img_icon_url'] . '.jpg';
            $data['img_logo_url'] = 'http://media.steampowered.com/steamcommunity/public/images/apps/' . $data['appid'] . '/' . $data['img_logo_url'] . '.jpg';
            $games[$data['appid']] = $data;
        }
        return $games;
    }

    /**
     * @param int $steamid 64 bit
     * @param type $relationship
     * @return array
     */
    public function getFriendList($steamid = 64, $relationship = 'all') {
        $json = $this->send(SteamApi::GET_FRIENDS, [
            'steamid' => $steamid,
            'relationship' => $relationship
                ]);
        // If the profile is not public or there are no available entries for the given relationship only an empty object will be returned.
        if (!isset($json['friendslist']['friends'])) {
            return NULL;
        }

        return $json['friendslist']['friends'];
    }

    /**
     * return an array groups of user
     * @param int $steamid
     * @return array
     */
    public function getUserGroupList($steamid = 64) {
        $json = $this->send(SteamApi::GET_USER_GROUP, [
            'steamid' => $steamid
                ]);
        if (!isset($json['response']['groups'])) {
            return NULL;
        }
        $groups = [];
        foreach ($json['response']['groups'] as $g) {
            $groups[] = $g['gid'];
        }
        return $groups;
    }

    public function getUserInventory($steamid = 64) {
        return $this->send('http://steamcommunity.com/profiles/' . $steamid . '/inventory/json/570/2/', []);
    }

    /**
     * steam news feed (ENG ONLY)
     * @param array $params:
     *      int maxlength - The max length of the contents field.
     *      int enddate - Unix timestamp, returns posts before this date.
     *      int count - The max number of news items to retrieve. Default: 20.
     *      string feeds - Commma-seperated list of feed names to return news for.
     *
     * @return arrays 		},
     */
    public function GetNewsForApp(array $params = []) {
        if (!isset($params['appid'])) {
            $params['appid'] = 570; // dota 2 appid
        }
        $json = $this->send(SteamApi::GET_APP_NEWS, $params);

        if (!isset($json['appnews']['newsitems'])) {

            return $json['appnews']['statusDetail'];
        }
        return $json['appnews']['newsitems'];
    }

    /**
     * This request is used to fetch file information for given UGC files.
     * Currently certain items found in a player's backpack that can have a custom texture applied
     *  to a part of them have attributes attached used for generating the 64 bit ID.
     *  The attribute values for generating the ID are stored in two attributes for portability reasons:
     *  custom texture lo is the low word, and custom texture hi is the high word.
     *
     * @param array $params
     *    steamid - If specified, only returns details if the file is owned by the SteamID specified
     *    ugcid  - ID of UGC file to get info for
     *    appid - appID of product
     *
     * @return array
     *    filename - Path to the file along with its name
     *    url - URL to the file
     *    size - Size of the file
     */
    public function getUGCFile(array $params = []) {
        $json = $this->send(SteamApi::GetUGCFileDetails, $params);
        if (isset($json['status']['code']) and $json['status']['code'] == 9) {
            return 'this id was not found.';
        }
        return $json['data'];
    }

    /**
     * work?
     * @param int $appid
     */
    public function getSchema($appid = 570) {
        $json = $this->send('http://api.steampowered.com/IEconItems_' . $appid . '/GetSchema/v1/', []);
        if (!isset($json['result'])) {
            return NULL;
        }
        return $json;
    }

    /**
     * filter
     * @param string $str
     * @return string
     */
    public static function filter($str) {
        return htmlentities(trim($str), ENT_QUOTES);
    }

}
