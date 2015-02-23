<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 07.11.14 16:37
 */

namespace SolveConsole\Controllers;


use Solve\Config\ConfigService;
use Solve\Controller\ConsoleController;
use Solve\Database\QC;
use Solve\Environment\Environment;
use Solve\Kernel\DC;
use Solve\Utils\FSService;

/**
 * Class EnvController
 * @package SolveConsole\Controllers
 *
 * Class EnvController is a database console operator
 * @help helps with environment operations
 *
 * @version 1.0
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 */
class EnvController extends ConsoleController {


    /**
     * Add new environment
     */
    public function addAction() {
        $name = $this->ask('Enter the <underline>profile name</underline> to add', 'local');
        if (!ConfigService::createEnvironment($name)) {
            $this->error($name . ' is already exists');
        }

        $this->notify('Environment created – ' . $name);
    }

    /**
     * Shows all available environments
     */
    public function listAction() {
        $this->writeln("Here is a list of the available environments:");
        foreach(ConfigService::getAllEnvironments() as $env) {
            $this->writeln(" - ".$env);
        }
    }

    /**
     * Set active environment
     */
    public function setAction() {
        if (!ConfigService::isEnvironmentExists('local')) {
            if ($this->confirm('We need to create local environment to save your params. Ok', true)) {
                ConfigService::createEnvironment('local');
            };
        }
        $name = $this->ask('Enter name of environment');
        $config = ConfigService::getConfig('project', 'local');
        $config->set('activeEnvironment', 'local', 'local');
        $config->save();
        $this->notify('- You set active environment to <bold>' . $name . '</bold>');
    }

}