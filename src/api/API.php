<?php

namespace dhnnz\Friend\api;

use dhnnz\Friend\Loader;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

/**
 * Summary of API
 */
class API
{
    use SingletonTrait;

    /**
     * Summary of plugin
     * @var Loader
     */
    protected Loader $plugin;

    public function __construct()
    {
        $this->setInstance($this);
        $this->plugin = Loader::getInstance();
    }

    /**
     * Summary of registerAccount
     * @param string $playerName
     * @param string $playerXuid
     * @return void
     */
    public function registerAccount(string $playerName, string $playerXuid)
    {
        $config = new Config($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json", Config::JSON, []);
        $configData = array(
            "name" => $playerName,
            "xuid" => $playerXuid,
            "friends" => array(),
            "requests" => array()
        );
        $config->setAll($configData);
        $config->save();
        $config->reload();
    }

    /**
     * Summary of getFriendList
     * @param string $playerName
     * @return array
     */
    public function getFriendList(string $playerName): array
    {
        if (!file_exists($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json"))
            return array();
        $config = new Config($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json", Config::JSON, []);
        return $config->getAll()["friends"];
    }

    /**
     * Summary of getRequests
     * @param string $playerName
     * @return array
     */
    public function getRequests(string $playerName): array
    {
        if (!file_exists($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json"))
            return array();
        $config = new Config($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json", Config::JSON, []);
        return $config->getAll()["requests"];
    }

    /**
     * Summary of getFriendData
     * @param string $playerName
     * @param string $friendName
     * @return array|bool
     */
    public function getFriendData(string $playerName, string $friendName): array|bool
    {
        if (!file_exists($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json"))
            return array();
        $config = new Config($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json", Config::JSON, []);
        $friends = $config->getAll()["friends"];
        $key = array_search($friendName, $friends);

        return isset($friends[$key][$friendName]) ? $friends[$key][$friendName] : false;
    }

    /**
     * Summary of getRequestData
     * @param string $playerName
     * @param string $friendName
     * @return array|bool
     */
    public function getRequestData(string $playerName, string $friendName): array|bool
    {
        if (!file_exists($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json"))
            return array();
        $config = new Config($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json", Config::JSON, []);
        $requests = $config->getAll()["requests"];
        $key = array_search($friendName, array_column($requests, $friendName));

        return isset($requests[$key][$friendName]) ? $requests[$key][$friendName] : false;
    }

    /**
     * Summary of addFriend
     * @param string $playerName
     * @param string $newFriend
     * @return bool|string
     */
    public function addFriend(string $playerName, string $newFriend)
    {
        $config = new Config($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json", Config::JSON, []);
        $configData = $config->getAll();
        array_push(
            $configData["friends"],
            array(
                $newFriend => array(
                    "name" => $newFriend,
                    "dated" => date("Y-m-d h:i:s")
                )
            )
        );
        $config->setAll($configData);
        $config->save();
        return json_encode($configData);
    }

    /**
     * Summary of addRequest
     * @param string $playerName
     * @param string $newRequest
     * @return bool|string
     */
    public function addRequest(string $playerName, string $newRequest)
    {
        $config = new Config($this->plugin->getDataFolder() . "accounts/" . $newRequest . ".json", Config::JSON, []);
        $configData = $config->getAll();
        array_push(
            $configData["requests"],
            array(
                $playerName => array(
                    "name" => $playerName,
                    "dated" => date("Y-m-d h:i:s")
                )
            )
        );
        $config->setAll($configData);
        $config->save();
        return json_encode($configData);
    }

    /**
     * Summary of acceptRequest
     * @param string $playerName
     * @param string $friendName
     * @return bool
     */
    public function acceptRequest(string $playerName, string $friendName): bool
    {
        if (!file_exists($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json"))
            return false;
        $configPlayer = new Config($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json", Config::JSON, []);
        $configFriend = new Config($this->plugin->getDataFolder() . "accounts/" . $friendName . ".json", Config::JSON, []);
        $data = $configPlayer->getAll();
        $index = null;
        foreach ($data["requests"] as $key => $value) {
            if (isset($value[$friendName])) {
                $index = $key;
                break;
            }
        }
        if ($index !== null) {
            unset($data["requests"][$index][$friendName]);
        }
        $configPlayer->set("requests", $data["requests"]);
        $configPlayer->save();
        $this->addFriend($playerName, $friendName);
        $this->addFriend($friendName, $playerName);
        return true;
    }

    /**
     * Summary of getTimeAgo
     * @param mixed $time
     * @return string
     */
    public function getTimeAgo($time)
    {
        $time_difference = time() - $time;

        if ($time_difference < 1) {
            return 'less than 1 second ago';
        }
        $condition = array(
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($condition as $secs => $str) {
            $d = $time_difference / $secs;

            if ($d >= 1) {
                $t = round($d);
                return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
            }
        }
        return '';
    }

    /**
     * Summary of isFriend
     * @param string $playerName
     * @param string $friendName
     * @return bool
     */
    public function isFriend(string $playerName, string $friendName)
    {
        if (!file_exists($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json"))
            return false;
        $configPlayer = new Config($this->plugin->getDataFolder() . "accounts/" . $playerName . ".json", Config::JSON, []);
        $result = false;
        foreach ($configPlayer->getAll()["friends"] as $request) {
            if (isset($request[$friendName])) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * Summary of removeFriend
     * @param string $playerName
     * @param string $friendName
     * @return void
     */
    public function removeFriend(string $playerName, string $friendName)
    {
        $accountsPath = $this->plugin->getDataFolder() . "accounts/";

        $configPlayer = new Config($accountsPath . $playerName . ".json", Config::JSON, []);
        $configFriend = new Config($accountsPath . $friendName . ".json", Config::JSON, []);

        $arrayPlayer = $configPlayer->getAll();
        $arrayFriend = $configFriend->getAll();

        unset($arrayPlayer['friends'][array_search($friendName, array_column($arrayPlayer['friends'], 'name'))]);
        unset($arrayFriend['friends'][array_search($playerName, array_column($arrayFriend['friends'], 'name'))]);

        $configPlayer->setAll($arrayPlayer);
        $configPlayer->save();
        $configFriend->setAll($arrayFriend);
        $configFriend->save();
    }

}