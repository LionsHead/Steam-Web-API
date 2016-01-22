## Steam Web API
1. STEAM_API_KEY - see www.steamcommunity.com/dev/apikey.
2. Language -  language format ISO639-1 English (en, en_US), Russian (ru, ru_RU).
3. Format - json (default), xml, vdf - Valve Data Format.
 
## example:

````php
use SteamApi\SteamApi;
use SteamApi\JsFeed;
use SteamApi\DotaApi;
````
####Steam api
 get  steam profile
````php
$api = new SteamApi(STEAM_API_KEY);
$steam_ids_64bit = [SteamApi::convertUserId(36553880)]; // convertaion steam id - 32 to 64bit
$json = $api->getPlayerSummaries((array) $steam_ids_64bit);
print_r($json);
````
get app achivements, default app - dota2 (570) 
````php
$steam_id_64bit = SteamApi::convertUserId(36553880);
$json = $api->getPlayerAchievements((int) $steam_id_64bit,(int) $app_id); 
print_r($json);
````
get lvl, integer
````php
$json = $api->getSteamLevel((int) $steam_id64bit); 
echo $json;
````
get player friends
````php
$json = $api->getFriendList((int) $steam_id64bit); 
print_r($json);
````
get player groups
````php
$json = $api->getUserGroupList((int) $steam_id_64bit); 
print_r($json);
````
get inventory list
````php
$json = $api->getUserInventory((int) $steam_id_64bit,(int) $app_id); 
print_r($json);
````
get users ban info
````php
$json = $api->getPlayerBans((array) $steam_ids_64bit); 
print_r($json);
````
get recently played games
````php
$json = $api->getRecentlyPlayedGames((int) $steam_id_64bit, (int) $counter = 25); 
print_r($json);
````
get steam app list
````php
$json= $api->getAppList(); // get steam app list
print_r($json);
````
####DotA 2 api
````php
$api = new DotaApi(STEAM_API_KEY);

# Live league games
$json = $api->getLiveLeagueGames();
print_r($json);

# Dota 2 match details info
$match_id = 1745689587; // ti5 final
$json = $api->getMatchDetails($id);
print_r($json);
````
 heropedia dota2.com
 ````php
$api = new JsFeed();
$api->LANGUAGE_JS = 'russian'; // optional

// get current items descriptions 
$json = $api->getItemData();
print_r($json);

// get current ability descriptions
$json = $api->getAbilityData();
print_r($json);

// get current hero info
$json = $api->getHeroData();
print_r($json);
````
