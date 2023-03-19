# Friend

Friend Plugin for PocketMine is a useful plugin that allows players to easily manage their friends list in the game. This plugin enables players to add and remove friends from their list, view the status of their friends, and send private messages to them.

With this plugin, players can add friends by entering their usernames or selecting them from a list of online players. The plugin also provides an option to accept or decline friend requests, allowing players to control who can access their information.

The Friend Plugin for PocketMine also allows players to view their friends' online status, which indicates whether they are currently playing the game or not.

# FEATURES

- Friend Chat
- Friend Online
- Unlimited Friend

# Commands

- /friend (Main command)
- /friends (Main command)
- /f (Main command)

# Sub Commands

- /friend online (friend online)
- /friend chat (friend chat message)

# API

```php
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
```
registerAccount function
