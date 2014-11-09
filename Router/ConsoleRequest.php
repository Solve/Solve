<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/6/14 7:26 PM
 */

namespace Solve\Router;


use Solve\EventDispatcher\BaseEvent;
use Solve\Http\Request;

class ConsoleRequest {

    public function onRequestBuild(BaseEvent $event) {
        /**
         * @var Request $request
         */
        $request = $event->getParameters();
        $params  = array_splice($_SERVER['argv'], 1);
        $uri = str_replace(':', '/', array_shift($params));
        if (empty($uri)) $uri = '/';

        $request->setUri($uri);
        $index = 0;
        foreach ($params as $param) {
            $m = array();
            preg_match('#--(?P<key>[-\.\w\d]+)(\s?=\s?(?P<value>.+))?#is', $param, $m);
            if (!empty($m)) {
                $request->setVar($m['key'], array_key_exists('value', $m) ? $m['value'] : true);
            } else {
                $request->getVars()->setDeepValue('params/'.$index++, trim($param));
            }
        }
    }

    public function getEventListeners() {
        $events = array(
            'route.buildRequest' => array(
                'listener'   => array($this, 'onRequestBuild'),
                'parameters' => array()
            )
        );

        return $events;
    }
}