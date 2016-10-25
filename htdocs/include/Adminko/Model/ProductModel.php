<?php
namespace Adminko\Model;

use Adminko\System;
use Adminko\Metadata;
use Adminko\Db\Db;

class ProductModel extends Model
{
    // Возвращает каталог товара
    public function getCatalogue()
    {
        return Model::factory('catalogue')->get($this->getProductCatalogue());
    }

    // Возвращает URL товара
    public function getProductUrl()
    {
        return System::urlFor(array('controller' => 'product',
            'catalogue' => $this->getCatalogue()->getCatalogueName(),
            'action' => 'item', 'id' => $this->getProductId()));
    }
    
    // Возвращает список маркеров товара
    public function getMarkerList()
    {
        return Model::factory('marker')->getByProduct($this);
    }
    
    // Возвращает бренд товара
    public function getBrand()
    {
        if ($this->getProductBrand()) {
            return Model::factory('brand')->get($this->getProductBrand());
        } else {
            return false;
        }
    }
    
    // Возвращает список товаров по маркеру
    public function getByMarker($marker, $limit = 4)
    {
        $records = db::selectAll('
            select product.* from product
                inner join catalogue on product_catalogue = catalogue_id
                inner join product_marker using(product_id)
            where marker_id = :marker_id and
                product_active = :product_active and catalogue_active = :catalogue_active
            order by rand() limit ' . $limit,
            array('marker_id' => $marker->getId(), 'product_active' => 1, 'catalogue_active' => 1));
        
        return $this->getBatch($records);
    }
    
    // Возвращает изображения товара
    public function getPictureList()
    {
        return Model::factory('product_picture')->getList(
            array('picture_product' => $this->getId()), array('picture_order' => 'asc')
        );
    }
        
    // Возвращает изображение по умолчанию
    public function getProductImage()
    {
        $picture_list = Model::factory('product_picture')->getList(
            array('picture_product' => $this->getId()), array('picture_order' => 'asc'), 1
        );
        if (empty($picture_list)) {
            return get_preference('default_image');
        }
        $default_image = current($picture_list);
        return $default_image->getPictureImage();
    }
    
    // Поисковый запрос
    public function getSearchResult($search_value)
    {
        $search_words = preg_split('/\s+/', $search_value);
            
        $filter_clause = array();
        foreach (array('product_article', 'product_title', 'product_description') as $field_name) {
            $field_filter_clause = array();
            foreach ($search_words as $search_index => $search_word) {
                $field_prefix = $field_name . '_' . $search_index;
                $field_filter_clause[] = 'lower(' . $field_name . ') like :' . $field_prefix;
                $filter_binds[$field_prefix] = '%' . mb_strtolower($search_word , 'utf-8') . '%';
            }
            $filter_clause[] = join(' and ', $field_filter_clause);
        }
        
        $records = Db::selectAll('
            select
                product.*
            from
                product
                inner join catalogue on product.product_catalogue = catalogue.catalogue_id
            where (' . join(' or ', $filter_clause) . ') and
                product_active = :product_active and catalogue_active = :catalogue_active
            order by
                product_order asc',
            $filter_binds + array('product_active' => 1, 'catalogue_active' => 1)
        );
        
        return $this->getBatch($records);
    }
    
    // Добавляет оценку товару
    public function addMark($mark)
    {
        $voters = $this->getProductVoters();
        $rating = $this->getProductRating();
        
        $this->setProductVoters($voters + 1);
        $this->setProductRating(($rating * $voters + $mark) / ($voters + 1));
        
        return $this;
    }
}
