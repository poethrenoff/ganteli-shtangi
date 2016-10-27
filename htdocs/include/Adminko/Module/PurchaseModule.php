<?php
namespace Adminko\Module;

use Adminko\Cart;
use Adminko\Date;
use Adminko\Mail;
use Adminko\View;
use Adminko\System;
use Adminko\Session;
use Adminko\Model\Model;
use Adminko\Module\Module;
use Adminko\Valid\Valid;

class PurchaseModule extends Module
{
    /**
     * Корзина
     */
    protected $cart = null;
    
    /**
     * Оформление заказа
     */
    protected function actionIndex()
    {
        if (session::flash('purchase_complete')) {
            $this->content = $this->view->fetch('module/purchase/complete');
        } else {
            $this->cart = Cart::factory();
            
            $error = !empty($_POST) && $this->cart->getQuantity() ? $this->addPurchase() : array();
            
            $this->view->assign('error', $error);
            $this->view->assign('cart', $this->cart);
            $this->content = $this->view->fetch('module/purchase/form');
        }
    }
    
    /**
     * Создание заказа
     */
    protected function addPurchase()
    {
        $error = array();
        
        $field_list = array(
            'purchase_person', 'purchase_email', 'purchase_phone',
            'purchase_address', 'purchase_comment');
        foreach ($field_list as $field_name) {
            $$field_name = trim(init_string($field_name));
        }
        
        $field_list = array(
            'purchase_person', 'purchase_email', 'purchase_phone', 'purchase_address');
        foreach ($field_list as $field_name) {
            if (is_empty($$field_name)) {
                $error[$field_name] = 'Поле обязательно для заполнения';
            }
        }

        if (!isset($error['purchase_email']) && !Valid::factory('email')->check($purchase_email)) {
            $error['purchase_email'] = 'Поле заполнено некорректно';
        }
                
        if (count($error)) {
            return $error;
        }
        
        $purchase_sum = $this->cart->getSum();
        
        // Сохранение заказа
        $purchase = Model::factory('purchase')
            ->setPurchasePerson($purchase_person)
            ->setPurchaseEmail($purchase_email)
            ->setPurchasePhone($purchase_phone)
            ->setPurchaseAddress($purchase_address)
            ->setPurchaseComment($purchase_comment)
            ->setPurchaseDate(Date::now())
            ->setPurchaseSum($purchase_sum)
            ->setPurchaseStatus('new')
            ->save();
        
        // Сохранение позиций заказа
        foreach($this->cart->get() as $item) {
            Model::factory('purchase_item')
                ->setItemPurchase($purchase->getId())
                ->setItemProduct($item->id)
                ->setItemPrice($item->price)
                ->setItemQuantity($item->quantity)
                ->save();
        }
        
        // Отправка сообщения
        $from_email = get_preference('from_email');
        $from_name = get_preference('from_name');
        
        $client_subject = get_preference('client_subject');
        
        $manager_email = get_preference('manager_email');
        $manager_subject = get_preference('manager_subject');
        
        $purchase_view = new View();
        $purchase_view->assign('purchase', $purchase);
        
        $client_letter = $purchase_view->fetch('module/purchase/client_letter');
        $manager_letter = $purchase_view->fetch('module/purchase/manager_letter');
        
        Mail::send($purchase_email, $from_email, $from_name, $client_subject, $client_letter);
        Mail::send($manager_email, $from_email, $from_name, $manager_subject, $manager_letter);
        
        Session::flash('purchase_complete', true);
        
        $this->cart->clear();
        
        System::redirectBack();
    }
    
    // Отключаем кеширование
    protected function getCacheKey()
    {
        return false;
    }
}