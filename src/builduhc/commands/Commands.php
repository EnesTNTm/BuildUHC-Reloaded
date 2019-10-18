<?php

namespace builduhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use builduhc\arena\Arena;
use builduhc\BuildUHC;

class Commands extends Command implements PluginIdentifiableCommand {

    protected $plugin;

    public function __construct(BuildUHC $plugin) {
        $this->plugin = $plugin;
        parent::__construct("nowalegacystyle", "NowaLegacy Menüleri", \null, ["ngts"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission("buhc.cmd")) {
            $sender->sendMessage("§cilk önce izin al lütfen");
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage("§ckullanım: §7/buhc yardim");
            return;
        }

        switch ($args[0]) {
            case "yardim":
                if(!$sender->hasPermission("buhc.cmd.help")) {
                    $sender->sendMessage("§cilk önce izin al lütfen");
                    break;
                }
                $sender->sendMessage("§a--- §bBuildUHC §a---\n" .
                    "§8»§7/buhc yardim : §3Yardım Listesidir\n".
                    "§8»§7/buhc olustur : §3BuildUHC Arenası Oluştur\n".
                    "§8»§7/buhc sil : §3BuildUHC seçtiğin arenayı dünyanın öbür ucuna götürür\n".
                    "§8»§7/buhc düzenle : §3Arenayı Düzenler\n".
                    "§8»§7/buhc arenalar : §3Arenaları Gösterir");

                break;
            case "olustur":
                if(!$sender->hasPermission("buhc.cmd.create")) {
                    $sender->sendMessage("§cizin alırmısıın");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("§cKullanım: §7/buhc olustur <arenaismi>");
                    break;
                }
                if(isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§c> $args[1] Lütfen Farklı Bir İsim Dene bu Zaten var");
                    break;
                }
                $this->plugin->arenas[$args[1]] = new Arena($this->plugin, []);
                $sender->sendMessage("§a> $args[1] Oluştursammı Hmm. Tamam Oluşturdum");
                break;
            case "sil":
                if(!$sender->hasPermission("buhc.cmd.remove")) {
                    $sender->sendMessage("§cizin almalısın!");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("§cKullanım: §7/buhc sil <arenaismi>");
                    break;
                }
                if(!isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§c> $args[1] Böyle bir arena yok neyin kafası bu");
                    break;
                }

                /** @var Arena $arena */
                $arena = $this->plugin->arenas[$args[1]];

                foreach ($arena->players as $player) {
                    $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
                }

                if(is_file($file = $this->plugin->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $args[1] . ".yml")) unlink($file);
                unset($this->plugin->arenas[$args[1]]);

                $sender->sendMessage("§a%1 %5 %31 %40 %63 %69 %80 %99 %99.50 %99.89 %99.99 %100 §bTamam Sildim Agam");
                break;
            case "duzenle":
                if(!$sender->hasPermission("buhc.cmd.set")) {
                    $sender->sendMessage("§cİzin al önce!");
                    break;
                }
                if(!$sender instanceof Player) {
                    $sender->sendMessage("§cConsolede Yazmıcaksın");
                    break;
                }
                if(!isset($args[1])) {
                    $sender->sendMessage("§cKullanım: §7/buhc duzenle <arenaismi>");
                    break;
                }
                if(isset($this->plugin->setters[$sender->getName()])) {
                    $sender->sendMessage("§cDüzenlerken Başka Düzenleme");
                    break;
                }
                if(!isset($this->plugin->arenas[$args[1]])) {
                    $sender->sendMessage("§c $args[1] Bulamıyorum");
                    break;
                }
                $sender->sendMessage("§aKuruluma Başarı İle Katıldın.\n".
                    "§7-  §b§lyardim§r§3 Yazarak Komut listesini öğren\n"  .
                    "§7- §3İşlemin Bitince §b§lbitti§f§3 Yazıcaksın");
                $this->plugin->setters[$sender->getName()] = $this->plugin->arenas[$args[1]];
                break;
            case "arenalar":
                if(!$sender->hasPermission("buhc.cmd.arenas")) {
                    $sender->sendMessage("§cizin al önce!");
                    break;
                }
                if(count($this->plugin->arenas) === 0) {
                    $sender->sendMessage("§6Söyle Bana Arena Yokken Nasıl Görebilirsin? .");
                    break;
                }
                $list = "§7 Arenalar:\n";
                foreach ($this->plugin->arenas as $name => $arena) {
                    if($arena->setup) {
                        $list .= "§7- $name : §cDe-Aktif\n";
                    }
                    else {
                        $list .= "§7- $name : §aAktif\n";
                    }
                }
                $sender->sendMessage($list);
                break;
        }

    }

    public function getPlugin(): Plugin {
        return $this->plugin;
    }

}
