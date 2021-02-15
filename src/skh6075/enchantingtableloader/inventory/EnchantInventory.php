<?php

namespace skh6075\enchantingtableloader\inventory;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\ContainerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use skh6075\enchantingtableloader\block\EnchantingTable;
use skh6075\enchantingtableloader\EnchantingTableLoader;

class EnchantInventory extends ContainerInventory{

    public function getName(): string{
        return "EnchantInventory";
    }

    public function getDefaultSize(): int{
        return 2;
    }

    public function getNetworkType(): int{
        return WindowTypes::ENCHANTMENT;
    }

    /**
     * @param Player $who
     * @param InventoryTransactionPacket $packet
     */
    public function onInventoryListener(Player $who, InventoryTransactionPacket $packet): void{
        foreach ($packet->actions as $action) {
            if ($action->sourceType !== NetworkInventoryAction::SOURCE_CONTAINER)
                continue;

            $newSlot = $action->inventorySlot - 14;
            $slots = [14, 15]; // [Tool, Liquid]

            $ev = new InventoryTransactionEvent(new InventoryTransaction($who, [new SlotChangeAction($this, $newSlot, $action->oldItem, $action->newItem)]));
            $ev->call();

            if ($action->windowId === ContainerIds::UI and in_array($action->inventorySlot, $slots)) {
                $this->setItem($newSlot, $ev->isCancelled() ? $action->oldItem : $action->newItem);
            } else {
                if (($inventory = $who->getWindow($action->windowId)) instanceof EnchantInventory)
                    $inventory->setItem($action->inventorySlot, $ev->isCancelled() ? $action->oldItem : $action->newItem);
            }
        }
    }

    /**
     * @param Player $who
     * @param PlayerActionPacket $packet
     */
    public function onEnchantTradeResult(Player $who, PlayerActionPacket $packet): void{
        if ($packet->action !== PlayerActionPacket::ACTION_SET_ENCHANTMENT_SEED)
            return;
        if (!($inventory = EnchantingTableLoader::getInventoryProcess($who)) instanceof EnchantInventory)
            return;
        $item = $inventory->getItem(0);
        $who->getInventory()->addItem(clone $item);
    }
}