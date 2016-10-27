<?php
namespace Adminko\Module;

use Adminko\Paginator;
use Adminko\Model\Model;

class SearchModule extends Module
{
    protected function actionIndex()
    {
        $search_value = trim(init_string('text'));
        
        if (!is_empty($search_value)) {
            $product_list = Model::factory('product')->getSearchResult($search_value);
            
            $pages = Paginator::create(count($product_list), array('by_page' => 20));
            $product_list = array_slice($product_list, $pages['offset'], 20);
            
            $this->view->assign('total', $pages['count']);
            $this->view->assign('product_list', $product_list);
            $this->view->assign('pages', Paginator::fetch($pages));
        }
        
        $this->content = $this->view->fetch('module/search/index');
    }
    
    protected function actionForm()
    {
        $this->content = $this->view->fetch('module/search/form');
    }
}
