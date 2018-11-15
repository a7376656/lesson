<?php

/**
 * Created by PhpStorm.
 * User: zsm
 * Date: 2018/5/22
 * Time: 9:58
 */
class weChat
{
    private $Appid = "wx001fa6f201211aa9";
    private $AppSecret = "6a2beeefe5d23647d25668da0d5a30d9";
    private $redirect_uri = "http://127.0.0.1/yiyaotui/index/user/login";

    /**
     * 授权链接
     * @author zsm
     * @param $state
     * @return string
     */
    public function getAuthorizeUrl($state)
    {
        $redirect_uri = urldecode($this->redirect_uri);
        return "http://proxy.erdoudou.com?appid={$this->Appid}&redirect_uri={$redirect_uri}&response_type=code&device=pc&scope=snsapi_login&state={$state}";

    }

    /**
     * 获取accessToken
     * @author zsm
     * @param $code
     * @return bool|mixed
     */
    public function getAccessToken($code)
    {
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->Appid}&secret={$this->AppSecret}&code={$code}&grant_type=authorization_code";
        $token_data = $this->get($token_url);

        if ($token_data[0] == 200) {
            return json_decode($token_data[1], true);
        }
        return false;
    }

    /**
     * 获取用户信息
     * @author zsm
     * @param $accessToken
     * @param $openId
     * @return bool|mixed
     */
    public function getUserInfo($accessToken, $openId)
    {

        if (isset($accessToken) && isset($openId)) {
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
            $data = $this->get($url);

            if ($data[0] == 200) {
                return json_decode($data[1], true);
            }

        }
        return false;
    }

    /**
     * 发送请求
     * @author zsm
     * @param $url
     * @param array $headers
     * @return array
     */
    public function get($url, $headers = [])
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return [$http_code, $response];
    }
}