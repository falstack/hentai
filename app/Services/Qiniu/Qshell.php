<?php
/**
 * Created by PhpStorm.
 * User: yuistack
 * Date: 2019-03-14
 * Time: 21:27
 */

namespace App\Services\Qiniu;


use App\Services\Qiniu\Storage\BucketManager;

class Qshell
{
    // 抓取小文件
    public function fetch($srcResUrl, $fileName = '')
    {
        if (strpos($srcResUrl, '//') === 0)
        {
            $srcResUrl = "http:{$srcResUrl}";
        }
        $auth = new \App\Services\Qiniu\Auth();
        $bucketManager = new BucketManager($auth);

        $now = time();
        $str = str_rand();
        $tail = explode('?', $srcResUrl)[0];
        $tail = explode('.', $tail);
        if (count($tail) >= 2)
        {
            $tail = "." . last($tail);
        }
        else
        {
            $tail = '';
        }
        $target = $fileName ? $fileName : "fetch/{$now}/{$str}{$tail}";

        list($ret, $err) = $bucketManager->fetch($srcResUrl, config('app.qiniu.bucket'), $target);

        if ($err !== null)
        {
            return '';
        }

        return $ret['key'];
    }

    // 抓取音频
    public function audio($srcResUrl, $fileName = '')
    {
        if (strpos($srcResUrl, '//') === 0)
        {
            $srcResUrl = "http:{$srcResUrl}";
        }
        $auth = new \App\Services\Qiniu\Auth();
        $bucketManager = new BucketManager($auth);

        $now = time();
        $str = str_rand();
        $tail = explode('?', $srcResUrl)[0];
        $tail = explode('.', $tail);
        if (count($tail) >= 2)
        {
            $tail = "." . last($tail);
        }
        else
        {
            $tail = '';
        }
        $target = $fileName ? $fileName : "fetch/{$now}/{$str}{$tail}";

        list($ret, $err) = $bucketManager->fetch($srcResUrl, config('app.qiniu.bucket'), $target);

        if ($err !== null)
        {
            return $err;
        }

        return $ret;
    }
}
