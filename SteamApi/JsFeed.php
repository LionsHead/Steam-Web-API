<?php

namespace SteamApi;

use SteamApi\Request;

class JsFeed extends Request {

    const ABILITY_JS = 'http://www.dota2.com/jsfeed/abilitydata/';
    const ITEM_JS = 'http://www.dota2.com/jsfeed/itemdata/';
    const HEROPEDIA_JS = 'http://www.dota2.com/jsfeed/heropediadata/';
    const HEROPICKER_JS = 'http://www.dota2.com/jsfeed/heropickerdata/';
    const UNIQUE_USERS_JS = 'http://www.dota2.com/jsfeed/uniqueusers/';
    const TI_POOL_JS = 'http://www.dota2.com/jsfeed/intlprizepool/';

    public $LANGUAGE_JS = 'russian';
    
    public $fixit = [
        'techies_suicide' => 'techies_suicide_squad_attack', 
        'drow_ranger_wave_of_silence' =>  'drow_ranger_silence',
        'skeleton_king_mortal_strike' => 'skeleton_king_critical_strike'
    ];

    public function getAbilityData() {
        $json = $this->send(jsFeed::ABILITY_JS, ['l' => $this->LANGUAGE_JS]);

        $abilitydata = [];
        foreach ($json['abilitydata'] as $key => $value) {

            $value['steam_id'] = (int) $value['id'];
            unset($value['id']);
            $value['steam_name'] = $key;
            // удаляем лишние пробелы и переносы, чисто визуальная составляющая
            
            $value['affects'] = jsFeed::format($value['affects']);
            $value['attrib'] = jsFeed::format($value['attrib']);
            $value['dmg'] = jsFeed::format($value['dmg']);
            // удаляем img и перенос
            $value['cmb'] = preg_replace("/<(img|br)[^<>]*?>/", '', $value['cmb']); 
            
// метки чтоб не пропадали момо кассы - спс вольво
            if (array_key_exists($key, $this->fixit)){
                $key = $this->fixit[$key];
            }
            
            $abilitydata[$key] = $value;
        }
        return $abilitydata;
    }

       /**
        * удаление переноса в конце, удаление пробелов в перечислении
        * @param type $value
        * @return type
        */
    public static function format($value){
        return str_replace(" / ", "/",  preg_replace("/<br[^<>]*?>$/", '', $value));
    }
    
    /**
     * return all descripton heroes
     * @return array
     */
    public function getItemData() {
        $json = $this->send(jsFeed::ITEM_JS, ['l' =>  $this->LANGUAGE_JS], Request::ONLY_REQUIRED);

        $itemdata = [];
        // обрабатываем все данные
        foreach ($json['itemdata'] as $key => $value) {
            $value['steam_id'] = (int) $value['id'];
            unset($value['id']);
            $value['steam_name'] = $key;
            $itemdata[$value['steam_id']] = $value;
        }
        return $itemdata;
    }

    /**
     * return all descripton heroes
     * @return array
     */
    public function getHeroData(){
        $heropedia = $this->getHeropedia();
        $heropicer = $this->getHeroPickerData();

        $heroes = [];
        foreach ($heropedia as $key => $value) {
            $hero_a = $value;
            $hero_b = $heropicer[$key];
            $hero = array_merge($hero_a, $hero_b);
            //  delete duplicates
            unset($hero['droles']);
            unset($hero['roles_l']); // roles are not localized
            unset($hero['dac']); // see [atk], [atk_l]

            $heroes[$key] = $hero;
        }
        return $heroes;
    }

    /**
     * http://www.dota2.com/jsfeed/heropediadata?feeds=herodata&l=russian&callback=HeropediaDFReceive
     *
     * @return array
     */
    public function getHeropedia() {
        $data = $this->send(jsFeed::HEROPEDIA_JS, ['feeds' => 'herodata', 'l' =>  $this->LANGUAGE_JS], Request::ONLY_REQUIRED);
        $herodata = [];
        foreach ($data['herodata'] as $key => $value) {
            $value['steam_name'] = $key;
            $value['roles'] = explode(' - ', $value['droles']);

            $herodata[$key] = $value;
        }
        return $herodata;
    }

    /**
     * http://www.dota2.com/jsfeed/heropickerdata?l=russian
     *
     * @return array
     */
    public function getHeroPickerData() {
        $json = $this->send(jsFeed::HEROPICKER_JS, ['l' =>  $this->LANGUAGE_JS], Request::ONLY_REQUIRED);
        $herodata = [];
        foreach ($json as $key => $value) {
            $value['steam_name'] = $key;
            $herodata[$key] = $value;
        }
        return $herodata;
    }

    /**
     * return counter unique DotA 2 players in a month
     * @return int
     */
    public function getUniqueUsers(){
        $json = $this->send(jsFeed::UNIQUE_USERS_JS, [], Request::ONLY_REQUIRED);
        return (int) $json['users_last_month'];
    }

}

