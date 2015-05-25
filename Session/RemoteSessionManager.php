<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 27.01.14 23:20
 */

namespace Solve\Session;


/**
 * Class RemoteSessionManager
 * @package Solve\Session
 *
 * @version 1.0
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 */
class RemoteSessionManager {

    public function onKernelBoot() {
        $headers = getallheaders();
        $sessionHeader = '';
        if (!empty($headers['Session-Token'])) {
            $sessionHeader = $headers['Session-Token'];
        } elseif (!empty($headers['session-token'])) {
            $sessionHeader = $headers['session-token'];
        }
        if (!empty($sessionHeader) && strlen($sessionHeader) == strlen('mapnsbmr9vvdn9ctgteiepuj90')) {
            session_id($sessionHeader);
        }
    }

    static public function getSessionToken() {
        return session_id();
    }

    public function getEventListeners() {
        return array(
            'kernel.boot'       => array(
                'listener' => array($this, 'onKernelBoot')
            )
        );
    }}

if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}