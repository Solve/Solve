<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 08.11.14 05:15
 */

namespace SolveConsole\Controllers;

use Solve\Controller\ConsoleController;
use Solve\Database\Models\DBOperator;
use Solve\Database\Models\ModelOperator;
use Solve\Kernel\DC;
use Solve\Utils\FSService;
use Solve\Utils\Inflector;

/**
 * Class GenController
 * @package SolveConsole\Controllers
 *
 * Class GenController is a console generator
 * @help helps you generate a lot of useful code
 *
 * @version 1.0
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 */
class GenController extends ConsoleController {

    /**
     * Generates default database model structure
     */
    public function modelAction() {
        $name = ucfirst(Inflector::camelize($this->getFirstParamOrAsk('Enter model name')));
        $path = DC::getEnvironment()->getUserClassesRoot() . 'db/';
        $mo   = ModelOperator::getInstance($path);
        if ($mo->getModelStructure($name)) {
            $this->warning('model ' . $name . ' is already exists', '~ model exists, skipping:');
            return true;
        }
        $mo->generateBasicStructure($name);
        $mo->saveModelStructure($name);
        $this->notify($path . 'structure/' . $name . '.yml', '+ model created');
    }

    /**
     * Generates controller and view structure for it
     */
    public function cvAction() {
        $params         = array();
        $params['name'] = ucfirst($this->getFirstParamOrAsk('Enter controller\'s name'));
        $params['app']  = ucfirst($this->ask('App to generate controller', 'Frontend'));

        $this->createCV($params);
    }

    /**
     * Generates new application
     * @throws \Exception
     */
    public function appAction() {
        $name = ucfirst($this->getFirstParamOrAsk('Enter application\'s name'));;
        FSService::makeWritable(DC::getEnvironment()->getApplicationRoot() . $name);
        $this->createCV(array(
            'name' => 'Index',
            'app'  => $name,
        ));
        $appPath = DC::getEnvironment()->getApplicationRoot() . $name . '/';
        $this->safeCreateFromTemplate($appPath . 'Views/_layout.slot', '_view_layout', array('app'=>$name));
        $this->safeCreateFromTemplate($appPath . 'config.yml', '_app_config', array('app'=>$name));
        $config = DC::getProjectConfig();
        if (!$config->has('applications/'.strtolower($name))) {
            $config->set('applications/'.strtolower($name), strtolower($name));
            $this->notify('added information to project.yml', '+ config');
            $config->save();
        }
    }

    protected function createCV($params) {
        $appPath = DC::getEnvironment()->getApplicationRoot() . $params['app'];
        FSService::makeWritable($appPath . '/Views/' . strtolower($params['name']));
        FSService::makeWritable($appPath . '/Controllers/');

        $this->safeCreateFromTemplate($appPath . '/Controllers/' . $params['name'] . 'Controller.php',
            '_controller',
            $params);

        $this->safeCreateFromTemplate($appPath . '/Views/' . strtolower($params['name']) . '/default.slot',
            '_view_default',
            $params);
    }

    protected function safeCreateFromTemplate($destination, $template, $vars = array()) {
        $templatePath = __DIR__ . '/../_templates/' . $template . '.dist';

        $this->safeCreateFile($destination, $this->getContentFromTemplate($templatePath, $vars));
    }

    protected function safeCreateFile($destination, $content) {
        if (is_file($destination)) {
            $this->warning($destination, '~ file exists, skipping:');
            return false;
        }
        file_put_contents($destination, $content);
        $this->notify($destination, '+ file created:');
    }

    protected function getContentFromTemplate($templatePath, $vars = array()) {
        if (!is_file($templatePath)) throw new \Exception('No template found in ' . $templatePath);

        $content = file_get_contents($templatePath);
        foreach ($vars as $key => $value) {
            $vars['__' . strtoupper($key) . '__'] = $value;
            unset($vars[$key]);
        }
        $vars['__DATE__']    = date('Y-m-d H:i:s');
        $vars['__PROJECT__'] = DC::getProjectConfig('name');

        return str_replace(array_keys($vars), $vars, $content);
    }
}