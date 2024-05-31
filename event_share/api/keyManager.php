<?php
header('Content-Type: application/json');
require '../vendor/autoload.php';
$config = include './config.php';
require './function.php';
$user = getUser($_POST['token'] ?? '');
if (!$user) {
    json_return(2, "token错误");
    exit;
}
if ($user['email'] != $config['manager']) {
    json_return(3, "权限不足");
    exit;
}
$action = $_POST['action'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
    json_return(4, "参数错误");
    exit;
}
switch ($action) {
    case 'list':
        $files = scandir(__DIR__ . '/../data/');
        $files = array_diff($files, ['.', '..', 'cache', 'email.db', 'users.db', 'keys']);
        json_return(0, 'success', [
            'list' => $files
        ]);
        break;
    case 'create':
        $dir = __DIR__ . '/../data';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $dir .= '/';
        if (!file_exists($dir . 'keys')) {
            file_put_contents($dir . 'keys', str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'));
        }
        $id = decimalToBase62(SnowFlake::generateParticle());
        if (is_dir($dir . $id)) {
            while (true) {
                $id = decimalToBase62(SnowFlake::generateParticle());
                if (!is_dir($dir . $id)) {
                    mkdir($dir . $id);
                    break;
                }
            }
        } else {
            mkdir($dir . $id);
        }
        json_return(0, '创建成功', [
            'id' => $id,
        ]);
        break;
    case 'game_list':
        $key = $_POST['key'] ?? '';
        if(!$key){
            json_return(1, '参数错误');
            break;
        }
        if(!preg_match('/^[a-zA-Z0-9]+$/', $key)){
            json_return(2, '参数错误');
            break;
        }
        if(!is_dir(__DIR__ . '/../data/' . $key)){
            json_return(3, 'key不存在');
            break;
        }
        $files = scandir(__DIR__ . '/../data/' . $key);
        $files = array_diff($files, ['.', '..']);
        $list = [];
        foreach($files as $v){
            if(is_file(__DIR__ . '/../data/' . $key . '/' . $v . '/game.json')){
                $list[] = json_decode(file_get_contents(__DIR__ . '/../data/' . $key . '/' . $v . '/game.json'), true);
            }
        }
        json_return(0, 'success', [
            'list' => $list
        ]);
        break;
    default:
        json_return(1, '未知操作');
}
