<?php
require './vendor/autoload.php';
$config = include './api/config.php';
require './api/function.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use think\facade\Db;

while(true){
    try {
        $r = Db::table('email')->where('1 = 1')->order('id asc')->find();
        if($r){
            // 发送smtp邮件
            $mail = new PHPMailer(true);
            // 设置SMTP配置
            $mail->isSMTP();
            $mail->Host = $config['email']['Host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['email']['Username'];
            $mail->Password = $config['email']['Password'];
            $mail->SMTPSecure = '';
            $mail->Port = $config['email']['Port'];

            // 设置发件人和回复人
            $mail->setFrom('no-reply@biila.com', '心情过客');
            $mail->addReplyTo('no-reply@biila.com', '心情过客');

            // 添加收件人
            $mail->addAddress($r['mailto'], $r['mailto']); // 可以添加多个收件人

            // 设置邮件主题和正文
            $mail->Subject = '心情过客的比赛管理器邮箱验证';

            // 如果需要发送HTML格式的邮件
            $mail->isHTML(true);
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Body = $r['content'];

            // 发送邮件
            $mail->send();
            Db::table('email')->delete($r['id']);
            // 重置自增
            $r = Db::table('email')->count();
            if($r['mycount'] == 0){
                Db::table('sqlite_sequence')->where('name', 'email')->update([
                    'seq' => 0
                ]);
            }
        }
    } catch (Exception $e) {
        logs($e->getMessage());
    }
    echo '.';
    sleep(1);
}