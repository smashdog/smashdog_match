<?php
require '../vendor/autoload.php';
require './function.php';
use Gregwar\Captcha\CaptchaBuilder;
if(!preg_match('/^[0-9a-zA-Z\_]+$/', $_GET['action'])){
    exit('参数错误');
}
switch($_GET['action']){
    case 'get':
        $builder = new CaptchaBuilder();
        header('Content-type: image/jpeg');
        $builder->build();
        $_SESSION['phrase'] = strtolower($builder->getPhrase());
        $builder->output();
        break;
    case "test":
        $_SESSION['test'] = '123';
        break;
    case "test1":
        echo $_SESSION['test'];
        break;
    default:
        exit('参数错误');
}
