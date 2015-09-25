#### Steam Web API
1. API_KEY - see www.steamcommunity.com/dev/apikey.
2. Language -  language format ISO639-1 English (en, en_US), Russian (ru, ru_RU).
3. Format - json (default), xml, vdf - Valve Data Format.
 
#### example:

````php
use SteamApi\SteamApi;
use SteamApi\JsFeed;
use SteamApi\DotaApi;
````
Steam api
````php
$api = new SteamApi(STEAM_KEY);
$steam_id64bit = [SteamApi::convertUserId(36553880)]; // convertaion steam id - 32 to 64bit
$json = $api->getPlayerSummaries($steam_id64bit); // get  steam profile
print_r($json);
````
DotA 2 api
````php
$api = new DotaApi(STEAM_KEY);

# Live league games
$json = $api->getLiveLeagueGames();
print_r($json);

# Dota 2 match details info
$match_id = 1745689587; //ti5 final
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
