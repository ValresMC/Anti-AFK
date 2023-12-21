<?php

namespace Valres\AntiAFK;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class Main extends PluginBase implements Listener
{
    public static array $cooldown = [];
    private int $coold;
    private string $message;

    protected function onEnable(): void
    {
        $this->getLogger()->info("by Valres est lancé !");
        $this->saveDefaultConfig();
        $this->coold = $this->getConfig()->get("max-time");
        $this->message = $this->getConfig()->get("message");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(){
            foreach(self::$cooldown as $player => $time) {
                if(!isset(self::$cooldown[$player]) || self::$cooldown[$player] - time() <= 0){
                    $p = Server::getInstance()->getPlayerExact($player);
                    if(!$p->hasPermission("antiafk.bypass")){
                        $p->kick($this->message);
                        unset(self::$cooldown[$player]);
                    }
                }
            }
        }), 20);
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();

        if(!isset(self::$cooldown[$player->getName()])){
            self::$cooldown[$player->getName()] = time() + $this->coold;
        }
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if(!isset(self::$cooldown[$player->getName()])){
            unset(self::$cooldown[$player->getName()]);
        }
    }

    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();

        self::$cooldown[$player->getName()] = time() + $this->coold;
    }
}
