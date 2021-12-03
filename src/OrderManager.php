<?php

namespace PSX\Github;

use Doctrine\DBAL\Connection;
use PSX\Framework\Util\Uuid;

class OrderManager
{
    const ORDER_COMPLETED = 1;
    const ORDER_CANCELED = 2;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function create(Order $order)
    {
        $license = Uuid::pseudoRandom();

        $this->connection->insert('apioo_license', [
            'li_status' => self::ORDER_COMPLETED,
            'li_license' => $license,
            'li_name' => $order->getUserName(),
            'li_avatar' => $order->getAvatarUrl(),
            'li_url' => $order->getHtmlUrl(),
            'li_price' => $order->getPrice(),
            'li_insert_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(Order $order)
    {
        $this->connection->update('apioo_license', [
            'li_price' => $order->getPrice(),
        ], [
            'li_name' => $order->getUserName(),
        ]);
    }

    public function cancel(Order $order)
    {
        $this->connection->update('apioo_license', [
            'li_status' => self::ORDER_CANCELED,
        ], [
            'li_name' => $order->getUserName()
        ]);
    }
}
