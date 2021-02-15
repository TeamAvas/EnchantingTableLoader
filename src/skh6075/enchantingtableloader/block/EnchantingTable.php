<?php

namespace skh6075\enchantingtableloader\block;

use pocketmine\block\EnchantingTable as PMEnchantingTable;
use pocketmine\item\Item;
use pocketmine\Player;
use skh6075\enchantingtableloader\EnchantingTableLoader;
use skh6075\enchantingtableloader\inventory\EnchantInventory;

class EnchantingTable extends PMEnchantingTable{

    public function onActivate(Item $item, Player $player = null): bool{
        if ($player instanceof Player) {
            EnchantingTableLoader::setInventoryProcess($player, $inventory = new EnchantInventory($this));
            $player->addWindow($inventory);
        }
        return true;
    }
}