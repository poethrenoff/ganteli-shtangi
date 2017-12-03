<?php
namespace Adminko\Module;

use Adminko\View;
use Adminko\System;
use Adminko\Paginator;
use Adminko\Model\Model;

class SaleModule extends Module
{
    // Вывод списка товаров
    protected function actionIndex()
    {
        $product_list = Model::factory('product')->getList(
            array('product_active' => 1, 'product_sale' => 1), array('product_order' => 'asc')
        );

        $this->view->assign('product_list', $product_list);
        $this->content = $this->view->fetch('module/product/sale');
    }
}
