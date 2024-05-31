<?php

use  \Fastknife\Service\BlockPuzzleCaptchaService;
use think\facade\Db;

session_start();
function json_return($code = 0, $msg = '', $data = [])
{
    echo json_encode([
        "code" => $code,
        "msg" => $msg,
        "data" => $data
    ]);
}

function createdb()
{
    $config = include __DIR__ . '/config.php';
    Db::setConfig($config['db']);
}
createdb();

function logs($msg)
{
    $date = date('Y-m-d');
    if (!is_dir(__DIR__ . '/../data/logs')) {
        mkdir(__DIR__ . '/../data/logs');
    }
    if (!is_dir(__DIR__ . '/../data/logs/' . $date)) {
        mkdir(__DIR__ . '/../data/logs/' . $date);
    }
    $fp = fopen(__DIR__ . '/../data/logs/' . $date . '/' . date('H') . '.log', 'a+');
    fwrite($fp, '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n");
    fclose($fp);
}

function checkKey()
{
    // 获取一个post参数，判断该参数是否为空，如果为空，则返回一个错误信息，如果不为空，则继续执行
    if (empty($_POST['key'])) {
        json_return(1, "请输入key");
        exit;
    }
    // 判断key是否为英文字母和数字组成
    if (!preg_match('/^[a-zA-Z0-9]+$/', $_POST['key'])) {
        json_return(2, "key错误");
        exit;
    }
    // 根据获取到的key，检查当前是否存在目录，如果不存在，返回一个错误信息，如果存在，则继续执行
    if (!is_dir('../data/' . $_POST['key'])) {
        json_return(3, "key不存在");
        exit;
    }
    // 判断是否传了id
    if (empty($_POST['id'])) {
        json_return(4, "请输入id");
        exit;
    }
    // 判断id是否为大于0的整数
    if (!preg_match('/^[0-9]+$/', $_POST['id'])) {
        json_return(5, "id错误");
        exit;
    }
    if (!dir('../data/' . $_POST['key'] . '/' . $_POST['id'])) {
        mkdir('../data/' . $_POST['key'] . '/' . $_POST['id']);
    }
    if(isset($_POST['token'])){
        // 用正则验证token是否为md5的值
        if (!preg_match('/^[a-z0-9]{32}$/', strtolower($_POST['token']))) {
            json_return(4, "token错误");
            exit;
        }
    }
}

function getUser($token)
{
    $user = Db::table('users')->where('token', $token)->find();
    if (!$user) {
        return false;
    }
    return $user;
}

function validate($config){
    // 二次验证码验证
    $service = new BlockPuzzleCaptchaService($config['captcha']);
    $captchaVerification = $_POST['captchaVerification'] ?? '';
    $success = false;
    try {
        $service->verificationByEncryptCode($captchaVerification);
        $success = true;
    } catch (\Exception $e) {
    }
    return $success;
}

function createGame(){
    if(!isset($_POST['game'])){
        return [3, '参数错误'];
    }
    $data = json_encode([
        'game' => $_POST['game'],
        'group_count' => isset($_POST['group_count']) ? json_decode($_POST['group_count'], true) : [],
        'winner_matchs' => isset($_POST['winner_matchs']) ? json_decode($_POST['winner_matchs'], true) : [],
        'loser_matchs' => isset($_POST['loser_matchs']) ? json_decode($_POST['loser_matchs'], true) : [],
        'winner_idx' => $_POST['winner_idx'] ?? 0,
        'looser_idx' => $_POST['looser_idx'] ?? 0,
    ]);
    if (@file_put_contents('../data/' . $_POST['key'] . '/' . $_POST['id'] . '/' . 'game.json', $data)) {
        return [0, '更新成功'];
    } else {
        return [6, '更新失败'];
    }
}
function decimalToBase62(int $decimal): string
{
    $base62Chars = file_get_contents(__DIR__ . '/../data/keys');

    $result = '';
    do {
        $remainder = $decimal % 62;
        $result = $base62Chars[$remainder] . $result;
        $decimal = (int) ($decimal / 62);
    } while ($decimal > 0);

    return $result;
}
class SnowFlake
{
    const EPOCH = 1479533469598;
    const max12bit = 4095;
    const max41bit = 1099511627775;
 
    static $machineId = null;
 
    public static function machineId($mId = 0) {
        self::$machineId = $mId;
    }
 
    public static function generateParticle() {
        /*
        * Time - 42 bits
        */
        $time = floor(microtime(true) * 1000);
 
        /*
        * Substract custom epoch from current time
        */
        $time -= self::EPOCH;
 
        /*
        * Create a base and add time to it
        */
        $base = decbin(self::max41bit + $time);
 
 
        /*
        * Configured machine id - 10 bits - up to 1024 machines
        */
        if(!self::$machineId) {
            $machineid = self::$machineId;
        } else {
            $machineid = str_pad(decbin(self::$machineId), 10, "0", STR_PAD_LEFT);
        }
 
        /*
        * sequence number - 12 bits - up to 4096 random numbers per machine
        */
        $random = str_pad(decbin(mt_rand(0, self::max12bit)), 12, "0", STR_PAD_LEFT);
 
        /*
        * Pack
        */
        $base = $base.$machineid.$random;
 
        /*
        * Return unique time id no
        */
        return bindec($base);
    }
 
    public static function timeFromParticle($particle) {
        /*
        * Return time
        */
        return bindec(substr(decbin($particle),0,41)) - self::max41bit + self::EPOCH;
    }
}

class filterWords
{
    protected $dict; //敏感词字典  

    public function __construct()
    {
        $this->loadDataFormFile();
    }

    /** 
     * 从文件中加载敏感词字典  
     */
    protected function loadDataFormFile()
    {
        //此处可以修改为读文件，一般敏感词为文件形式，一行对应一个敏感词  
        //如果经常调用的话，还可以通过缓存处理（redis、memcache）等等，此处不详细处理  
        $arr = include __DIR__ . '/words.php';
        //将敏感词加入此次节点  
        foreach ($arr as $value) {
            $this->addWords(trim($value));
        }
    }
    /** 
     * 分割文本  
     * @param $str 
     * @return array[]|false|string[] 
     */
    protected function splitStr($str)
    {
        //将字符串分割成组成它的字符  
        // 其中/u 表示按unicode(utf-8)匹配（主要针对多字节比如汉字），否则默认按照ascii码容易出现乱码  
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /** 
     * 添加敏感字至节点  
     * @param $words 
     */
    protected function addWords($words)
    {
        //1.分割字典  
        $wordArr = $this->splitStr($words);
        $curNode = &$this->dict;
        foreach ($wordArr as $char) {
            if (!isset($curNode)) {
                $curNode[$char] = [];
            }
            $curNode = &$curNode[$char];
        }
        //标记到达当前节点完整路径为"敏感词"  
        $curNode['end'] = !isset($curNode['end']) ? 0 : $curNode['end'] + 1;
    }

    /**  
     * 敏感词校验  
     * @param $str ;需要校验的字符串  
     * @param int $level ;屏蔽词校验等级 1-只要顺序包含都屏蔽；2-中间间隔skipDistance个字符就屏蔽；3-全词匹配即屏蔽  
     * @param int $skipDistance ;允许敏感词跳过的最大距离，如笨aa蛋a傻瓜等等  
     * @param bool $isReplace ;是否需要替换，不需要的话，返回是否有敏感词，否则返回被替换的字符串  
     * @param string $replace ;替换字符  
     * @return bool|string  
     */
    public function filter($str, $level = 1, $skipDistance = 2, $isReplace = true, $replace = '*')
    {
        //允许跳过的最大距离  
        if ($level == 1) {
            $maxDistance = strlen($str) + 1;
        } elseif ($level == 2) {
            $maxDistance = max($skipDistance, 0) + 1;
        } else {
            $maxDistance = 2;
        }
        $strArr = $this->splitStr($str);
        $strLength = count($strArr);
        $isSensitive = false;
        for ($i = 0; $i < $strLength; $i++) {
            //判断当前敏感字是否有存在对应节点  
            $curChar = $strArr[$i];
            if (!isset($this->dict[$curChar])) {
                continue;
            }
            $isSensitive = true; //引用匹配到的敏感词节点  
            $curNode = &$this->dict[$curChar];
            $dist = 0;
            $matchIndex = [$i]; //匹配后续字符串是否match剩余敏感词  
            for ($j = $i + 1; $j < $strLength && $dist < $maxDistance; $j++) {
                if (!isset($curNode[$strArr[$j]])) {
                    $dist++;
                    continue;
                }
                //如果匹配到的话，则把对应的字符所在位置存储起来，便于后续敏感词替换  
                $matchIndex[] = $j;
                //继续引用  
                $curNode = &$curNode[$strArr[$j]];
            }
            //判断是否已经到敏感词字典结尾，是的话，进行敏感词替换  
            if (isset($curNode['end']) && $isReplace) {
                foreach ($matchIndex as $index) {
                    $strArr[$index] = $replace;
                }
                $i = max($matchIndex);
            }
        }
        if ($isReplace) {
            return implode('', $strArr);
        } else {
            return $isSensitive;
        }
    }
}
