<?php

namespace weblement\yii2\behaviors;

use yii;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\Html;
use Closure;

class LinkableBehavior extends Behavior
{
    public $route = null;
    public $defaultAction = 'view';
    public $defaultParams = [];
    public $hotlinkTextAttr = 'name';

    public function getUrlRoute($action = null, array $params = [])
    {
        $route = strtr('{route}/{action}', [
            '{route}' => $this->getParsedRoute(),
            '{action}' => !empty($action) ? $action : $this->defaultAction,
        ]);

        return ArrayHelper::merge([$route], $this->parsedParams, $params);
    }

    public function getWebUrl($action = null, array $params = [], $scheme = false)
    {
        return Url::to($this->getUrlRoute($action, $params), $scheme);
    }

    public function getHotlink($action = null, $params = [], $options = [])
    {
        return Html::a($this->owner->{$this->hotlinkTextAttr}, $this->getUrlRoute($action, $params), $options);
    }

    protected function getParsedRoute()
    {
        return strtr('/{route}', [
            '{route}' => is_null($this->route) ? strtolower(Inflector::pluralize(StringHelper::baseName($this->owner->className()))) : $this->route,
        ]);
    }

    protected function getParsedParams()
    {
        $params = [];

        foreach($this->defaultParams as $key => $value)
        {
            if($value instanceof \Closure) {
                $params[$key] = $value($this->owner);
            }
            else {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}