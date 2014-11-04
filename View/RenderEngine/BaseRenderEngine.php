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
        $this->_view->getResponse()->getHeaders()->add('Content-type', 'text/json');
        if (defined('JSON_UNESCAPED_UNICODE')) {
            echo json_encode($this->_view->getCombinedVars()->getArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            echo json_encode($this->_view->getCombinedVars()->getArray());
        }
    }

    public function renderXml() {
        $this->_view->getResponse()->getHeaders()->add('Content-type', 'text/xml');
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><vars></vars>");
        $xml = $this->array2xml($this->_view->getCombinedVars(), $xml);
        if (!headers_sent()) {
            header('Content-type: text/xml; encoding=utf8;');
        }
        echo $xml->asXML();

    }

    /**
     * @param $array
     * @param \SimpleXMLElement $xmlNode
     * @return \SimpleXMLElement
     */
    private function array2xml($array, $xmlNode) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $this->array2xml($value, $xmlNode->addChild("$key"));
                } else {
                    $this->array2xml($value, $xmlNode->addChild("item$key"));
                }
            } else {
                $xmlNode->addChild("$key", htmlspecialchars("$value"));
            }
        }
        return $xmlNode;
    }

    public function renderConsole() {
        foreach ($this->_view->getCombinedVars()->getArray() as $key => $value) {
            echo $key . ': ' . $value . "\n";
        }
    }
}