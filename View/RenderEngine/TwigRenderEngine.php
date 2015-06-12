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
use Solve\Storage\ArrayStorage;
use Solve\TwigExtension;
use Solve\Utils\FSService;
use Solve\Utils\Inflector;

class TwigRenderEngine extends BaseRenderEngine {

    /**
     * @var \Twig_Environment
     */
    private $_twigEnvironment;

    public function configure() {
        parent::configure();
        \Twig_Autoloader::register();
        $loader = new \Twig_Loader_Filesystem();

        $applications = DC::getProjectConfig('applications');
        $loader->addPath(DC::getEnvironment()->getUserClassesRoot() . 'views/', 'Common');
        foreach ($applications as $appName => $info) {
            $loader->addPath(DC::getEnvironment()->getApplicationRoot() . ucfirst($appName) . '/Views/', ucfirst($appName));
        }


        $this->_twigEnvironment = new \Twig_Environment($loader, array(
            'cache' => DC::getEnvironment()->getTmpRoot() . 'templates/' . DC::getApplication()->getName() . '/',
            'debug' => true,
        ));
        if (class_exists('TwigExtension')) {
            $this->_twigEnvironment->addExtension(new TwigExtension());
        }

        $fs = new FSService();

        //DC::getAutoloader()->registerSharedPath(DC::getEnvironment()->getUserClassesRoot() . 'helpers');
        //if (($files = $fs->in(DC::getEnvironment()->getUserClassesRoot() . 'helpers')->find('*Block.php'))) {
        //    foreach ($files as $file) {
        //        $this->_slot->registerBlock(Inflector::underscore(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1, -9)), '\\');
        //    }
        //}
    }

    public function fetchHtml($vars = array(), $templateName) {
        $currentAppName = DC::getApplication()->getName();
        $template       = $this->_twigEnvironment->loadTemplate('@' . ucfirst($currentAppName) . '/' . $templateName . '.html.twig');
        if (!empty($vars) && is_object($vars) && $vars instanceof ArrayStorage) {
            $vars = $vars->getArray();
        }
        $vars['app'] = DC::getApplication();
        return $template->render($vars);
    }
}