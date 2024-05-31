<?php
header('Content-Type: application/json');
require '../vendor/autoload.php';
$config = include './config.php';
require './function.php';

$action = $_POST['action'] ?? '';

if (!preg_match('/^[a-zA-Z0-9_]+$/', $action) && $action != '') {
    json_return(2, "参数错误");
    exit;
}
checkKey();
// 根据传递的参数，决定逻辑流程，分为“update”更新文件和“空”为读文件
switch ($_POST['action'] ?? '') {
    case 'update':
        $r = createGame();
        json_return($r[0], $r[1]);
        break;
    default:
        if (!file_exists('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'game.json')) {
            json_return(-1, "数据不存在");
            break;
        }
        $r = json_decode(file_get_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'game.json'), true);
        if(is_string($r['game'])){
            $r['game'] = json_decode($r['game'], true);
        }
        json_return(0, "success", $r);
}
