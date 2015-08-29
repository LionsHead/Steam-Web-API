<?php

namespace SteamApi\Steam;

use SteamApi\SteamApi;

class SteamUser extends SteamApi {

    private $users;

    public function __construct($json = []) {
        $this->users = $this->setUsers($json);
    }

    public function setUsers($users = []) {
        $profiles = [];
        foreach ($users as $user) {
            $user['personaname'] = (SteamApi::FILTER) ? SteamApi::filter($user['personaname']) : $user['personaname'];
            $user['steam_id_64'] = (string) $user['steamid']; // 64-bit account ID
            $user['steam_id_32'] = (int) SteamApi::convertUserId($user['steamid']); // 32-bit account ID

            $profiles[] = $user;
        }

        return $profiles;
    }

    public function getUsers() {
        return $this->users;
    }

}

