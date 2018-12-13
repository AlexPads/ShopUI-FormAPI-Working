<?php
#   _______   _                   _____    _                 _   _____                       
#  |__   __| (_)                 |  __ \  (_)               | | |  __ \                      
#     | |     _   _ __    _   _  | |__) |  _  __  __   ___  | | | |  | |   ___  __   __  ____
#     | |    | | | '_ \  | | | | |  ___/  | | \ \/ /  / _ \ | | | |  | |  / _ \ \ \ / / |_  /
#     | |    | | | | | | | |_| | | |      | |  >  <  |  __/ | | | |__| | |  __/  \ V /   / / 
#     |_|    |_| |_| |_|  \__, | |_|      |_| /_/\_\  \___| |_| |_____/   \___|   \_/   /___|
#                          __/ |                                                             
#                         |___/    
#     
namespace AlexPads\ShopUI\TinyPixelDevz;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class Shop extends PluginBase implements Listener{

	 public $items;
	 public $category;
	 public $allshop;
	 public $name;
	 public $result;
	 public $data;
	 public $list;

	 public function onEnable()
	 {
		 $this->saveDefaultConfig();
		 $this->saveResource("shop.yml");
	 }

	 public function onDisable(){
     }

	 public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
	 {
		 switch ($cmd->getName()) {
			 case "shop":
				 if ($sender instanceof Player) {
                     if ($sender->getGamemode() != 0 and $this->getConfig()->get("Survival") === true) {
                         $sender->sendMessage($this->getConfig()->getNested("messages.Survival"));
			 return true;
                     }else{
                         $this->catForm($sender);
                         return true;
                     }
                     if ($this->getConfig()->get("Survival") === false){
                         $this->catForm($sender);
                         return true;
                     }
				 }
				 $sender->sendMessage(TF::RED . "Please use this in-game.");
				 break;
		 }
		 return true;
	 }

	 public function catForm(Player $player): void
	 {
		 $form = new SimpleForm(function (Player $player, int $data = null) {
			 $cat = $data;
			 $this->itemForm($player, $data, $cat);
		 });
		 $form->setTitle($this->getConfig()->get("Title"));
		 $money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player);
		 $allshop = yaml_parse_file($this->getDataFolder() . "shop.yml");
		 foreach ($allshop as $name => $content) {
			 $form->addButton(ucfirst($name));
		 }
		 $form->sendToPlayer($player);
	 }

	 public function itemForm(Player $player, $data, $cat): void
	 {
		 $form = new SimpleForm(function (Player $player, int $data = null) use ($cat) {
			 $result = $data;
			 $this->buysellForm($player, $result, $cat);
		 });
		 $form->setTitle($this->getConfig()->get("Title"));
		 $money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player);
		 $allshop = yaml_parse_file($this->getDataFolder() . "shop.yml");
		 foreach ($allshop as $categoryName => $access) {
			 $category[] = $access;
		 }
		 if ($data === null) {
			 $player->sendmessage($this->getConfig()->getNested("message.thanks") . " " . $this->getConfig()->get("Title"));
		 } else {
			 foreach ($category[$cat] as $items) {
				 $list = explode(":", $items);
				 $form->addButton($list[3] . "  " . "$" . $list[4], $list[6], $list[7]);
			 }
			 $form->sendToPlayer($player);
		 }
	 }
	 public function buysellForm(Player $player, $result, $cat): void
	 {
		 $form = new SimpleForm(function (Player $player, int $data = null) use ($cat, $result) {
		 	$buydata = $data;
			 if ($data === 0) {
			 	$this->amountForm($player, $cat, $result, $buydata);
			 }
			 if ($data === 1) {
			 	$this->amountForm($player, $cat, $result, $buydata);
			 }
			 if ($data === 2) {
			 	$this->catForm($player);
			 }
			 if ($data === null){
			     $this->catForm($player);
             }
		 });
		 $form->setTitle($this->getConfig()->get("Title"));
		 $money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player);
		 $allshop = yaml_parse_file($this->getDataFolder() . "shop.yml");
		 foreach ($allshop as $categoryName => $access) {
			 $category[] = $access;
		 }
		 foreach ($category[$cat] as $items => $itemarray) {
			 $itemlist[] = $itemarray;
		 }
		 if ($result === null) {
			 $this->catForm($player);
		 } else {
			 $list = explode(":", $itemlist[$result]);
			 $message = $this->getConfig()->getNested("messages.money");
			 $form->setContent("$message $money$");
			 $form->addButton("Buy for ". " ". $list[4]. " ". "Each");
			 $form->addButton("Sell for". " ". $list[5]. " ". "Each");
			 $form->addButton(TF::RED . TF::BOLD . "Back");
			 $form->sendToPlayer($player);
		 }
	 }
	public function amountForm(Player $player, $cat, $result, $buydata): void
	{
		$form = new CustomForm(function (Player $player, $data) use ($cat, $result, $buydata) {
			if ($buydata === 0) {
				$allshop = yaml_parse_file($this->getDataFolder() . "shop.yml");
				$money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player);
				foreach ($allshop as $categoryName => $access) {
					$category[] = $access;
				}
				foreach ($category[$cat] as $items => $itemarray) {
					$itemlist[] = $itemarray;
				}
				$list = explode(":", $itemlist[$result]);
				$money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player);
				if ($money >= $list[4]*$data[1]) {
					if ($this->getConfig()->get("Enchants") === false) {
						$player->getInventory()->addItem(Item::get($list[0], $list[1], $data[1])->setCustomName($list[3]));
						$this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->reduceMoney($player, $list[4]*$data[1]);
						$message = $this->getConfig()->getNested("messages.paid-for");
						$vars = ["{amount}" => $list[2], "{item}" => $list[3], "{cost}" => $list[4]*$data[1]];
						foreach ($vars as $var => $replacement) {
							$message = str_replace($var, $replacement, $message);
						}
					} else {
						$enchant = Enchantment::getEnchantment($list[6]);
						$enchants = new EnchantmentInstance($enchant, $list[7]);
						$player->getInventory()->addItem(Item::get($list[0], $list[1], $data[1])->setCustomName($list[3]));
						$this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->reduceMoney($player, $list[4]*$data[1]);
						$message = $this->getConfig()->getNested("messages.paid-for");
						$vars = ["{amount}" => $list[2], "{item}" => $list[3], "{cost}" => $list[4]*$data[1]];
						foreach ($vars as $var => $replacement) {
							$message = str_replace($var, $replacement, $message);
						}
					}
					$player->sendMessage($message);
					$this->buysellForm($player, $result, $cat);
				} else {
					$message = $this->getConfig()->getNested("messages.not-enough-money");
					$tags = ["{amount}" => $list[2], "{name}" => $list[3], "{cost}" => $list[4]*$data[1], "{missing}" => $list[4]*$data[1] - $money];
					foreach ($tags as $tag => $replacement) {
						$message = str_replace($tag, $replacement, $message);
					}
					$player->sendMessage($message);
					$this->buysellForm($player, $result, $cat);
				}
			}
			if ($buydata === 1) {
				$allshop = yaml_parse_file($this->getDataFolder() . "shop.yml");
				$money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player);
				foreach ($allshop as $categoryName => $access) {
					$category[] = $access;
				}
				foreach ($category[$cat] as $items => $itemarray) {
					$itemlist[] = $itemarray;
				}
				$list = explode(":", $itemlist[$result]);
				$money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player);
				if ($data[1] === null){
					$this->catForm($player);
				}else{
				if ($player->getInventory()->contains(Item::get($list[0], $list[1], $data[1])) === true) {
					$player->getInventory()->removeItem(Item::get($list[0], $list[1], $data[1]));
					$this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($player, $list[5]*$data[1]);
					$message = $this->getConfig()->getNested("messages.money-recieved");
					$vars = ["{amount}" => $list[2], "{item}" => $list[3], "{money}" => $list[5]];
					foreach ($vars as $var => $replacement) {
						$message = str_replace($var, $replacement, $message);
					}
					$player->sendMessage($message);
					$this->buysellForm($player, $result, $cat);
				} else {
					$message = $this->getConfig()->getNested("messages.not-enough-items");
					$tags = [
						"{amount}" => $list[2], "{name}" => $list[3], "{money}" => $list[5], "{missing}" => $list[4]*$data[1] - $money];
					foreach ($tags as $tag => $replacement) {
						$message = str_replace($tag, $replacement, $message);
					}
					$player->sendMessage($message);
					$this->buysellForm($player, $result, $cat);
				    }
			    }
			}
			if ($buydata === 2) {
				$this->catForm($player);
			}
			if ($data === null){
				$this->catForm($player);
			}
		});
        $allshop = yaml_parse_file($this->getDataFolder() . "shop.yml");
        foreach ($allshop as $categoryName => $access) {
            $category[] = $access;
        }
        foreach ($category[$cat] as $items => $itemarray) {
            $itemlist[] = $itemarray;
        }
        if ($result === null) {
            $this->catForm($player);
        } else {
            $list = explode(":", $itemlist[$result]);
            $form->setTitle($this->getConfig()->get("Title"));
            $money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player);
            $form->addLabel($this->getConfig()->getNested("messages.how-many") . $list[3]. $this->getConfig()->getNested("messages.how-many2")."\n" . $this->getConfig()->getNested("messages.money") . $money);
            $form->addInput("Amount of Item", $list[2], $list[2]);
            $form->sendToPlayer($player);
        }
	}
 }
