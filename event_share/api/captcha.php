<?php
require '../vendor/autoload.php';
require './function.php';
require './BlockPuzzleController.php';
if(!preg_match('/^[0-9a-zA-Z\_]+$/', $_GET['action'])){
    exit('参数错误');
}
switch($_GET['action']){
    case 'get':
        (new BlockPuzzleController())->get();
        break;
    case 'check':
        (new BlockPuzzleController())->check();
        break;
    case 'verification':
        (new BlockPuzzleController())->verification();
        break;
    default:
        exit('参数错误');
}
