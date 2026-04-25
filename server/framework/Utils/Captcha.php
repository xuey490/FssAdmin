<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use Ramsey\Uuid\Uuid;

/**
 * 验证码工具类
 *
 * 提供图形验证码的生成和验证功能，支持字母验证码、数学验证码、
 * 中文验证码等多种类型，可配置干扰线、杂点、背景图片等视觉效果。
 * 验证码值通过 Redis 存储并使用 bcrypt 加密，确保安全性。
 *
 * @package Framework\Utils
 */
class Captcha
{
    /**
     * 验证验证码是否正确
     *
     * 从 Redis 中获取存储的验证码哈希值，与用户输入进行比对验证。
     * 验证成功后会自动删除 Redis 中的验证码记录，防止重复使用。
     *
     * @param string $code 用户输入的验证码
     * @param string $key  验证码的唯一标识键
     *
     * @return bool 验证通过返回 true，否则返回 false
     */
    public static function check(string $code, string $key): bool
    {
        $config = config('captcha.captcha');

        $redisKey = $config['prefix'] . ':' . $key;

        $redis = app('redis');
        $hash  = $redis->get($redisKey);
        if (! $hash) {
            return false;
        }

        $code = mb_strtolower($code, 'UTF-8');
        $ok   = password_verify($code, $hash);

        if ($ok) {
            $redis->del($redisKey);
        }

        return $ok;
    }

    /**
     * 生成 Base64 编码的验证码图片
     *
     * 根据配置生成验证码图片，返回 Base64 编码的图片数据和验证码键名。
     * 支持自定义配置覆盖默认配置，生成的验证码值会存储到 Redis 中。
     *
     * @param array $_config 可选的自定义配置项，会与默认配置合并
     *
     * @return array 返回包含 'key'（验证码键名）和 'base64'（Base64 图片数据）的数组
     *
     * @throws \Exception 生成验证码时可能抛出异常
     */
    public static function base64(array $_config = [])
    {
        $config = app('config')->get('captcha.captcha');

        if (! empty($_config)) {
            $config = array_merge($config, $_config);
        }

        # return $config;
        $generator = self::generateValue($config);
        // 图片宽(px)
        $config['imageW'] || $config['imageW'] = $config['length'] * $config['fontSize'] * 1.5 + $config['length'] * $config['fontSize'] / 2;
        // 图片高(px)
        $config['imageH'] || $config['imageH'] = $config['fontSize'] * 2.5;
        // 建立一幅 $config['imageW'] x $config['imageH'] 的图像
        $im = imagecreate((int) $config['imageW'], (int) $config['imageH']);
        // 设置背景
        imagecolorallocate($im, $config['bg'][0], $config['bg'][1], $config['bg'][2]);

        // 验证码字体随机颜色
        $color = imagecolorallocate($im, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));

        // 验证码使用随机字体
        $ttfPath = BASE_PATH . '/config/assets/' . ($config['useZh'] ? 'zhttfs' : 'ttfs') . '/';

        if (empty($config['fontttf'])) {
            $dir  = dir($ttfPath);
            $ttfs = [];
            while (false !== ($file = $dir->read())) {
                if (substr($file, -4) == '.ttf' || substr($file, -4) == '.otf') {
                    $ttfs[] = $file;
                }
            }
            $dir->close();
            $config['fontttf'] = $ttfs[array_rand($ttfs)];
        }

        $fontttf = $ttfPath . $config['fontttf'];

        if ($config['useImgBg']) {
            self::background($config, $im);
        }

        if ($config['useNoise']) {
            // 绘杂点
            self::writeNoise($config, $im);
        }
        if ($config['useCurve']) {
            // 绘干扰线
            self::writeCurve($config, $im, $color);
        }

        // 绘验证码
        $text = $config['useZh'] ? preg_split('/(?<!^)(?!$)/u', $generator['value']) : str_split($generator['value']); // 验证码

        foreach ($text as $index => $char) {
            $x     = $config['fontSize'] * ($index + 1) * mt_rand((int) 1.2, (int) 1.6) * ($config['math'] ? 1 : 1.5);
            $y     = $config['fontSize'] + mt_rand(10, 20);
            $angle = $config['math'] ? 0 : mt_rand(-40, 40);
            imagettftext($im, $config['fontSize'], $angle, (int) $x, (int) $y, $color, $fontttf, $char);
        }

        ob_start();
        imagepng($im);
        $content = ob_get_clean();
        imagedestroy($im);

        return [
            'key'    => $generator['key'],
            'base64' => 'data:image/png;base64,' . base64_encode($content),
        ];
    }

    /**
     * 生成验证码内容并保存到 Redis
     *
     * 根据配置生成验证码内容，支持数学运算验证码和字符验证码。
     * 验证码答案经过 bcrypt 加密后存储到 Redis，设置过期时间。
     *
     * @param array $config 验证码配置数组
     *
     * @return array 返回包含 'value'（验证码显示内容）和 'key'（验证码键名）的数组
     */
    protected static function generateValue(array $config): array
    {
        if ($config['math']) {
            $x      = random_int(10, 30);
            $y      = random_int(1, 9);
            $value  = "{$x}+{$y}";
            $answer = (string) ($x + $y);
        } else {
            $value      = '';
            $characters = $config['useZh']
                ? preg_split('/(?<!^)(?!$)/u', $config['zhSet'])
                : str_split($config['codeSet']);

            for ($i = 0; $i < $config['length']; ++$i) {
                $value .= $characters[array_rand($characters)];
            }

            $answer = mb_strtolower($value, 'UTF-8');
        }

        // redis key
        $key      = Uuid::uuid4()->toString();
        $redisKey = $config['prefix'] . ':' . $key;

        app('redis')->setex(
            $redisKey,
            $config['expire']??60,
            password_hash($answer, PASSWORD_BCRYPT)
        );

        return [
            'value' => $value,
            'key'   => $key,
        ];
    }

    /**
     * 从指定路径随机选择一个字体文件
     *
     * 扫描指定目录下的 TTF 和 OTF 字体文件，随机选择一个返回。
     * 如果配置中已指定字体，则直接使用配置的字体。
     *
     * @param string $path   字体文件所在目录路径
     * @param array  $config 配置数组，可包含 'fontttf' 指定字体文件
     *
     * @return string 完整的字体文件路径
     */
    protected static function pickFont(string $path, array &$config): string
    {
        if (! empty($config['fontttf'])) {
            return $path . $config['fontttf'];
        }

        $fonts = [];
        foreach (scandir($path) as $f) {
            if (str_ends_with($f, '.ttf') || str_ends_with($f, '.otf')) {
                $fonts[] = $f;
            }
        }

        $config['fontttf'] = $fonts[array_rand($fonts)];
        return $path . $config['fontttf'];
    }

    // ====== 干扰项绘制函数 保留原样 =======

    /**
     * 绘制正弦曲线干扰线
     *
     * 在验证码图片上绘制一条由两条连接的正弦曲线组成的干扰线，
     * 用于增加验证码识别难度，防止机器识别。
     *
     * @param array $config 验证码配置数组，包含图片宽高信息
     * @param mixed $im     图像资源句柄
     * @param mixed $color  线条颜色
     */
    protected static function writeCurve(array $config, $im, $color): void
    {
        $py = 0;
        // 曲线前部分
        $A = mt_rand(1, (int) ($config['imageH'] / 2)); // 振幅
        $b = mt_rand(-(int) ($config['imageH'] / 4), (int) ($config['imageH'] / 4)); // Y轴方向偏移量
        $f = mt_rand(-(int) ($config['imageH'] / 4), (int) ($config['imageH'] / 4)); // X轴方向偏移量
        $T = mt_rand((int) $config['imageH'], (int) ($config['imageW'] * 2)); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand((int) ($config['imageW'] / 2), (int) $config['imageW']); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $config['imageH'] / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($config['fontSize'] / 5);
                while ($i > 0) {
                    imagesetpixel($im, (int) $px + $i, (int) $py + $i, (int) $color); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    --$i;
                }
            }
        }

        // 曲线后部分
        $A   = mt_rand(1, (int) ($config['imageH'] / 2)); // 振幅
        $f   = mt_rand(-(int) ($config['imageH'] / 4), (int) ($config['imageH'] / 4)); // X轴方向偏移量
        $T   = mt_rand((int) $config['imageH'], (int) ($config['imageW'] * 2)); // 周期
        $w   = (2 * M_PI)                                        / $T;
        $b   = $py - $A * sin($w * $px + $f) - $config['imageH'] / 2;
        $px1 = $px2;
        $px2 = $config['imageW'];

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $config['imageH'] / 2; // y = Asin(ωx+φ) + b
                $i  = (int) ($config['fontSize'] / 5);
                while ($i > 0) {
                    imagesetpixel($im, (int) $px + $i, (int) $py + $i, (int) $color);
                    --$i;
                }
            }
        }
    }

    /**
     * 绘制杂点干扰
     *
     * 在验证码图片上随机绘制多个不同颜色的字母或数字杂点，
     * 增加验证码识别难度，防止机器自动识别。
     *
     * @param array $config 验证码配置数组，包含图片宽高信息
     * @param mixed $im     图像资源句柄
     */
    protected static function writeNoise(array $config, $im): void
    {
        $codeSet = 'FSSPHP20222345678abcdefhijkmnpqrstuvwxyz';
        for ($i = 0; $i < 10; ++$i) {
            // 杂点颜色
            $noiseColor = imagecolorallocate($im, mt_rand(150, 225), mt_rand(150, 225), mt_rand(150, 225));
            for ($j = 0; $j < 5; ++$j) {
                // 绘杂点
                imagestring($im, 5, mt_rand(-10, (int) $config['imageW']), mt_rand(-10, (int) $config['imageH']), $codeSet[mt_rand(0, 29)], (int) $noiseColor);
            }
        }
    }

    /**
     * 绘制背景图片
     *
     * 从指定目录随机选择一张背景图片，并将其缩放填充到验证码图片中。
     * 注意：大尺寸背景图片会占用较多系统资源。
     *
     * @param array $config 验证码配置数组，包含图片宽高信息
     * @param mixed $im     图像资源句柄
     */
    protected static function background(array $config, $im): void
    {
        $path = BASE_PATH . '/config/assets/bgs/';
        $dir  = dir($path);

        $bgs = [];
        while (false !== ($file = $dir->read())) {
            if ($file[0] != '.' && substr($file, -4) == '.jpg') {
                $bgs[] = $path . $file;
            }
        }
        $dir->close();

        $gb = $bgs[array_rand($bgs)];

        [$width, $height] = @getimagesize($gb);
        $bgImage          = @imagecreatefromjpeg($gb);
        @imagecopyresampled($im, $bgImage, 0, 0, 0, 0, (int) $config['imageW'], (int) $config['imageH'], $width, $height);
        @imagedestroy($bgImage);
    }
}
