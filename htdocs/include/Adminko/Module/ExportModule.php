<?php
namespace Adminko\Module;

use Adminko\Model\Model;

class ExportModule extends Module
{
    protected function actionIndex()
    {
        $catalogue_list = Model::factory('catalogue')->getList(
            array('catalogue_active' => 1), array('catalogue_id' => 'asc')
        );
        $product_list = Model::factory('product')->getList(
            array('product_active' => 1, 'product_stock' => 1), array('product_id' => 'asc')
        );

        header( 'Content-type: text/xml; charset: UTF-8' );

        $this->view->assign('catalogue_list', $catalogue_list);
        $this->view->assign('product_list', $product_list);
        $this->content = $this->view->fetch('module/export/export');
    }
}