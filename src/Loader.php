<?php

namespace dhnnz\Friend;

use dhnnz\Friend\api\API;
use dhnnz\Friend\commands\FriendCommand;
use dhnnz\Friend\forms\FormManager;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Loader extends PluginBase
{
    use SingletonTrait;

    public function onEnable(): void
    {
        $this->setInstance($this);
        @mkdir($this->getDataFolder() . "accounts/");
        $this->getServer()->getCommandMap()->register("Friend", new FriendCommand($this), "friend");
        $this->getServer()->getPluginManager()->registerEvent(PlayerLoginEvent::class, function (PlayerLoginEvent $playerLoginEvent) {
            $p = $playerLoginEvent->getPlayer();
            if (!file_exists($this->getDataFolder() . "accounts/" . $p->getName() . ".json")) {
                API::getInstance()->registerAccount($p->getName(), $p->getXuid());
            }
        }, EventPriority::NORMAL, $this);
    }

    /**
     * Summary of getFormManager
     * @return FormManager
     */
    public function getFormManager(): FormManager
    {
        return new FormManager($this);
    }
}