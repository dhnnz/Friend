<?php

namespace dhnnz\Friend\forms;

use DateTime;
use dhnnz\Friend\api\API;
use dhnnz\Friend\libs\jojoe77777\FormAPI\CustomForm;
use dhnnz\Friend\libs\jojoe77777\FormAPI\SimpleForm;
use dhnnz\Friend\Loader;
use pocketmine\player\Player;
use pocketmine\Server;

/**
 * Summary of FormManager
 */
class FormManager
{

    /**
     * Summary of __construct
     * @param Loader $plugin
     */
    public function __construct(protected Loader $plugin)
    {
    }

    /**
     * Summary of main
     * @param Player $p
     * @return SimpleForm
     */
    public function main(Player $p)
    {
        $form = new SimpleForm(function (Player $p, $data = null) {
            if ($data === null)
                return;
            match ($data) {
                "manage" => $this->manageFriendMain($p),
                "add" => $this->addFriendMain($p),
                "request" => $this->requestFriends($p),
                default => ""
            };
        });
        $form->setTitle("Friend");
        $form->addButton("§lManage Friends\n§r§9You have §l" . count(API::getInstance()->getFriendList($p->getName())) . " §r§9friends", label: "manage");
        $form->addButton("§lAdd Friend", label: "add");
        $form->addButton("§lFriend Request §r§9(" . count(API::getInstance()->getRequests($p->getName())) . ")", label: "request");
        $form->addButton("§l§cClose", 0, "textures/blocks/barrier", label: "close");
        return $form;
    }

    /**
     * Summary of manageFriendMain
     * @param Player $p
     * @return SimpleForm
     */
    public function manageFriendMain(Player $p)
    {
        $form = new SimpleForm(function (Player $p, $data = null) {
            if ($data === null)
                return;
            if ($data == "close")
                return;
            $form = new SimpleForm(
                function (Player $p, $data = null) {
                    if ($data === null)
                        return;
                    if ($data !== "close"){
                        API::getInstance()->removeFriend($p->getName(), $data);
                        $p->sendMessage("§aYou have removed §e".$data."§a from the friends list.");
                        $fPlayer = Server::getInstance()->getPlayerExact($data);
                        if($fPlayer instanceof Player){
                            $fPlayer->sendMessage("§f".$p->getName()." §cremoved you from the friend list");
                        }
                    }
                    return;
                }
            );
            $date = API::getInstance()->getFriendData($p->getName(), $data)["dated"];
            $timeAgo = API::getInstance()->getTimeAgo(DateTime::createFromFormat("Y-m-d H:i:s", $date)->getTimestamp());
            $form->setTitle("Friend $data");
            $form->setContent("§fFriend since: §a" . $date . "§7 (" . $timeAgo . ")");
            $form->addButton("§lRemove Friend", label: $data);
            $form->addButton("§l§cClose", 0, "textures/blocks/barrier", label: "close");
            $form->sendToPlayer($p);
            return;
        });
        $form->setTitle("Manage Friends");
        foreach (API::getInstance()->getFriendList($p->getName()) as $i => $friends) {
            foreach ($friends as $friend => $fdata) {
                $online = (Server::getInstance()->getPlayerExact($friend) instanceof Player) ? true : false;
                $form->addButton($friend . "\n" . (($online) ? "§l§aONLINE" : "§l§cOFFLINE"), label: $friend);
            }
        }
        $form->addButton("§l§cClose", 0, "textures/blocks/barrier", label: "close");
        $form->sendToPlayer($p);
        return $form;
    }

    /**
     * Summary of addFriendMain
     * @param Player $p
     * @return CustomForm
     */
    public function addFriendMain(Player $p)
    {
        $players = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player !== $p) {
                if (!API::getInstance()->isFriend($p->getName(), $player->getName())) {
                    $players[] = $player->getName();
                }
            }
        }
        $form = new CustomForm(function (Player $p, $data = null) use ($players) {
            if ($data === null)
                return;
            $friendName = Server::getInstance()->getPlayerExact($players[array_search($data, $players)]);
            if ($friendName instanceof Player) {
                $p->sendMessage("§aYou have sent a friend request to §r" . $friendName->getDisplayName());
                API::getInstance()->addRequest($p->getName(), $friendName->getName());
                $friendName->sendMessage($p->getDisplayName() . " §asent you a friend request. To accept the friend request. type /friend -> Friend Requests");
            }

        });
        $form->setTitle("Add Friend");
        $form->addDropdown("Select Player", $players);
        $form->sendToPlayer($p);
        return $form;
    }

    /**
     * Summary of requestFriends
     * @param Player $p
     * @return SimpleForm
     */
    public function requestFriends(Player $p)
    {
        $form = new SimpleForm(function (Player $p, $data = null) {
            if ($data === null)
                return;
            if ($data == "close")
                return;
            $acceptFriend = API::getInstance()->acceptRequest($p->getName(), $data);
            $friend = Server::getInstance()->getPlayerExact($data);
            if ($acceptFriend) {
                if ($friend instanceof Player) {
                    $friend->sendMessage("§aYour friend request to §r" . $p->getDisplayName() . "§a has been accepted.");
                    $p->sendMessage("§aYou have accepted §r" . $friend->getDisplayName() . "§a friend request.");
                } else {
                    $p->sendMessage("§cplayer $data offline.");
                }
            }
        });
        $form->setTitle("Friend Requests");
        foreach (API::getInstance()->getRequests($p->getName()) as $i => $friends) {
            foreach ($friends as $friend => $fdata) {
                $form->addButton($friend, label: $friend);
            }
        }
        $form->addButton("§l§cClose", 0, "textures/blocks/barrier", label: "close");
        $form->sendToPlayer($p);
        return $form;
    }
}