<?php
use Adminko\Db\Db;

include_once dirname(dirname(__FILE__)) . '/config/config.php';

// Каталог
$catalogue_list = Db::selectAll('select * from kor9f_jshopping_categories order by category_parent_id, ordering');

$catalogue_id = 1;
$catalogue_map = array(); $catalogue_parent_map = array();
$catalogue_image_url = 'http://ganteli-shtangi.ru/components/com_jshopping/files/img_categories/';
$catalogue_image_path = '../upload/catalogue/'; $catalogue_image_alias = '/upload/catalogue/';

Db::delete('catalogue');

foreach ($catalogue_list as $catalogue_item) {
    $catalogue_parent = $catalogue_item['category_parent_id'] ?
        $catalogue_map[$catalogue_item['category_parent_id']] : 0;
    @$catalogue_parent_map[$catalogue_parent]++;
    
    if (!file_exists($catalogue_image_path . $catalogue_item['category_image'])) {
        @$catalogue_image_data = file_get_contents($catalogue_image_url . $catalogue_item['category_image']);
        file_put_contents($catalogue_image_path . $catalogue_item['category_image'], $catalogue_image_data);
    }
    
    Db::insert('catalogue', array(
        'catalogue_id' => $catalogue_id,
        'catalogue_parent' => $catalogue_parent,
        'catalogue_title' => $catalogue_item['name_ru-RU'],
        'catalogue_name' => str_replace('-', '_', $catalogue_item['alias_ru-RU']),
        'catalogue_image' => $catalogue_image_alias . $catalogue_item['category_image'],
        'catalogue_order' => $catalogue_parent_map[$catalogue_parent],
        'catalogue_active' => $catalogue_item['category_publish']
    ));
    
    $catalogue_map[$catalogue_item['category_id']] = $catalogue_id++;
}


// Бренды
$brand_list = Db::selectAll('select * from kor9f_jshopping_manufacturers order by ordering');

$brand_id = 1;
$brand_map = array();

Db::delete('brand');

foreach ($brand_list as $brand_item) {
    Db::insert('brand', array(
        'brand_id' => $brand_id,
        'brand_title' => $brand_item['name_ru-RU'],
    ));
    
    $brand_map[$brand_item['manufacturer_id']] = $brand_id++;
}


// Товары
$product_list = Db::selectAll('select * from kor9f_jshopping_products inner join kor9f_jshopping_products_to_categories using(product_id) order by category_id, product_ordering');

$product_id = 1;
$product_map = array(); $catalogue_product_map = array();

Db::delete('product');

foreach ($product_list as $product_item) {
    $product_catalogue = $catalogue_map[$product_item['category_id']];
    $product_brand = isset($brand_map[$product_item['product_manufacturer_id']]) ?
        $brand_map[$product_item['product_manufacturer_id']] : 0;
    @$catalogue_product_map[$product_catalogue]++;
    
    Db::insert('product', array(
        'product_id' => $product_id,
        'product_catalogue' => $product_catalogue,
        'product_brand' => $product_brand,
        'product_title' => $product_item['name_ru-RU'],
        'product_description' => $product_item['description_ru-RU'],
        'product_price' => $product_item['product_price'],
        'product_price_old' => 0,
        'product_stock' => $product_item['product_publish'],
        'product_rating' => 0,
        'product_voters' => 0,
        'product_order' => $catalogue_product_map[$product_catalogue],
        'product_active' => $product_item['product_publish'],
    ));
    
    $product_map[$product_item['product_id']] = $product_id++;
}


// Картинки
$picture_list = Db::selectAll('select * from kor9f_jshopping_products_images
    where image_name not like \'ganteli__shtangi__diski%\' and
          image_name not like \'garantija_luchshej_tseny%\' and
          image_name not like \'uznajte_tsenu%\'
    order by product_id, ordering');

$product_picture_map = array();
$product_image_url = 'http://ganteli-shtangi.ru/components/com_jshopping/files/img_products/';
$product_image_path = '../upload/product/'; $product_image_alias = '/upload/product/';

Db::delete('product_picture');

foreach ($picture_list as $picture_item) {
    $product_id = $product_map[$picture_item['product_id']];
    @$product_picture_map[$product_id]++;
    
    if (!file_exists($product_image_path . $picture_item['image_name'])) {
        $product_image_data = file_get_contents($product_image_url . 'full_' . $picture_item['image_name']);
        file_put_contents($product_image_path . $picture_item['image_name'], $product_image_data);
    }
    
    Db::insert('product_picture', array(
        'picture_product' => $product_id,
        'picture_image' => $product_image_alias . $picture_item['image_name'],
        'picture_order' => $product_picture_map[$product_id]
    ));
}
