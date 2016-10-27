<?php
namespace Adminko\Model;

use Adminko\Metadata;

class PurchaseModel extends Model
{
    // Возвращает список позиций
    public function getItemList()
    {
        return Model::factory('purchase_item')->getList(
            array('item_purchase' => $this->getId())
        );
    }
}
