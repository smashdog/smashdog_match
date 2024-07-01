<?php
header('Content-Type: application/json');
require '../vendor/autoload.php';
$config = include './config.php';
require './function.php';
$action = $_POST['action'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
    json_return(2, "参数错误");
    exit;
}
checkKey();
switch($action ?? ''){
    case 'create_sign':
        $r = createGame();
        json_return($r[0], $r[1]);
        break;
    case 'is_sign':
        $is_sign = checkSign();
        json_return($is_sign[0], $is_sign[1], signList($is_sign, $config));
        break;
    case 'sign':
        $is_sign = checkSign();
        if($is_sign[0] != 0){
            json_return($is_sign[0], $is_sign[1]);
            break;
        }
        $game = json_decode($is_sign[4]['game'], true);
        if($game['game_status'] != 0){
            json_return(-1, "报名已结束");
            break;
        }
        if(count($is_sign[3]) >= ($game['group_count'] * $game['group_nums'])){
            json_return(-1, "报名已满");
            break;
        }
        $r = $is_sign[3];
        $validateSign = validateSign($is_sign[3]);
        if($validateSign[0] != 0){
            json_return($validateSign[0], $validateSign[1]);
            break;
        }
        list($nickname, $fastcopy, $weixin) = $validateSign[1];
        $r[$is_sign[2]['id']] = [
            'nickname' => $nickname,
            'fastcopy' => $fastcopy,
            'weixin' => $weixin
        ];
        file_put_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json', json_encode($r, JSON_UNESCAPED_UNICODE));
        json_return(0, "报名成功");
        break;
    case 'list':
        $is_sign = checkSign();
        if($is_sign[0] > 1){
            json_return($is_sign[0], $is_sign[1]);
            break;
        }
        json_return(0, "", signList($is_sign, $config));
        break;
    case 'edit':
        list($r, $sign) = validateManagerSign($config);
        if($r != 0){
            json_return($r, $sign);
            break;
        }
        list($r, $other) = validateSign($sign);
        if($r != 0){
            json_return($r, $other);
            break;
        }
        list($nickname, $fastcopy, $weixin) = $other;
        $sign[$_POST['k']] = [
            'nickname' => $nickname,
            'fastcopy' => $fastcopy,
            'weixin' => $weixin
        ];
        file_put_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json', json_encode($sign, JSON_UNESCAPED_UNICODE));
        json_return(0, "修改成功");
        break;
    case 'del':
        $game = json_decode(json_decode(file_get_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'game.json'), true)['game'], true);
        if($game['game_status'] != 0){
            json_return(-1, "报名已结束不能删除");
            break;
        }
        list($r, $sign) = validateManagerSign($config);
        if($r != 0){
            json_return($r, $sign);
            break;
        }
        unset($sign[$_POST['k']]);
        file_put_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json', json_encode($sign, JSON_UNESCAPED_UNICODE));
        json_return(0, "删除成功");
        break;
    case 'sync':
        $sign = [];
        if(file_exists('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json')){
            $sign = json_decode(file_get_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json'), true);
        }
        json_return(0, "success", $sign);
        break;
}

/**
 * Undocumented function
 *
 * @return array [状态码（0正确）, 消息, 用户信息, 报名信息, 比赛信息] 2-4只有状态码为0时存在
 */
function checkSign(){
    if(!isset($_POST['token'])){
        return [301, "请先登录或注册"];
    }
    $user = getUser($_POST['token']);
    if($user === false){
        return [301, "登录已过期，请重新登录"];
    }
    $r1 = [];
    if(file_exists('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'game.json')){
        $r1 = json_decode(file_get_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'game.json'), true);
    }
    if(!$r1){
        return [7, '比赛信息错误'];
    }
    $r = [];
    if(file_exists('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json')){
        $r = json_decode(file_get_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json'), true);
    }
    if(!isset($r[$user['id']])){
        return [0, "未报名", $user, $r, $r1];
    }
    return [-1, '已报名', $user, $r, $r1];
}

function signList($is_sign, $config){
    $list = $is_sign[3];
    if($is_sign[2]['email'] == $config['manager']){
        $title = [
            'nickname' => '昵称',
            'fastcopy' => '用户码',
            'weixin' => '微信号',
            'uid' => 'uid',
            'action' => '管理',
        ];
    }else{
        $title = [
            'nickname' => '昵称'
        ];
    }
    $data = [
        'title' => $title,
        'game' => json_decode($is_sign[4]['game'], true),
        'list' => []
    ];
    foreach($list as $k => $v){
        $temp = [];
        foreach($title as $k1 => $v1){
            if($k1 != 'action' && $k1 != 'uid') $temp[$k1] = $v[$k1];
        }
        if($is_sign[2]['email'] == $config['manager']){
            $temp['uid'] = $k;
            $temp['action'] = '';
        }
        $data['list'][] = $temp;
    }
    return $data;
}

function validateSign($signs)
{
    $weixin = $_POST['weixin'] ?? '';
    // 验证微信号
    if(!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{5,20}$/', $weixin) && !preg_match('/^1[3-9]\d{9}$/', $weixin)){
        return [2, "微信号错误"];
    }
    $nickname = $_POST['nickname'] ?? '';
    // 用正则验证昵称是否为中文、英文、下划线、数字组合
    if(!preg_match('/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]+$/u', $nickname)){
        return [3, "昵称必须为中文、英文、下划线、数字组合"];
    }
    // 判断昵称长度是否在1-10个字符之间
    if(mb_strlen($nickname) < 1 || mb_strlen($nickname) > 10){
        return [4, "昵称长度必须在1-10个字符之间"];
    }
    $err = false;
    $k = $_POST['k'] ?? null;
    foreach($signs as $k1 => $v){
        if($nickname == $v['nickname'] && $k1 != $k){
            $err = true;
            break;
        }
    }
    if($err){
        return [5, "昵称重复"];
    }
    // 验证昵称是否有敏感词
    $fliter = include __DIR__ . '/words.php';
    $err = false;
    foreach($fliter as $v){
        if(preg_match('/'.$v.'/', $nickname)){
            $err = true;
            break;
        }
    }
    if($err){
        return [5, "昵称包含敏感词"];
    }
    $fastcopy = $_POST['fastcopy'] ?? '';
    // 用正则验证fastcopy是否为数字
    if(!preg_match('/^[0-9]+$/', $fastcopy)){
        return [6, "用户码必须为数字"];
    }
    return [0, [$nickname,$fastcopy,$weixin]];
}

function validateManagerSign($config){
    $user = getUser($_POST['token'] ?? '');
    if(!$user){
        return [301, "登录已过期，请重新登录"];
    }
    if($user['email'] != $config['manager']){
        return [1, "没有权限"];
    }
    $sign = [];
    if(file_exists('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json')){
        $sign = json_decode(file_get_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'sign.json'), true);
    }
    if(count($sign) == 0){
        return [2, "参数错误"];
    }
    $k = $_POST['k'] ?? null;
    if(!$k){
        return [3, "参数错误"];
    }
    if(!isset($sign[$k])){
        return [4, "参数错误"];
    }
    return [0, $sign];
}