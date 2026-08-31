<?php

namespace Krabo\SnelstartBundle\Cron;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;
use Krabo\SnelstartBundle\Helper\IsotopeOrderSync;
use Symfony\Component\HttpFoundation\Response;

class Cron {

    /**
     * @var IsotopeOrderSync
     */
    private $orderSync;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $limit;

    public function __construct(Connection $connection, IsotopeOrderSync $orderSync, $limit)
    {
        $this->orderSync = $orderSync;
        $this->connection = $connection;
        $this->limit = $limit;
    }

    public function sync() {
        \System::log('Snelstart Cron', __METHOD__, TL_GENERAL);
        $stmnt = $this->connection->prepare("SELECT `id` FROM `tl_iso_product_collection` WHERE `type` = 'order' AND `locked` IS NOT NULL AND `snelstart_sync_date` IS NULL AND `document_number` <> '' ORDER BY `id` LIMIT 0, ?");
        $stmnt->bindValue(1, $this->limit, ParameterType::INTEGER);
        $orders = $stmnt->executeQuery();
        $orders = $orders->fetchAllAssociative();
        foreach($orders as $order) {
            $order_id = $order['id'];
            \System::log('Snelstart Sync Order: '.$order_id, __METHOD__, TL_GENERAL);
            $order = Order::findOneBy('id', $order_id);
            if ($order) {
                $this->orderSync->syncOrder($order);
            }
        }
    }

}
