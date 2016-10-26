<?php
namespace Adminko\Module;

use Adminko\View;
use Adminko\System;
use Adminko\Model\Model;

class ProductModule extends Module
{
    // Вывод списка товаров
    protected function actionIndex()
    {
        $catalogue_name = System::getParam('catalogue');
        $catalogue = $this->getCatalogue($catalogue_name);
        
        $catalogue_list = $catalogue->getCatalogueList();
        $product_list = $catalogue->getProductList();
        
        if (count($catalogue_list)) {
            $this->view->assign('catalogue', $catalogue);
            $this->view->assign('catalogue_list', $catalogue_list);
            $this->content = $this->view->fetch('module/product/catalogue');
        } else {
            $this->view->assign('catalogue', $catalogue);
            $this->view->assign('product_list', $product_list);
            $this->content = $this->view->fetch('module/product/product');
        }
    }

    protected function actionMenu()
    {
        $catalogue_list = Model::factory('catalogue')->getList(
            array('catalogue_active' => 1), array('catalogue_order' => 'asc')
        );
        
        $catalogue_name = System::getParam('catalogue');
        
        if ($catalogue_name) {
            $catalogue = $this->getCatalogue($catalogue_name);
            $selected_list = array($catalogue->getId());
            
            while ($catalogue->getCatalogueParent()) {
                $catalogue_parent = Model::factory('catalogue')->get(
                    $catalogue->getCatalogueParent()
                );
                $selected_list[] = $catalogue_parent->getId();
                $catalogue = $catalogue_parent;
            }
            foreach ($catalogue_list as $catalogue_index => $catalogue_item) {
                if (in_array($catalogue_item->getId(), $selected_list)) {
                    $catalogue_list[$catalogue_index]->setSelected(true);
                }
            }
        }
        
        $catalogue_tree = Model::factory('catalogue')->getTree($catalogue_list);

        $this->view->assign($catalogue_tree);
        $this->content = $this->view->fetch('module/product/menu');
    }

    protected function actionItem()
    {
        $product = $this->getProduct(System::id());
        
        $this->view->assign($product);
        $this->content = $this->view->fetch('module/product/item');
    }
    
    protected function actionVote()
    {
        $product = $this->getProduct(System::id());
        $product->addMark(min(5, max(1, init_string('mark'))))->save();
        
        $this->content = json_encode(array(
            'id' => $product->getId(),
            'rating' => $product->getProductRating(),
            'count' => $product->getProductVoters()
        ));
    }
    
    protected function actionMarker()
    {
		foreach (array('discount', 'leader', 'novelty') as $marker_name) {
			$marker = Model::factory('marker')->getByName($marker_name);
			$product_list = Model::factory('product')->getByMarker($marker, 4);
			
			$marker_view = new View();
			$marker_view->assign('marker', $marker);
			$marker_view->assign('product_list', $product_list);
			
			$this->content .= $marker_view->fetch('module/product/marker');
		}        
    }
    
    protected function actionMarkerList()
    {
        $marker_name = System::getParam('marker');
        $marker = Model::factory('marker')->getByName($marker_name);
		$product_list = Model::factory('product')->getByMarker($marker);
		
        $this->view->assign('marker', $marker);
		$this->view->assign('product_list', $product_list);
        $this->content = $this->view->fetch('module/product/marker');
    }

    /**
     * Получение товара
     */
    public function getProduct($id)
    {
        try {
            $product = Model::factory('product')->get($id);
        } catch (\AlarmException $e) {
            System::notFound();
        }
        if (!$product->getProductActive()) {
            System::notFound();
        }
        return $product;
    }
    
    /**
     * Получение каталога
     */
    public function getCatalogue($catalogue_name)
    {
        try {
            $catalogue = Model::factory('catalogue')->getByName($catalogue_name);
        } catch (\AlarmException $e) {
            System::notFound();
        }
        if (!$catalogue->getCatalogueActive()) {
            System::notFound();
        }
        return $catalogue;
    }
}
