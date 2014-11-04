<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 04.11.14 20:57
 */

namespace Solve\Kernel;


use Solve\Config\ConfigService;
use Solve\Database\DatabaseService;

class PackagesConfigurator {

    public function processConfigs() {
        if ($webRoot = DC::getProjectConfig('webRoot')) {
            DC::getEnvironment()->setWebRoot($webRoot);
        }
        $databaseConfig = DC::getDatabaseConfig();
        if (($profiles = $databaseConfig->get('profiles'))) {
            foreach($profiles as $profileName => $profileInfo) {
                DatabaseService::configProfile($profileInfo, $profileName);
            }
        }
    }

    public function onEnvironmentUpdate($params) {
        ConfigService::setConfigsPath(DC::getEnvironment()->getConfigRoot());
        ConfigService::loadAllConfigs();
        DC::getLogger()->setLogsPath(DC::getEnvironment()->getTmpRoot() . 'log');

//        var_dump($params);die();

    }

    public function getEventListeners() {
        var_dump(1);die();
        return array(
            'kernel.ready'  => array(
                'listener'   => array($this, 'processConfigs')
            ),
            'environment.update'    => array(
                'listener'=> array($this, 'onEnvironmentUpdate')
            )
        );
    }

}