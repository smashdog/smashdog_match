<?php
function decimalToBase62(int $decimal): string
{
    $base62Chars = file_get_contents('./data/keys');

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
if(!is_dir('./data')){
    mkdir('./data');
}
if(!file_exists('./data/keys')){
    file_put_contents('./data/keys', str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'));
}
$id = decimalToBase62(SnowFlake::generateParticle());
if(is_dir('./data/'.$id)){
    while(true){
        $id = decimalToBase62(SnowFlake::generateParticle());
        if(!is_dir('./data/'.$id)){
            mkdir('./data/'.$id);
            break;
        }
    }
}else{
    mkdir('./data/'.$id);
}
echo "key为“{$id}”，请妥善保管\n";