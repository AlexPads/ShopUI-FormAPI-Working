<?php

declare(strict_types=1);

namespace SaltyPixelDevz\CustomShopUIv2;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Server;
use pocketmine\utils\Config;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat as TF;
use jojoe77777\FormAPI\CustomForm;
use JackMD\ConfigUpdater\ConfigUpdater;

class gui extends PluginBase
{

    public $msg;
    // For Config Updates
    private const CONFIG_VERSION = 1;

    // For shop.yml Updates! (Changes more xD)
    private const SHOP_VERSION = 1;

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        $this->saveResource("shop.yml");
        $this->checkConfigs();
    }

    // Thanks JackMD!
    public function checkConfigs(): void{
        $cfg2 = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        ConfigUpdater::checkUpdate($this, $cfg2, "shop-version", self::SHOP_VERSION);
        ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION);

    }
    // Thanks JackMD!

    // For Commands Test Survival
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ("shop") {
            case "shop":
                if ($sender instanceof Player) {
                    if ($sender->getGamemode() != 0 and $this->getConfig()->get("Survival") === true) {
                        $msg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
                        $sender->sendMessage($msg->getNested("messages.Survival"));
                        return true;
                    } else {
                        $cfg = yaml_parse_file($this->getDataFolder() . "shop.yml");
                        $msg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
                        $this->Category($cfg, $sender, $msg);
                        return true;
                    }
                }
                $sender->sendMessage(TF::RED . "Please use this in-game.");
                break;
        }
        return true;
    }
    // For Commands Test Survival

    // For Categories
    public function Category($cfg, Player $player, Config $msg): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) use ($cfg, $msg) : void {
            if ($data == 0) {
                $player->sendMessage($msg->getNested("Messages.Thanks2"));
            }else {
                if ($this->getConfig()->get("Category_ExitButton") == true) {
                    $categorys = $data - 1;
                    $this->Items($player, $categorys, $cfg);
                } else {
                    $categorys = $data;
                    $this->Items($player, $categorys, $cfg);
                }
            }
        });
        $form->setTitle($msg->getNested("Titles.Category"));
        if ($this->getConfig()->get("Category_ExitButton") == true) {
            $form->addButton($msg->getNested("Messages.Category_ExitButton"));
        }
        foreach ($cfg as $cate => $category) {
            if ($category == self::SHOP_VERSION){
            }else {
                $list = explode(":", $category["Name"]);
                if (substr($list[1], 0, 4) == "http") {
                    $form->addButton($list[0], 1, "https:" . $list[2]);
                } else {
                    $form->addButton($list[0], 0, $list[1]);
                }
            }
        }
        $form->sendToPlayer($player);
    }
    // For Categories

    // For Items
    public function Items(Player $player, $categorys, $cfg)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) use ($cfg, $categorys) : void {
            if ($data == 0) {
                $msg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
                $this->Category($cfg, $player, $msg);
            } else {
                $items = $cfg[$categorys];
                foreach ($items["Items"] as $cate => $item) {
                    $list = explode(":", $item);
                }
                if ($list[0] == "cmd") {
                    $command = $data - 1;
                    $this->Command($player, $cfg, $categorys, $command);
                } elseif (($data != 0) && ($list[0] != "cmd")) {
                    $item = $data - 1;
                    $this->Confirmation($player, $cfg, $categorys, $item);
                }
            }
        });
        $msg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $form->setTitle($msg->getNested("Titles.Items"));
        $form->addButton($msg->getNested("Messages.ExitButton"));
        if (($categorys === null) && ($this->getConfig()->get("Thanks") == true)) {
            $player->sendMessage($msg->getNested("Messages.Thanks"));
        } else {
            $items = $cfg[$categorys];
            foreach ($items["Items"] as $cate => $item) {
                $list = explode(":", $item);
                $name = Item::get((int)$list[0], 0, 1)->getName();
                if ($list[0] == "cmd") {
                    if (substr($list[5], 0, 4) == "http") {
                        $form->addButton($list[1] . " " . $list[2] . $msg->getNested("Messages.Each"), 1, $list[5] . ":" . $list[6]);
                    } else {
                        $form->addButton($list[1], 0, $list[5]);
                    }
                } else {
                    if (substr($list[6], 0, 4) == "http") {
                        $form->addButton($name . " " . $list[3] . $msg->getNested("Messages.Each"), 1, $list[6] . ":" . $list[7]);
                    } else {
                        $form->addButton($name . " " . $list[3] . $msg->getNested("Messages.Each"), 0, $list[6]);
                    }
                }
            }
            $form->sendToPlayer($player);
        }
    }
    // For Items

    // For Commands
    public function Command(Player $player, $cfg, $categorys, $command)
    {
        $items = $cfg[$categorys];
        foreach ($items["Items"] as $cate => $item2) {
            $item1[] = $item2;
        }
        $list = explode(":", $item1[$command]);
        if (EconomyAPI::getInstance()->myMoney($player) > $list[3]) {
            if ($list[3] == "Console") {
                $cmd = str_replace("{player}", $player->getName(), $list[4]);
                Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $cmd);
                EconomyAPI::getInstance()->reduceMoney($player->getName(), $list[3]);
            } elseif ($list[3] == "Player") {
                $cmd = str_replace("{player}", $player->getName(), $list[4]);
                Server::getInstance()->dispatchCommand($player, $cmd);
                EconomyAPI::getInstance()->reduceMoney($player->getName(), $list[3]);
            }
        }
    }
    // For Commands

    // For Confirm Form (LONG BOI)
    public function Confirmation(Player $player, $cfg, $categorys, $item)
    {
        $form = new CustomForm(function (Player $player, $data) use ($cfg, $categorys, $item) {
            if ($data === null) {
                $msg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
                if ($this->getConfig()->get("Straight_Back") == true) {
                    $player->sendMessage($msg->getNested("Messages.Thanks"));
                } elseif ($this->getConfig()->get("Back_to_Start") == true) {
                    $this->Category($cfg, $player, $msg);
                }
            } else {
                $msg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
                $money = EconomyAPI::getInstance()->myMoney($player);
                $items = $cfg[$categorys];
                $message = $msg->getNested("Messages.Information");
                foreach ($items["Items"] as $cate => $item2) {
                    $item1[] = $item2;
                }
                $list = explode(":", $item1[$item]);
                $name = Item::get((int)$list[0], 0, 1)->getName();
                $vars = ["{item}" => $name, "{cost}" => $list[4]];
                foreach ($vars as $var => $replacement) {
                    $message = str_replace($var, $replacement, $message);
                }
                if ($this->getConfig()->getNested("Types.Toggle") == true) {
                    if ($this->getConfig()->getNested("Types.Input") == true) {
                        $data1 = (int)$data[2];
                    }
                    if ($this->getConfig()->getNested("Types.Slider") == true) {
                        $data1 = $data[3];
                    }
                    if ($this->getConfig()->getNested("Types.StepSlider") == true) {
                        $numbers = $this->getConfig()->getNested("Slider_Numbers");
                        $data1 = $numbers[$data[4]];
                    }
                }

                if (($data1 == 0) && ($this->getConfig()->get("Thanks")) === true) {
                    $player->sendMessage($msg->getNested("Messages.Thanks2"));
                } else {
                    if ($data[1] == false) {
                        if ($money >= $list[3] * $data1) {
                            $item = $player->getInventory();
                            if ($list[5] != "Default") {
                                $item->addItem(Item::get((int)$list[0], (int)$list[1], $data1)->setCustomName($data[5]));
                            } elseif ($list[5] == "Default") {
                                $item->addItem(Item::get((int)$list[0], (int)$list[1], $data1));
                            }
                            EconomyAPI::getInstance()->reduceMoney($player, $list[3] * $data1);
                            $message = $msg->getNested("Messages.Paid_for");
                            $vars = ["{amount}" => $data1, "{item}" => $name, "{cost}" => $list[3] * $data1];
                            foreach ($vars as $var => $replacement) {
                                $message = str_replace($var, $replacement, $message);
                            }
                            $player->sendMessage($message);
                        } else {
                            $message = $msg->getNested("Messages.Not_enough_money");
                            $tags = ["{amount}" => $data1, "{item}" => $name, "{cost}" => $list[3] * $data1, "{missing}" => $list[3] * $data1 - $money];
                            foreach ($tags as $tag => $replacement) {
                                $message = str_replace($tag, $replacement, $message);
                            }
                            $player->sendMessage($message);
                        }
                    }


                    if ($data[1] == true) {
                        if ($player->getInventory()->contains(Item::get((int)$list[0], (int)$list[1], $data1)) === true) {
                            $player->getInventory()->removeItem(Item::get((int)$list[0], (int)$list[1], $data1));
                            EconomyAPI::getInstance()->addMoney($player, $list[4] * $data1);
                            $message = $msg->getNested("Messages.Paid");
                            $vars = ["{amount}" => $data1, "{item}" => $list[5], "{pay}" => $list[4] * $data1];
                            foreach ($vars as $var => $replacement) {
                                $message = str_replace($var, $replacement, $message);
                            }
                            $player->sendMessage($message);
                        } else {
                            $message = $msg->getNested("Messages.Not_enough_items");
                            $tags = ["{amount}" => $data1, "{name}" => $list[5], "{pay}" => $list[4] * $data1];
                            foreach ($tags as $tag => $replacement) {
                                $message = str_replace($tag, $replacement, $message);
                            }
                            $player->sendMessage($message);
                        }
                    }
                }
            }
        });


        $msg = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $items = $cfg[$categorys];
        $message = $msg->getNested("Messages.Information");
        foreach ($items["Items"] as $cate => $item2) {
            $item1[] = $item2;
        }
        $list = explode(":", $item1[$item]);
        $vars = ["{item}" => $list[5], "{cost}" => $list[4]];
        foreach ($vars as $var => $replacement) {
            $message = str_replace($var, $replacement, $message);
        }
        $form->setTitle($msg->getNested("Titles.Amount"));
        $form->addLabel($message);
        if ($this->getConfig()->getNested("Types.Toggle") == true) {
            $form->addToggle($msg->getNested("Messages.BuySell"));
        }
        if ($this->getConfig()->getNested("Types.Input") == true) {
            $form->addInput($msg->getNested("Messages.Input"));
        }
        if ($this->getConfig()->getNested("Types.Slider") == true) {
            $form->addSlider("Amount", $this->getConfig()->getNested("Types.Slider_Minimum"), $this->getConfig()->getNested("Types.Slider_Maximum"));
        }
        if ($this->getConfig()->getNested("Types.StepSlider") == true) {
            $form->addStepSlider("Amount", $this->getConfig()->getNested("Types.Slider_Numbers"));
        }
        $form->sendToPlayer($player);
    }
    // For Confirm Form (LONG BOI)
}