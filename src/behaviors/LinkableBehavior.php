<?php

namespace weblement\yii2\behaviors;

use yii;
use yii\base\Behavior;
use yii\base\Model;
use yii\base\InvalidRouteException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\Html;
use Closure;

class LinkableBehavior extends Behavior
{
    private $_route = null;
    public $defaultAction = 'view';
    public $defaultParams = [];
    public $hotlinkTextAttr = 'name';
    private $_linkableParams = [];

    public $disableHotlink = false;

    public function getUrlRoute($action = null, array $params = [])
    {
        $route = strtr('{route}/{action}', [
            '{route}' => $this->getParsedRoute(),
            '{action}' => !empty($action) ? $action : $this->defaultAction,
        ]);
        
        return ArrayHelper::merge([$route], $this->parseParams($this->defaultParams), $params);
    }

    public function getUrlRouteTo(Model $model, $action = null)
    {
        if(!ArrayHelper::isIn($this->className(), ArrayHelper::getColumn($model->behaviors(), 'class'))) {
            throw new InvalidRouteException('The "LinkableBehavior" is not attached to the specified model');
        }

        return $this->getUrlRoute(strtr('{route}/{action}', [
            '{route}' => $model->route,
            '{action}' => $action ?? $model->defaultAction
        ]), $model->linkableParams);
    }

    public function getWebUrl($action = null, array $params = [], $scheme = false)
    {
        return Url::to($this->getUrlRoute($action, $params), $scheme);
    }

    public function getHotlink($action = null, $params = [], $options = [])
    {
        return $this->disableHotlink 
        ? Html::tag('div', $this->owner->{$this->hotlinkTextAttr}, $options)
        : Html::a($this->owner->{$this->hotlinkTextAttr}, $this->getUrlRoute($action, $params), $options);
    }

    public function getHotlinkTo(Model $model, $action = null, $options = [])
    {
        if(!ArrayHelper::isIn($this->className(), ArrayHelper::getColumn($model->behaviors(), 'class'))) {
            throw new InvalidRouteException('The "LinkableBehavior" is not attached to the specified model');
        }

        return $this->getHotlink(strtr('{route}/{action}', [
            '{route}' => $model->route,
            '{action}' => $action ?? $model->defaultAction
        ]), $model->linkableParams, $options);
    }

    public function getRoute()
    {
        return trim($this->_route, '/');
    }

    public function setRoute($route)
    {
        return $this->_route = $route;
    }

    public function getLinkableParams()
    {
        $parsedLinkableParams = $this->parseParams($this->_linkableParams);
        return !empty($parsedLinkableParams) ? $parsedLinkableParams : [strtolower(substr(StringHelper::baseName($this->owner->className()), 0, 1)) => $this->owner->id];
    }

    public function setLinkableParams(array $linkParams)
    {
        $this->_linkableParams = $linkParams;
    }

    protected function getParsedRoute()
    {
        return strtr('/{route}', [
            '{route}' => is_null($this->route) ? strtolower(Inflector::pluralize(StringHelper::baseName($this->owner->className()))) : $this->route,
        ]);
    }

    protected function parseParams(array $params)
    {
        $parsedParams = [];

        foreach($params as $key => $value)
        {
            if($value instanceof \Closure) {
                $parsedParams[$key] = $value($this->owner);
            }
            else {
                $parsedParams[$key] = $value;
            }
        }

        return $parsedParams;
    }
}