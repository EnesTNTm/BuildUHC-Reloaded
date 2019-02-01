<?php

namespace builduhc\event;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
use builduhc\arena\Arena;
use builduhc\BuildUHC;

class PlayerArenaWinEvent extends PluginEvent {

    /** @var null $handlerList */
    public static $handlerList = \null;

    /** @var Player $player */
    protected $player;

    /** @var Arena $arena */
    protected $arena;

    /**
     * PlayerArenaWinEvent constructor.
     * @param BuildUHC $plugin
     * @param Player $player
     * @param Arena $arena
     */
    public function __construct(BuildUHC $plugin, Player $player, Arena $arena) {
        $this->player = $player;
        $this->arena = $arena;
        parent::__construct($plugin);
    }

    /**
     * @return Player $arena
     */
    public function getPlayer(): Player {
        return $this->player;
    }

    /**
     * @return Arena $arena
     */
    public function getArena(): Arena {
        return $this->arena;
    }
}