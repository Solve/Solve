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
use Solve\Slot\Slot;
use Solve\Utils\Inflector;

class SlotRenderEngine extends BaseRenderEngine {

    /**
     * @var Slot
     */
    private $_slot;

    public function configure() {
        parent::configure();
        $this->_slot = new Slot();
        $this->_slot->setTemplateDir($this->_view->getTemplatesPath());
        $this->_slot->setCompileDir(DC::getEnvironment()->getTmpRoot() . 'templates/' . DC::getApplication()->getName() . '/');
        $this->detectViewTemplate();
    }
    
    public function renderHtml() {
        $template = $this->_view->getTemplateName() . '.slot';

        if (($layout = $this->_view->getLayoutTemplate())) {
            $this->_view->setVar('innerContent', $this->_slot->fetchTemplate($template));
            $template = $layout  . '.slot';
        }
        echo $this->_slot->fetchTemplate($template, $this->_view->getVars());
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