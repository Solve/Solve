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
        if (!empty($headers['Session-Token']) && strlen($headers['Session-Token']) == strlen('mapnsbmr9vvdn9ctgteiepuj90')) {
            session_id($headers['Session-Token']);
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