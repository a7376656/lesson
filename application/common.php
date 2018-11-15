<?php
/**
 * 得到带错误码和错误信息的数组返回值
 * @author huxinlu
 * @param int $code 错误码
 * @param string $msg 错误信息
 * @param array $data 返回的数组信息
 * @return array
 */
function return_array($code = 1000, $msg = 'ok', $data = [])
{
	if ($data === null) {
        $data = (object)$data;
    }
    return ['code' => $code, 'msg' => $msg, 'data' => $data];
}

/**
 * 封装业务流程中遇到需要抛异常的处理
 * @author huxinlu
 * @param string $msg 异常消息
 * @param integer $code 异常代码（这里使用的是我们内部自定义的错误码，见项目的API文档）
 * @throws Exception
 */
function exception_throw( $msg = '', $code = 1001)
{
    throw new Exception($msg, $code);
}