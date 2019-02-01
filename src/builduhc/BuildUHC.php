<?php

namespace builduhc;

use pocketmine\command\Command;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use builduhc\arena\Arena;
use builduhc\commands\Commands;
use builduhc\math\Vector3;
use builduhc\provider\YamlDataProvider;

class BuildUHC extends PluginBase implements Listener {

    /** @var YamlDataProvider */
    public $dataProvider;

    /** @var Command[] $commands */
    public $commands = [];

    /** @var Arena[] $arenas */
    public $arenas = [];

    /** @var Arena[] $setters */
    public $setters = [];

    /** @var int[] $setupData */
    public $setupData = [];

    public function onEnable() {
        $this->getServer() -> getPluginManager()->registerEvents($this, $this);
        $this->dataProvider = new YamlDataProvider($this);
        $this->getServer()->getCommandMap()->register("builduhc", $this->commands[] = new Commands($this));
    }

    public function onDisable() {
        $this->dataProvider->saveArenas();
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();

        if(!isset($this->setters[$player->getName()])) {
            return;
        }

        $event->setCancelled(\true);
        $args = explode(" ", $event->getMessage());

        /** @var Arena $arena */
        $arena = $this->setters[$player->getName()];

        switch ($args[0]) {
            case "yardim":
                $player->sendMessage("§cBuildUHC Yardım Komutları \n".
                "§8» §7kisi (kişi sayısı): §3Kişi Sayısını Ayarlar \n".
                "§8»§7dunya (Dünya): §3Arena Alanını Seçersin\n".
                "§8»§7dogma (doğma): §3Doğma Alanını Ayarlar\n".
                "§8»§7tabela §3Tabelayı Ayarlar\n".
                "§8»§7kaydet: §3Dünyayı Kaydeder\n".
                "§8»§7baslat : §3Arenayı Kullanıma açar");
                break;
            case "kisi":
                if(!isset($args[1])) {
                    $player->sendMessage("§cKullanım: §7kisi <miktar:>");
                    break;
                }
                $arena->data["slots"] = (int)$args[1];
                $player->sendMessage("§a> Katılabilen Oyuncu Sayısı $args[1] Olarak Ayarlandı");
                break;
            case "dunya":
                if(!isset($args[1])) {
                    $player->sendMessage("§cKullanım: §7dunya <Dünyaİsmi>");
                    break;
                }
                if(!$this->getServer()->isLevelGenerated($args[1])) {
                    $player->sendMessage("§c> $args[1] Adlı Dünya Bulamadım");
                    break;
                }
                $player->sendMessage("§a> $args[1] Adlı Dünya Güncellendi!");
                $arena->data["level"] = $args[1];
                break;
            case "dogma":
                if(!isset($args[1])) {
                    $player->sendMessage("§cKullanım: §7dogma <katılımcı-kişi:>");
                    break;
                }
                if(!is_numeric($args[1])) {
                    $player->sendMessage("§cBu değer bir rakam olmalı!");
                    break;
                }
                if((int)$args[1] > $arena->data["slots"]) {
                    $player->sendMessage("§c Bu Arena{$arena->data["slots"]} Kişilik kisi komutu ile Ayarlamalısın");
                    break;
                }

                $arena->data["spawns"]["spawn-{$args[1]}"] = (new Vector3($player->getX(), $player->getY(), $player->getZ()))->__toString();
                $player->sendMessage("§a> Katılma Noktası kaydedildi kordinatlar : $args[1]  X: " . (string)round($player->getX()) . " Y: " . (string)round($player->getY()) . " Z: " . (string)round($player->getZ()));
                break;
            case "tabela":
                $player->sendMessage("§a» §bTabelayı kır Hemen Ayarlansın");
                $this->setupData[$player->getName()] = 0;
                break;
            case "kaydet":
                if(!$arena->level instanceof Level) {
                    $player->sendMessage("§c» §aBulamadım Haritayı");
                    if($arena->setup) {
                        $player->sendMessage("§c» §6Başka Zaman Denemelisin Dostum");
                    }
                    break;
                }
                $arena->mapReset->saveMap($arena->level);
                $player->sendMessage("§a» §eKaydettim Haberin Ola");
                break;
            case "enable":
                if(!$arena->setup) {
                    $player->sendMessage("§6» §bBurayı Zaten Açmışsın 2. Kez açamıcağını biliyorsun §cBile bilemi yapıyorsun");
                    break;
                }
                if(!$arena->enable()) {
                    $player->sendMessage("§c» Bitmemiş Olan İşlemi nasıl başlatabilirim acaba?");
                    break;
                }
                $player->sendMessage("§7[16:20] PocketMine Açılıyor \n".
              "§aŞaka Şaka Açtım Oyunu");
                break;
            case "bitti":
                $player->sendMessage("§a» §bBeni Bırakıp Gitmee Lütfeen. Keşke Gitmeseydin Ne Güzel Anlaşıyorduk ama");
                unset($this->setters[$player->getName()]);
                if(isset($this->setupData[$player->getName()])) {
                    unset($this->setupData[$player->getName()]);
                }
                break;
            default:
                $player->sendMessage("§6» §bTek Kurşunda 2 Kuş Yok Öyle Tek Kurşun Tek Arena Yapabilirsin. Başka Arenaya Gidemezsin\n".
                    "§7- Bütün Komutlar Listesini Görmek İstiyorsan §2§lyardim§f§7 Yazmalısın Dostum\n"  .
                    "§7-  §b§lbitti§f§7 Yazarak işlemi bitirebilirsin");
                break;
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if(isset($this->setupData[$player->getName()])) {
            switch ($this->setupData[$player->getName()]) {
                case 0:
                    $this->setters[$player->getName()]->data["joinsign"] = [(new Vector3($block->getX(), $block->getY(), $block->getZ()))->__toString(), $block->getLevel()->getFolderName()];
                    $player->sendMessage("§a» §aTabelayı Ayarladın helal olsun sana");
                    unset($this->setupData[$player->getName()]);
                    $event->setCancelled(\true);
                    break;
            }
        }
    }
}