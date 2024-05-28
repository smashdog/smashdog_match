<?php
header('Content-Type: application/json');
require '../vendor/autoload.php';
$config = include './config.php';
require './function.php';

use think\facade\Db;

if (!preg_match('/^[a-zA-Z0-9_]+$/', $_POST['action'])) {
    json_return(2, "参数错误");
    exit;
}

switch ($_POST['action'] ?? '') {
    case 'register':
        // 使用正则匹配，判断是否为邮箱格式
        if (!preg_match('/^[a-zA-Z0-9\-_]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]+$/', $_POST['email'])) {
            json_return(7, "邮箱格式错误");
            break;
        }
        if(strlen($_POST['email']) > 100){
            json_return(22, "邮箱长度过长");
            break;
        }
        $result = Db::table('users')->where('email', $_POST['email'])->value('id');
        if ($result) {
            json_return(8, "此邮箱已被注册");
            break;
        }
        // 验证密码是否为复杂密码，包含特殊字符、数字、大小写字母
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,}$/', $_POST['password'])) {
            json_return(9, "密码格式错误，必须包含特殊字符（@$!%*?&）、数字、大小写字母，长度必须大于8");
            break;
        }
        // 二次验证码验证
        if(!validate($config)){
            json_return(21, "人机验证失败");
        }
        // 写入数据库
        $time = time();
        $token = md5(file_get_contents('../data/keys') . $_POST['email'] . md5(file_get_contents('../data/keys') . $_POST['password']) . microtime(true));
        $id = Db::table('users')->insertGetId([
            'email' => $_POST['email'],
            'password' => md5(file_get_contents('../data/keys') . $_POST['password']),
            'reg_time' => $time,
            'token' => $token,
            'last_email_time' => $time
        ]);
        if ($id) {
            Db::table('email')->insert([
                'mailto' => $_POST['email'],
                'content' => "<p>您于" . date('Y-m-d H:i:s') . "注册了心情过客的比赛管理器</p>
                <p>请点击<a href=\"//{$_SERVER['HTTP_HOST']}/#/verify_email?vali_key={$token}\">这里</a>进行验证</p>
                <p>如果以上链接无法点击，请将下面的链接复制到浏览器地址栏访问</p>
                <p>//{$_SERVER['HTTP_HOST']}/#/verify_email?vali_key={$token}</p>"
            ]);
            json_return(0, "注册成功，请前往邮箱进行验证");
        } else {
            json_return(11, "注册失败，请联系心情过客反馈问题");
        }
        break;
    case 'verify_email':
        // 用正则验证get参数是否为md5后的数据
        if (!preg_match('/^[a-z0-9]{32}$/', $_POST['vali_key'])) {
            json_return(12, "验证失败，key错误");
            break;
        }
        if ($r = Db::table('users')->where('token', $_POST['vali_key'])->find()) {
            if ($r['email_validate'] == 1) {
                $token = md5(file_get_contents('../data/keys') . $r['email'] . $r['password'] . microtime(true));
                Db::table('users')->where('id', $r['id'])->update([
                    'token' => $token
                ]);
                json_return(0, "该邮箱已经验证过了", [
                    "token" => $token
                ]);
                break;
            }
            // 验证成功
            if (Db::table('users')->where('id', $r['id'])->update(['email_validate' => 1])) {
                $token = md5(file_get_contents('../data/keys') . $r['email'] . $r['password'] . microtime(true));
                Db::table('users')->where('id', $r['id'])->update([
                    'token' => $token
                ]);
                json_return(0, "验证成功", [
                    "token" => $token
                ]);
            } else {
                json_return(14, "验证失败，请联系心情过客反馈问题");
            }
        } else {
            json_return(15, "验证失败，key错误");
            break;
        }
        break;
    case 'login':
        if (!preg_match('/^[a-zA-Z0-9\-_]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z0-9]+$/', $_POST['email'])) {
            json_return(16, "邮箱格式错误");
            break;
        }
        // 验证密码是否为复杂密码，包含特殊字符、数字、大小写字母
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,}$/', $_POST['password'])) {
            json_return(17, "密码格式错误，必须包含特殊字符（@$!%*?&）、数字、大小写字母，长度必须大于8");
            break;
        }
        // 二次验证码验证
        if(!validate($config)){
            json_return(21, "人机验证失败");
            break;
        }
        if ($r = Db::table('users')->where('email', $_POST['email'])->field('id,password,login_error_time, email, password')->find()) {
            if ($r['login_error_time'] >= 3) {
                json_return(18, "该账号密码错误次数过多，请找回密码");
                break;
            }
            if ($r['password'] !== md5(file_get_contents('../data/keys') . $_POST['password'])) {
                Db::table('users')->where('id', $r['id'])->inc('login_error_time')->update();
                json_return(19, "密码错误");
                break;
            } else {
                $token = md5(file_get_contents('../data/keys') . $r['email'] . $r['password'] . microtime(true));
                $data = [
                    'token' => $token
                ];
                if ($r['login_error_time'] > 0) {
                    $data['login_error_time'] = 0;
                }
                Db::table('users')->where('id', $r['id'])->update($data);
                json_return(0, '登录成功', [
                    'token' => $token
                ]);
                break;
            }
        } else {
            json_return(20, "邮箱不存在");
        }
        break;
    case 'check_token':
        if (!preg_match('/^[a-z0-9]{32}$/', $_POST['token'])) {
            json_return(12, "token错误");
            break;
        }
        if ($r = Db::table('users')->where('token', $_POST['token'])->value('email')) {
            json_return(0, 'token验证成功', [
                'email' => $r
            ]);
        } else {
            json_return(22, "token验证失败");
        }
        break;
    default:
        json_return(-1, '参数错误！');
}