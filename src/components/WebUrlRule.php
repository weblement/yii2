<?php

namespace weblement\yii2\components;

use yii;
use yii\web\UrlRule;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class WebUrlRule extends UrlRule
{
    public $paramName = '_pi';

    public $autoEncodeParams = [];

    public function parseRequest($manager, $request)
    {
        if($parsedRequest = parent::parseRequest($manager, $request)) {
            list ($route, $params) = $parsedRequest;

            if($pi = $request->get($this->paramName)) {
                $pi = $this->urlDecode($pi);

                foreach ($pi as $key => &$value) {
                    $value = Json::decode($value);
                }

                $params = ArrayHelper::merge($params, $pi);
            }
            
            return [$route, $params];
        }

        return false;
    }

    public function createUrl($manager, $route, $params)
    {
        if (isset($params[$this->paramName]) && !is_array($params[$this->paramName])){
            $params[$this->paramName] = $this->urlDecode($params[$this->paramName]);

            foreach ($params[$this->paramName] as $key => &$value) {
                $value = Json::decode($value);
            }
        }

        foreach ($params as $key => $param) {
            if(in_array($key, $this->autoEncodeParams) && !is_null($param)) {
                $params[$this->paramName][$key] = $param;
                ArrayHelper::remove($params, $key);
            }
        }

        if (isset($params[$this->paramName]) && is_array($params[$this->paramName])) {
            foreach ($params[$this->paramName] as $key => &$value) {
                $value = Json::encode($value);
            }

            $params[$this->paramName] = $this->urlEncode($params[$this->paramName]);
        }
        
        return parent::createUrl($manager, $route, $params);
    }

    private function urlEncode(array $data)
    {
        $query = http_build_query($data);
        $query = base64_encode($query);
        $query = rawurlencode($query);

        return $query;
    }

    private function urlDecode($string)
    {
        $string = rawurldecode($string);
        $string = base64_decode($string);

        $data = [];
        parse_str($string, $data);
        
        return $data;
    }
}