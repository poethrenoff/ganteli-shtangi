<?php
namespace Adminko\Module;

use Adminko\Mail;
use Adminko\View;
use Adminko\System;
use Adminko\Captcha;
use Adminko\Session;
use Adminko\Valid\Valid;

class SubscribeModule extends Module
{
    public $type_list = array(
        '1' => 'Оптовая', '2' => 'Розничная',
    );
    
    protected function actionIndex()
    {
        if (Session::flash('subscribe_complete')) {
            $this->content = $this->view->fetch('module/subscribe/complete');
        } else {
            $error = !empty($_POST) ? $this->sendRequest() : array();

            $this->view->assign('error', $error);
            $this->view->assign('type_list', $this->type_list);
            $this->content = $this->view->fetch('module/subscribe/form');
        }
    }

    protected function sendRequest()
    {
        $error = array();

        $field_list = array(
            'subscribe_person', 'subscribe_email', 'subscribe_company',
            'subscribe_type', 'subscribe_phone', 'subscribe_captcha');
        foreach ($field_list as $field_name) {
            $$field_name = trim(init_string($field_name));
        }

        if (is_empty($subscribe_person)) {
            $error['subscribe_person'] = 'Не заполнено обязательное поле';
        }
        if (is_empty($subscribe_email)) {
            $error['subscribe_email'] = 'Не заполнено обязательное поле';
        } elseif (!Valid::factory('email')->check($subscribe_email)) {
            $error['subscribe_email'] = 'Поле заполнено некорректно';
        }
        
        if (!is_empty($subscribe_company)) {
            if (is_empty($subscribe_type)) {
                $error['subscribe_type'] = 'Не заполнено обязательное поле';
            } elseif (!in_array($subscribe_type, array_keys($this->type_list))) {
                $error['subscribe_type'] = 'Поле заполнено некорректно';
            }
            if (is_empty($subscribe_phone)) {
                $error['subscribe_phone'] = 'Не заполнено обязательное поле';
            }
        }
        if (is_empty($subscribe_captcha)) {
            $error['subscribe_captcha'] = 'Не заполнено обязательное поле';
        } elseif (!Captcha::check($subscribe_captcha)) {
            $error['subscribe_captcha'] = 'Неправильно введены символы с картинки';  
        }

        if (count($error)) {
            return $error;
        }

        // Отправка сообщения
        $from_email = get_preference('from_email');
        $from_name = get_preference('from_name');

        $subscribe_email = get_preference('subscribe_email');
        $subscribe_subject = get_preference('subscribe_subject');

        $subscribe_view = new View();
        $subscribe_view->assign('type_list', $this->type_list);
        $subscribe_message = $subscribe_view->fetch('module/subscribe/message');

        Mail::send($subscribe_email, $from_email, $from_name, $subscribe_subject, $subscribe_message);

        Session::flash('subscribe_complete', true);

        System::redirectBack();        
    }
    
    // Отключаем кеширование
    protected function getCacheKey()
    {
        return false;
    }
}