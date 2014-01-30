<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Steve High
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 * EvilNode\MVC\Router
 */

namespace EvilNode\MVC {
    /**
     * MVC namespace helper function to convert an action
     * to an action function name
     * @param $name
     * @return string
     */
    function actionName($name)
    {
        return strtolower($name) . 'Action';
    }

    /**
     * MVC namespace helper function to convert a controller name
     * to its class name
     * @param $name
     * @return string
     */
    function controllerName($name)
    {
        $parts = explode('\\', $name);
        $name = ucfirst(strtolower(array_pop($parts))) . 'Controller';
        $parts[] = $name;
        return implode('\\', $parts);
    }

    /**
     * Class Router
     * @package EPHP\MVC
     */
    class Router
    {
        /**
         * @var \EvilNode\MVC\Router Router Singleton
         */
        private static $_sharedRouter = null;
        /**
         * @var string Path to controllers
         */
        private static $_controllerPath = null;
        /**
         * @var string Path to views
         */
        private static $_viewPath = null;
        /**
         * @var \stdClass Route map
         */
        private $_routerTable;
        /**
         * @var array List of captured URL parameters
         */
        private $_captures;
        /**
         * @var bool Flag to identify that the router mapping was set up
         */
        private $_hasRouterTable;

        /**
         *  Constructor
         */
        private function __construct()
        {
            ob_start();
            //  start the buffer. flush at the end, or not
            register_shutdown_function(function () {
                if (defined('EPHP_SUPPRESS_BUFFER')) {
                    ob_end_clean();
                } else {
                    ob_end_flush();
                }
            });
            $this->_routerTable = new \stdClass();
            $this->_captures = array();
            $this->_hasRouterTable = false;
        }

        /**
         * Shared Router Singleton
         * @return \EvilNode\MVC\Router|null
         */
        public static function sharedRouter()
        {
            if (!isset(self::$_sharedRouter)) {
                self::$_sharedRouter = new Router();
            }
            return self::$_sharedRouter;
        }

        /**
         * Sets up controller and view paths
         * @param $controllerPath
         * @param $viewPath
         * @return void
         */
        public static function initializePaths($controllerPath, $viewPath)
        {
            self::$_controllerPath = $controllerPath;
            self::$_viewPath = $viewPath;
        }

        /**
         * Redirects through a header
         * @param $url
         */
        public static function redirect($url)
        {
            ob_clean();
            header("Location: {$url}", true, 302);
            exit();
        }

        /**
         * Globally accessible controller path
         * @return string|null
         */
        public static function controllerPath()
        {
            return self::$_controllerPath;
        }

        /**
         * Globally accessible view path
         * @return string|null
         */
        public static function viewPath()
        {
            return self::$_viewPath;
        }

        /**
         * Loads a serialized Router Map
         * @param string $map The serialized Router Map
         */
        public function loadRouterMap($map)
        {
            $this->_routerTable = unserialize($map);
        }

        /**
         * Loads predefined routes from an array of path=>Route matches
         * @param array $routes
         */
        public function loadRouterTable(array $routes)
        {
            //  @TODO pull this from a cache
            $method = $_SERVER['REQUEST_METHOD'];
            if (!isset($routes[$method])) {
                return;
            }
            $methodRoutes = $routes[$method];
            $this->_routerTable->$method = new \stdClass();
            $this->_hasRouterTable = true;
            foreach ($methodRoutes as $path => $route) {
                $components = explode('/', $path);
                $components = array_filter($components, 'strlen');
                if (count($components) === 0) {
                    $components[] = '/';
                }
                $root = & $this->_routerTable->$method;
                do {
                    $part = array_shift($components);
                    $root->$part = new \stdClass();
                    $root = & $root->$part;
                } while (count($components) > 0);
                $root = $route;
            }
        }

        /**
         * Performs the routing of the request
         * @throws MVCException
         * @internal param $path
         */
        public function dispatchRoute()
        {
            //  this will parse the route according to the router table
            $match = (true === $this->_hasRouterTable) ? $this->_matchingRegisteredRoute() : null;
            if (isset($match)) {
                $routeObj = $match;
            } else {
                // this will parse the route through a traditional MVC mechanism of
                // controller/action/k/v/k/v...
                $routeObj = $this->_routeFromRequest();
            }

            $controller = controllerName($routeObj->controller);
            if (!class_exists($controller, false)) {
                if (file_exists(self::$_controllerPath . DIRECTORY_SEPARATOR . $controller . '.php')) {
                    /** @noinspection PhpIncludeInspection */
                    include_once self::$_controllerPath . DIRECTORY_SEPARATOR . $controller . '.php';
                } else {
                    throw new MVCException("Controller not found: {$controller}");
                }
            }
            $controllerObj = new $controller();
            if (!($controllerObj instanceof Controller)) {
                throw new MVCException("{$controller} does not inherit from EvilNode\\MVC\\Controller");
            }
            $action = actionName($routeObj->action);

            if (!method_exists($controllerObj, $action)) {
                throw new MVCException("{$action} not implemented in {$controller}");
            }
            $controllerObj->viewBase = self::viewPath() . DIRECTORY_SEPARATOR . $routeObj->controller . DIRECTORY_SEPARATOR . $routeObj->action;
            $controllerObj->preLaunch();

            //  the cancellation can be set within the prelaunch, action, and postlaunch
            //  so we check after each state change
            if (!$controllerObj->cancelCurrentAction) {
                $controllerObj->$action($this->_captures);
                if (!$controllerObj->cancelCurrentAction) {
                    $controllerObj->postLaunch();
                    if (!($controllerObj->cancelCurrentAction || $controllerObj->suppressView)) {
                        $controllerObj->renderView();
                    }
                }
            }
        }

        /**
         * @return null
         */
        private function _matchingRegisteredRoute()
        {
            $components = array_filter(explode('/', $_SERVER['REQUEST_URI']), 'strlen');
            $step = $this->_routerTable->$_SERVER['REQUEST_METHOD'];
            $found = true;
            $stringSlug = '%s';
            $intSlug = '%d';
            do {
                $path = array_shift($components);
                if (isset($step->$path)) {
                    $step = $step->$path;
                } elseif (ctype_digit($path) && isset($step->$intSlug)) {
                    $step = $step->$intSlug;
                    $this->_captures[] = (int)$path;
                } elseif (isset($step->$stringSlug)) {
                    $step = $step->$stringSlug;
                    $this->_captures[] = $path;
                } else {
                    $found = false;
                    break;
                }
            } while (count($components) > 0);
            return $found ? $step : null;
        }

        /**
         * @return Route
         */
        private function _routeFromRequest()
        {
            //  this splits the query string up by the separator, then unsets empty or zero-length values,
            //  then filters out those empty values.
            //  All params are sent to the GET superglobal
            $d = explode('/', $_SERVER['REQUEST_URI']);
            $e = array();
            for ($i = 0; $j = count($d), $i < $j; $i++) {
                if (isset($d[$i]{0})) {
                    $e[] = $d[$i];
                }
            }
            $route = new Route();
            $cOFe = count($e);
            if (1 === $cOFe) {
                $route->controller = $e[0];
            } else if ($cOFe > 1) {
                $route->controller = array_shift($e);
                if (count($e) > 0) {
                    $route->action = array_shift($e);
                    if (count($e) > 1) {
                        $chunks = array_chunk($e, 2);
                        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
                            if (isset($chunks[$i][1])) {
                                $_GET[$chunks[$i][0]] = $chunks[$i][1];
                            }
                        }
                    }
                }
            } else {
                $route->controller = 'index';
            }
            return $route;
        }

        /**
         * @return array
         */
        public function capturedParameters()
        {
            return $this->_captures;
        }
    }
}