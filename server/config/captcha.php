<?php
return [
    'enable' => true,
    'captcha' => [
        // 验证码存储key前缀
        'prefix'  => 'captcha',
        // 验证码字符集合
        'codeSet'  => 'ABCDEFGHJKLMNPQRTUVWXY2345678abcdefhijkmnpqrstuvwxyz',
        // 是否使用中文验证码
        'useZh'  => false,
        // 中文验证码字符串
        'zhSet'  => '以最小内核提供最大的扩展性与最强的性能们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水',
        // 是否使用背景图（不建议开启）
        'useImgBg' => false,
        // 是否使用混淆曲线
        'useCurve' => true,
        // 是否添加杂点
        'useNoise' => true,
        // 验证码图片高度
        'imageH'   => 0,
        // 验证码图片宽度
        'imageW'   => 0,
        // 验证码位数
        'length'   => 4,
        // 验证码字符大小
        'fontSize' => 25,
        // 验证码过期时间 不设置默认60秒
        'expire'   => 300,
        // 验证码字体 不设置则随机
        'fontttf'  => '',
        // 背景颜色
        'bg'       => [rand(200,250), 251, 254],
        // 是否使用算术验证码（不建议开启）
        'math'     => true,
    ]
];