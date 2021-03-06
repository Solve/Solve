<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 27.10.14 18:24
 */

namespace Solve\Tests;

require_once 'include.php';

use Solve\Kernel\DC;
use Solve\Kernel\Kernel;
use Solve\Utils\FSService;

class KernelTest extends \PHPUnit_Framework_TestCase {

    public function testBasic() {
        $this->buildTestStructure();

        $kernel = Kernel::getMainInstance();
        $env = $kernel->getEnvironment();
        $this->assertEquals(realpath(__DIR__ . '/../../../') . '/', $env->getProjectRoot(), 'root detected correctly');

        $kernel->getEnvironment()->setProjectRoot(realpath(__DIR__ . '/project/') . '/', true);
        $this->assertEquals('Test project', DC::getProjectConfig('name'));

        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['HTTP_HOST'] = 'test.com';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['DOCUMENT_ROOT'] = $env->getWebRoot();
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        $kernel->run();
    }

    public function buildTestStructure() {
        FSService::makeWritable(array(
            __DIR__ . '/project/app/Frontend/Controllers',
            __DIR__ . '/project/app/Frontend/Views',
            __DIR__ . '/project/app/Admin',
            __DIR__ . '/project/src/classes',
            __DIR__ . '/project/src/db',
            __DIR__ . '/project/src/helpers',
            __DIR__ . '/project/src/libs',
            __DIR__ . '/project/tmp/log',
            __DIR__ . '/project/tmp/cache',
        ));

        $pdo = new \PDO('mysql:host=127.0.0.1', 'root', 'root');
        $pdo->query('create database if not exists test_project_db');

    }

}
