#### Steam Web API
 Language -  language format ISO639-1 English (en, en_US), Russian (ru, ru_RU).
 Format - json (default), xml, vdf - Valve Data Format.
 API_KEY - see www.steamcommunity.com/dev/apikey.
 
#### example:

````php
use SteamApi\SteamApi;
use SteamApi\JsFeed;
use SteamApi\DotaApi;
````

````php
$api = new SteamApi(STEAM_KEY);
$steam_id64bit = [SteamApi::convertUserId(36553880)]; // convertaion steam id - 32 to 64bit
$json = $api->getPlayerSummaries($steam_id64bit); // get  steam profile
print_r($json);
````

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

````php
$api = new JsFeed();
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
