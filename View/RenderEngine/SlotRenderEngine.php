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
use Solve\Utils\FSService;
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
        $fs = new FSService();

        DC::getAutoloader()->registerSharedPath(DC::getEnvironment()->getUserClassesRoot() . 'helpers');
        if (($files = $fs->in(DC::getEnvironment()->getUserClassesRoot() . 'helpers')->find('*Block.php'))) {
            foreach ($files as $file) {
                $this->_slot->registerBlock(strtolower(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1, -9)), '\\');
            }
        }
    }

    public function fetchHtml($vars = array(), $templateName) {
        $template = $templateName . '.slot';
        return $this->_slot->fetchTemplate($template, $vars);
    }
}