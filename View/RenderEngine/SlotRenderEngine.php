<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 5:45 PM
 */

namespace Solve\View\RenderEngine;

use Solve\Kernel\DC;
use Solve\Utils\Inflector;

class SlotRenderEngine extends BaseRenderEngine {

    public function configure() {
        parent::configure();
        $this->detectViewTemplate();
    }
    
    public function render() {
        echo file_get_contents($this->_view->getTemplatesPath() . $this->_view->getTemplateName() . '.slot');
    }

    public function detectViewTemplate() {
        $route = DC::getApplication()->getRoute();
        $folder = Inflector::slugify(substr($route->getControllerName(), 0, -10));
        $action = Inflector::slugify(substr($route->getActionName(), 0, -6));

        if (is_file($this->_view->getTemplatesPath() . $folder . '/' . $action . '.slot')) {
            $this->_view->setTemplateName($folder . '/' . $action);
        } elseif (is_file($this->_view->getTemplatesPath() . $action . '.slot')) {
            $this->_view->setTemplateName($action);
        } else {
            throw new \Exception('Cannot detect template:' . $folder . '/' . $action);
        }
    }
}