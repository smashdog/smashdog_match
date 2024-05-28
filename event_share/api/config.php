<?php
return [
    'email' => [
        'Host' => '', // 邮箱服务器地址
        'Port' => 0, // 邮箱服务器端口
        'Username' => '', // 邮箱用户名
        'Password' => '', // 邮箱密码
    ],
    'captcha' => [
        'font_file' => '', //自定义字体包路径， 不填使用默认值
        //文字验证码
        'click_world' => [
            'backgrounds' => [],
            'word_num' => 4, //写入多少字文字（2-5）
        ],
        //滑动验证码
        'block_puzzle' => [
            /*背景图片路径， 不填使用默认值， 支持string与array两种数据结构。string为默认图片的目录，array索引数组则为具体图片的地址*/
            'backgrounds' => [],

            /*模板图,格式同上支持string与array*/
            'templates' => [],

            'offset' => 10, //容错偏移量

            'is_cache_pixel' => true, //是否开启缓存图片像素值，开启后能提升服务端响应性能（但要注意更换图片时，需要清除缓存）

            'is_interfere' => true, //开启干扰图
        ],
        //水印
        'watermark' => [
            'fontsize' => 12,
            'color' => '#000000',
            'text' => '我的水印'
        ],
        'cache' => [
            //若您使用了框架，并且想使用类似于redis这样的缓存驱动，则应换成框架的中的缓存驱动
            'constructor' => \Fastknife\Utils\CacheUtils::class,
            'method' => [],
            'options' => [
                //如果您依然使用\Fastknife\Utils\CacheUtils做为您的缓存驱动，那么您可以自定义缓存配置。
                'expire'        => 300, //缓存有效期 （默认为0 表示永久缓存）
                'prefix'        => '', //缓存前缀
                'path'          => __DIR__ . '/../data/cache/', //缓存目录
                'serialize'     => [], //缓存序列化和反序列化方法
            ]
        ]
    ],
    'db' => [
        'default' => 'sqlite',
        'connections' => [
            'sqlite' => [
                'type' => 'sqlite',
                'database' => __DIR__ . '/../data/users.db',
                'prefix' => '',
                'params' => [
                    PDO::ATTR_CASE => PDO::CASE_NATURAL
                ]
            ]
        ]
    ],
    'manager' => 'test@test.com', // 管理员邮箱
];