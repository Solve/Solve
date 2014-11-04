<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 5:45 PM
 */

namespace Solve\View\RenderEngine;

use Solve\Storage\ArrayStorage;
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

    public function fetchHtml($vars = array(), $templateName) {
        return "Default HTML renderer ".$templateName;
    }

    public function fetchJson($vars = array()) {
        if (is_object($vars) && $vars instanceof ArrayStorage) {
            $vars = $vars->getData();
        }
        $this->_view->getResponse()->getHeaders()->add('Content-type', 'text/json');
        if (defined('JSON_UNESCAPED_UNICODE')) {
            return json_encode($vars, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else {
            return json_encode($vars);
        }
    }

    public function fetchXml($vars = array()) {
        $this->_view->getResponse()->getHeaders()->add('Content-type', 'text/xml');
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\"?><vars></vars>");
        $xml = $this->array2xml($vars, $xml);
        if (!headers_sent()) {
            header('Content-type: text/xml; encoding=utf8;');
        }
        return $xml->asXML();
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

    public function fetchConsole($vars = array()) {
        $result = '';
        foreach ($vars as $key => $value) {
            $result .= $key . ': ' . $value . "\n";
        }
        return $result;
    }
}