<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 5:45 PM
 */

namespace Solve\View\RenderEngine;

use Solve\View\View;

class BaseRenderEngine {

    /**
     * @var View
     */
    protected $_view;

    public function __construct($view) {
        $this->_view = $view;
    }

    public function configure() {
    }

    public function renderHtml() {
        echo "Default HTML renderer";
    }

    public function renderJson() {
        if (!headers_sent()) {
            header('Content-type: text/json; encoding=utf8;');
        }
        if (defined('JSON_UNESCAPED_UNICODE')) {
            echo json_encode($this->_view->getVars()->getArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            echo json_encode($this->_view->getVars()->getArray());
        }
    }

    public function renderXml() {
        echo "Default XML renderer";
    }

    public function renderConsole() {
        foreach($this->_view->getVars()->getArray() as $key=>$value) {
            echo $key . ': ' . $value . "\n";
        }
    }
}