<?php

namespace skh6075\enchantingtableloader;

use pocketmine\block\BlockFactory;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use skh6075\enchantingtableloader\block\EnchantingTable;
use skh6075\enchantingtableloader\inventory\EnchantInventory;

class EnchantingTableLoader extends PluginBase implements Listener{
    use SingletonTrait;

    /** @var EnchantInventory[] */
    private static array $inventory = [];


    public function onLoad(): void{
        self::setInstance($this);
        BlockFactory::registerBlock(new EnchantingTable(), true);
    }

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public static function setInventoryProcess(Player $player, EnchantInventory $inventory): void{
        self::$inventory[$player->getName()] = $inventory;
    }

    public static function removeInventoryProcess(Player $player): void{
        if (isset(self::$inventory[$player->getName()])) {
            unset(self::$inventory[$player->getName()]);
        }
    }

    public static function getInventoryProcess(Player $player): ?EnchantInventory{
        return self::$inventory[$player->getName()] ?? null;
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void{
        $pk = $event->getPacket();
        $player = $event->getPlayer();

        if (!($inventory = self::getInventoryProcess($player)) instanceof EnchantInventory)
            return;

        if ($pk instanceof ActorEventPacket) {
            if ($pk->event === ActorEventPacket::PLAYER_ADD_XP_LEVELS) {
                $player->addXpLevels($pk->data);
            }
        } else if ($pk instanceof InventoryTransactionPacket) {
            $inventory->onInventoryListener($player, $pk);
        } else if ($pk instanceof PlayerActionPacket) {
            $inventory->onEnchantTradeResult($player, $pk);
        }
    }
}