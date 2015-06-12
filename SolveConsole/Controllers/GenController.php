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
use Solve\Security\SecurityService;
use Solve\Storage\ArrayStorage;
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
    public function entityAction() {
        $name = ucfirst(Inflector::camelize($this->getFirstParamOrAsk('Enter model name')));
        //$name = 'Entities\Project';
        $path = DC::getEnvironment()->getEntitiesRoot();
        $mo   = ModelOperator::getInstance($path);
        if ($mo->getModelStructure($name)) {
            $this->warning('entity ' . $name . ' is already exist', '~ model exist, skipping:');
            return;
        }


        $structure = $mo->generateBasicStructure($name);
        $structure = $this->askForFieldsAndUpdateStructure($name, $structure);
        if ($this->confirm('Are you confirming generation', true)) {
            $mo->setStructureForModel($name, $structure);
            $mo->saveModelStructure($name);
            $this->notify($mo->getStructurePathForModel($name)['path'], '+ model created');
        }
    }

    public function fieldsAction() {
        $name = ucfirst(Inflector::camelize($this->getFirstParamOrAsk('Enter model name')));
        $path = DC::getEnvironment()->getEntitiesRoot();
        $mo   = ModelOperator::getInstance($path);
        if (!($structure = $mo->getModelStructure($name))) {
            $this->warning(null, 'entity ' . $name . ' does not exist');
            return;
        }
        $structure = $this->askForFieldsAndUpdateStructure($name, $structure);
        if ($this->confirm('Are you confirming generation', true)) {
            $mo->setStructureForModel($name, $structure);
            $mo->saveModelStructure($name);
            $this->notify($mo->getStructurePathForModel($name)['path'], '~ model updated');
        }
    }

    public function userAction() {
        $name = Inflector::camelize($this->getFirstParamOrAsk('Enter user name'));
        $password = Inflector::camelize($this->getFirstParamOrAsk('Enter user password'));
        $provider = new ArrayStorage(DC::getSecurityConfig('security/providers/users'));
        $class = $provider->get('class');
        $object = new $class();
        $usernameMethod = 'set' . ucfirst($provider->get('username', 'login'));
        $passwordMethod = 'set' . ucfirst($provider->get('password', 'password'));
        $object->$usernameMethod($name);
        $object->$passwordMethod(SecurityService::encodePassword($password, 'md5'));
        $object->save();
        $this->notify(null, 'User created');
    }

    private function askForFieldsAndUpdateStructure($name, $structure) {
        $fields = array();
        $this->notify(null, 'You can new enter fields for the '. $name . ' Entity (<light_gray> press [Enter] to finish </light_gray>)');
        while ($answer = $this->ask('Enter new field name', null, true)) {
            $type = $this->ask('Enter type of ' . $answer . ' (string, integer, boolean, array)');
            $fields[$answer] = $type;
        }
        foreach($fields as $fieldName => $fieldType) {
            $structure['columns'][$fieldName] = array(
                'type' => $fieldType
            );
        }
        return $structure;
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
     * @changed
     */
    public function configAction() {
        $projectName = DC::getProjectConfig('name');
        $config = <<<TEXT
<VirtualHost *:80>
    DocumentRoot "/work/www/{$projectName}/web"
    ServerName {$projectName}.local
    ServerAlias www.{$projectName}.local
</VirtualHost>

TEXT;
        $hosts = <<<TEXT
127.0.0.1   {$projectName}.local
::1         {$projectName}.local
TEXT;

        $this->notify('Here is your generated apache config:');
        $this->warning(null, $config);
        $this->notify('and hosts line:');
        $this->warning(null, $hosts);

        if ($this->confirm('Would you like to add hosts info to /etc/hosts?')) {
            if (is_writable("/etc/hosts")) {
                @copy('/etc/hosts', '/etc/~hosts');
                $f = fopen("/etc/hosts", "a+");
                fputs($f, "\n\n#added by Solve\n".$hosts);
                fclose($f);
            } else {
                $this->error('File /etc/hosts is not writable, try run with sudo');
            }
            $this->notify(null, 'added.');
        } else {
            $this->warning(null, 'skipped.');
        }
    }

    /**
     * Generates Slot template engine helper/block
     */
    public function helperAction() {
        $name = ucfirst($this->getFirstParamOrAsk('Enter helper\'s name'));;
        $path = DC::getEnvironment()->getUserClassesRoot() . 'helpers/';
        FSService::makeWritable($path);
        $this->safeCreateFromTemplate($path . $name . 'Block.php', '_helper');
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