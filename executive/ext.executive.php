<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

use BuzzingPixel\Executive\Controller\ConsoleController;
use BuzzingPixel\Executive\Service\ConsoleService;

/**
 * Class Executive_ext
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
// @codingStandardsIgnoreStart
class Executive_ext
// @codingStandardsIgnoreEnd
{
    /** @var string $version */
    public $version = EXECUTIVE_VER;

    /** @var array $settings */
    public $settings = array();

    /**
     * Executive_ext constructor
     * @param mixed $settings
     */
    public function __construct($settings = '')
    {
        $this->settings = is_array($settings) ? $settings : array();
        ee()->extensions->s_cache['Executive_ext'] = null;
    }

    /**
     * session_start
     */
    // @codingStandardsIgnoreStart
    public function sessions_start() // @codingStandardsIgnoreEnd
    {
        // Check for console request
        if (! defined('REQ') || REQ !== 'CONSOLE') {
            return;
        }

        /** @var \EE_Config $configService */
        $configService = ee()->config;
        $configService->set_item('disable_csrf_protection', 'y');


        /*
         *  Custom errors
         */

        // Set error reporting
        ini_set('display_errors', 'On');
        ini_set('html_errors', 0);
        error_reporting(-1);

        /**
         * Shutdown handler
         * @return bool|mixed
         */
        function shutdownHandler()
        {
            if (@is_array($error = @error_get_last())) {
                return(@call_user_func_array('errorHandler', $error));
            }

            return true ;
        }

        register_shutdown_function('shutdownHandler');

        /**
         * Error handler
         * @param $type
         * @param $message
         * @param $file
         * @param $line
         */
        function errorHandler($type, $message, $file, $line)
        {
            if (! error_reporting()) {
                return;
            }

            $errors = array(
                0x0001 => 'E_ERROR',
                0x0002 => 'E_WARNING',
                0x0004 => 'E_PARSE',
                0x0008 => 'E_NOTICE',
                0x0010 => 'E_CORE_ERROR',
                0x0020 => 'E_CORE_WARNING',
                0x0040 => 'E_COMPILE_ERROR',
                0x0080 => 'E_COMPILE_WARNING',
                0x0100 => 'E_USER_ERROR',
                0x0200 => 'E_USER_WARNING',
                0x0400 => 'E_USER_NOTICE',
                0x0800 => 'E_STRICT',
                0x1000 => 'E_RECOVERABLE_ERROR',
                0x2000 => 'E_DEPRECATED',
                0x4000 => 'E_USER_DEPRECATED'
            );

            if (! @is_string($name = @array_search($type, @array_flip($errors)))) {
                $name = 'E_UNKNOWN';
            }

            /** @var ConsoleService $consoleService */
            $consoleService = ee('executive:ConsoleService');

            $errorEncountered = lang('followingErrorEncountered');

            $consoleService->writeLn("<bold>{$errorEncountered}</bold>", 'red');
            $consoleService->writeLn("{$name}: {$message}", 'red');
            $consoleService->writeLn("File: {$file}");
            $consoleService->writeLn("Line: {$line}");
            $consoleService->writeLn('');
        }

        set_error_handler('errorHandler');
    }

    /**
     * core_boot
     * @throws \Exception
     */
    // @codingStandardsIgnoreStart
    public function core_boot() // @codingStandardsIgnoreEnd
    {
        // Check for console request
        if (! defined('REQ') || REQ !== 'CONSOLE') {
            return;
        }

        // Get the console controller
        /** @var ConsoleController $consoleController */
        $consoleController = ee('executive:ConsoleController');

        // Run the console controller
        $consoleController->runConsoleRequest();

        // Make sure we exit here
        exit;
    }

    /**
     * User extension routing
     */
    public function userExtensionRouting()
    {
        $class = $this->settings['class'];
        $method = $this->settings['method'];

        call_user_func_array(
            array(
                new $class(),
                $method,
            ),
            func_get_args()
        );
    }
}
