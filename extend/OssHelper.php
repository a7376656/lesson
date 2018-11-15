<?php
/**
 * Created by PhpStorm.
 * Author: huxinlu
 * Date: 2017/11/2
 * Time: 15:29
 */
use OSS\OssClient;
use OSS\Core\OssException;

class OssHelper
{
    const accessKeyId = 'LTAIhnvoqmZuzocM';
    const accessKeySecret = 'rW0WNLMco2K5eGdQNa1bRhvP3VCGLl';
    const endpoint = 'oss-cn-qingdao.aliyuncs.com';
    const bucket = 'kcbms';

    private static $_instance;

    /**
     * 获取一个OssClient实例
     * @return null|OssClient
     */
    public static function getInstance() {
        if (!(self::$_instance instanceof OssClient)) {
            try {
                self::$_instance = new OssClient(self::accessKeyId, self::accessKeySecret, self::endpoint, false);
            } catch (OssException $e) {
                printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
                printf($e->getMessage() . "\n");
                return null;
            }
        }
        return self::$_instance;
    }

    /**
     * 获取bucket
     * @return string
     */
    public static function getBucketName()
    {
        return self::bucket;
    }

    /**
     * 上传图片
     * @author huxinlu
     * @param string $file 接收的文件流的名称
     * @param bool $fileName 默认是系统生成，可自定义
     * @return array|string
     */
    public function upload($file = 'file', $fileName = true)
    {
        $file = request()->file($file);
        $info = $file->move(getcwd().'/runtime/uploads',$fileName);

        if (!$info) {
            return return_array(1013, '上传的图片信息获取失败');
        } else {
            $fileName = $info->getSaveName();//上传到oss上的路径
            $fileName = str_replace("\\",'/',$fileName);

            $ossClient = self::getInstance();
            $bucket = self::bucket;

            $ossClient->uploadFile($bucket, $fileName, $info->getPathname());

            return return_array(1000, 'ok', config('imageDomain').$fileName.'/thumbnail');
        }
    }
}