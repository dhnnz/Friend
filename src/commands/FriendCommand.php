<?php

namespace dhnnz\Friend\commands;

use dhnnz\Friend\api\API;
use dhnnz\Friend\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;

class FriendCommand extends Command implements PluginOwned
{

    /**
     * Summary of __construct
     * @param Loader $plugin
     */
    public function __construct(protected Loader $plugin)
    {
        $this->setAliases(["f", "friends"]);
    }

    /**
     * Summary of execute
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player)
            return;
        if (count($args) < 1) {
            $form = $this->getOwningPlugin()->getFormManager()->main($sender);
            $form->sendToPlayer($sender);
            return;
        }
        switch ($args[0]) {
            case "online":
                $fonline = [];
                foreach (API::getInstance()->getFriendList($sender->getName()) as $i => $friends) {
                    foreach ($friends as $friend => $fdata) {
                        if (Server::getInstance()->getPlayerExact($friend) instanceof Player) {
                            $fonline[] = $friend;
                        }
                    }
                }
                $sender->sendMessage("§6There are §a" . count($fonline) . "§6 online friends:");
                $sender->sendMessage("§e" . implode(", ", $fonline));
                break;
            case "chat":
                unset($args[0]);
                $chat = implode(" ", $args);
                foreach (API::getInstance()->getFriendList($sender->getName()) as $i => $friends) {
                    foreach ($friends as $friend => $fdata) {
                        $fPlayer = Server::getInstance()->getPlayerExact($friend);
                        if ($fPlayer instanceof Player) {
                            $fPlayer->sendMessage("§5FRIEND > §r" . $sender->getDisplayName() . ": " . $chat);
                        }
                    }
                }
                $sender->sendMessage("§5FRIEND > §r" . $sender->getDisplayName() . ": " . $chat);
                break;
            default:
                $form = $this->getOwningPlugin()->getFormManager()->main($sender);
                $form->sendToPlayer($sender);
                break;
        }
    }

    /**
     * Summary of getOwningPlugin
     * @return Loader
     */
    public function getOwningPlugin(): Loader
    {
        return $this->plugin;
    }
}