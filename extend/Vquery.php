<?php

class Vquery
{
    public $htmlContent;
    public $result = array();
    public $flag = false;

    function __construct($args)
    {
        if (is_array($args)) {
            $method = strtolower(@$args["method"]) == "post" ? "post" : "get";
            $this->htmlContent = $this->curl_request(@$args["url"], $method, @$args["header"], @$args["data"], @$args["timeout"]);
        } else {
            $this->htmlContent = $args;
        }
    }

    /**
     * [find description]
     * @param  [type] $str string
     * @return [type]      object
     */
    function find($str)
    {
        if (!$str) return $this;
        $backupResult = $this->result;
        $this->result = array();
        $findArray = explode(",", $str);
        foreach ($findArray as $value) {
            $arr = array();
            if (strstr($value, '$')) {
                $nowValue = explode('$', $value);
                $rz = "/\<($nowValue[0])\s?.*?\>.*?\<\/\\1\>/is";
                if (strstr($nowValue[1], "=")) {
                    $rz = "/\<($nowValue[0])\s?[^\<\\1]*?$nowValue[1].*?\>.*?\<\/\\1\>/is";
                }
            } else if (strstr($value, '=')) {
                $nowValue = explode(':', $value);
                $rz = "/\<([a-zA-Z]+)\s?[^\<\\1]*?$nowValue[0].*?\>.*?\<\/\\1\>/is";
            } else {
                $nowValue = explode(':', $value);
                $rz = "/\<($nowValue[0])\s?.*?\>.*?\<\/\\1\>/is";
            }
            if (@$backupResult[0] && $this->flag) {
                foreach ($backupResult as $val) {
                    if (!is_array($val)) continue;
                    foreach ($val as $vv) {
                        preg_match_all($rz, $vv, $arr);
                        if (strstr($value, ":")) {
                            $temp = explode(":", $value);
                            $number = @$temp[1] ? @$temp[1] : 0;
                            $number = is_numeric($number) ? $number : 0;
                            $this->result[] = array($arr[0][$number]);
                        } else {
                            $this->result[] = $arr[0];
                        }
                    }
                }
            } else {
                preg_match_all($rz, $this->htmlContent, $arr);
                if (strstr($value, ":")) {
                    $temp = explode(":", $value);
                    $number = @$temp[1] ? @$temp[1] : 0;
                    $number = is_numeric($number) ? $number : 0;
                    $this->result[] = array($arr[0][$number]);
                } else {
                    $this->result[] = $arr[0];
                }
            }

        }
        $this->flag = true;
        return $this;
    }

    /**
     * [deal description]
     * @return [type] return dealArray
     */
    function deal()
    {
        $resArray = array();
        foreach ($this->result as $value) @$value[0] && $temp[] = $value;
        return $resArray[] = $temp;
    }

    /**
     * [attr description]
     * @param  [type]  $attr string
     * @param  boolean $rz internal call
     * @return [type]        return result
     */
    function attr($attr, $rz = false)
    {
        if (@!$this->result[0]) return $this;
        $backupResult = $this->result;
        $this->result = array();
        foreach ($backupResult as $value) {
            if (!is_array($value)) continue;
            foreach ($value as $val) {
                !$rz && $rz = "/$attr=(\"|\')(.*?)(\'|\")/is";
                preg_match($rz, $val, $arr);
                @$arr[2] && $temp[] = $arr[2];
            }
        }
        $this->result[] = array_merge($this->result, $temp);
        return $this->result;
    }

    /**
     * [html description]
     * @return [type] return innerHTML
     */
    function html()
    {
        $rz = "/\<([a-zA-Z]+)\s?[^\<\\1]*?\>(.*?)\<\/\\1\>/is";
        return $this->attr("self", $rz);
    }

    /**
     * [text description]
     * @return [type] return innerText
     */
    function text()
    {
        $tempArray = $this->html();
        foreach ($tempArray as $key => $value)
            $tempArray[$key] = preg_replace("/\<.*?\>|\<.*?\>/", '', $value);
        return $tempArray;
    }

    /**
     * [attr description]
     * @param  [type]  $url     url
     * @param  [type]  $method  post or get
     * @param  [type]  $data    request data
     * @param  [type]  $header  request header
     * @param  [type]  $timeout timeout
     * @return [type]  return   result
     */
    function curl_request($url, $method, $header, $data, $timeout = 0)
    {
        if (!$url) return false;
        $urlInfo = $this->parseUrl($url);
        if ($method == "get") {
            $conn = curl_init($url);
            curl_setopt($conn, CURLOPT_HEADER, 0);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($conn, CURLOPT_HTTPHEADER, $header);
            $content = curl_exec($conn);
            curl_close($conn);
        } elseif ($method == "post") {
            $conn = curl_init($url);
            $data && $data = $this->formatUrl($data);
            curl_setopt($conn, CURLOPT_HEADER, 0);
            curl_setopt($conn, CURLOPT_POST, true);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $timeout);
            $data && curl_setopt($conn, CURLOPT_POSTFIELDS, $data);
            curl_setopt($conn, CURLOPT_HTTPHEADER, $header);
            $content = curl_exec($conn);
            curl_close($conn);
        }
        return $content;
    }

    /**
     * [attr description]
     * @param  [type]  $url url
     * @return [type]  return result(array)
     */
    function parseUrl($url)
    {
        $username = "";
        $password = "";
        $scheme = @explode("//", $url)[0] ? explode(":", $url)[0] : "http";
        $port = @preg_match_all("/.*?\:(\d+).*?/", $url, $matches) ? $matches[1][0] : "80";
        $path = @preg_match_all("/\/.*?\/.*?\/{1,}.*?/", @explode("//", $url)[1], $pathTmp) ? $pathTmp[0][0] : "/";
        $hash = @explode("#", $url)[1] ? @explode("#", $url)[1] : "";
        $queryString = @explode("?", $url)[1] ? @explode("?", $url)[1] : "";
        $filename = @preg_match_all("/.*?\/([^\/]*?)\?.*?/", @explode("//", $url)[1], $matches) ? $matches[1][0] : preg_match("/.*?(\/.*)/", explode("//", $url)[1], $matches) ? $matches[1] : "/";
        if (stristr(explode("?", $url)[0], "@")) {
            $username = @explode(":", @explode("//", $url)[1])[0] ? @explode(":", @explode("//", $url)[1])[0] : "";
            $password = @explode(":", @explode("@", @explode("//", $url)[1])[0])[1] ? @explode(":", @explode("@", @explode("//", $url)[1])[0])[1] : "";
            $host = @explode(":", @explode("/", @explode('@', @explode("?", $url)[0])[1])[0])[0];
        } else $host = explode(":", preg_match("/.*?\/\/([^\/]*+)/", $url, $matches) ? $matches[1] : "")[0];
        return array(
            "scheme" => $scheme,
            "host" => $host,
            "port" => $port,
            "username" => $username,
            "password" => $password,
            "path" => $path,
            "filename" => $filename,
            "queryString" => $queryString,
            "hash" => $hash
        );
    }

    /**
     * [attr description]
     * @param  [type]  $ar array or string
     * @return [type]  return result(array or string)
     */
    function formatUrl($arg)
    {
        if (is_array($arg)) {
            $str = '';
            foreach ($arg as $key => $value) $str .= $key . "=" . $value . "&";
            return rtrim($str, "&");
        } else {
            $array = array();
            $arg = explode("&", $arg);
            foreach ($arg as $value) $value && $array = array_merge($array, array(explode("=", $value)[0] => explode("=", $value)[1]));
            return $array;
        }
    }

    /**
     * [attr description]
     * @param  [type]  $flag default false
     * @return [type]  return htmlcontont or echo htmlcontent
     */
    function getHtmlContent($flag = false)
    {
        if ($flag) return $this->htmlContent;
        echo "<pre>";
        echo htmlspecialchars($this->htmlContent);
        echo "</pre>";
    }
}
/**
 * [$content description] example 1
 * @var [type]
 * $content=file_get_contents("http://www.baidu.com");
 * $vq=new Vquery($content);
 * $vq=$vq->find('a');
 * var_dump($vq->html());
 * [$content description] example 2 get
 * @var [type]
 * $arr=array("url"=>"http://www.baidu.com");
 * $vq=new Vquery($arr);
 * $vq=$vq->find('a');
 * var_dump($vq->html());
 * [$content description] example 2 post
 * @var [type]
 * $arr=array(
 * "url"=>"http://www.baidu.com",
 * "method"=>"post",
 * "data"=>array("username"=>"admin","password"=>"admin")
 * );
 * $vq=new Vquery($arr);
 * $vq=$vq->find('a');
 * var_dump($vq->html());
 * more information please visit vquery.com/vquery_document.html
 */
