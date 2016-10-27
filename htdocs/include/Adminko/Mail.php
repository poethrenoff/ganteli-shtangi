<?php
namespace Adminko;

include_once CLASS_DIR . 'PHPMailer/PHPMailerAutoload.php';

class Mail
{
    public static function send($to, $from, $name, $subject, $body, $files = array())
    {
        return self::sendMessage($to, self::prepareMessage($from, $name, $subject, $body, $files));
    }

    public static function prepareMessage($from, $name, $subject, $body, $files = array())
    {
        $mail = new \PHPMailer();
        
        $mail->CharSet = 'utf-8';
        $mail->Encoding = 'base64';
        $mail->setLanguage('ru');
        $mail->isHtml(true);
        
        $mail->Subject = $subject;
        $mail->setFrom($from, $name);

        $body = preg_replace_callback( 
            '/src=\"(.+)\"/isU',
            function($match) use ($mail) {
                $path_parts = pathinfo($match[1]); $cid = uniqid();
                if ($img_data = file_get_contents($match[1])) {
                    $mail->addStringEmbeddedImage($img_data, $cid, $path_parts['basename']);
                }
                return 'src="cid:' . $cid . '"';
            }, $body
        );
        $mail->Body = $body;
        
        foreach ($files as $file_name => $file_path) {
            $mail->addAttachment($file_path);
        }
        
        return $mail;
    }
    
    public static function sendMessage($to, $mail)
    {
        $mail->addAddress($to);
        
        return $mail->send();
    }
}
