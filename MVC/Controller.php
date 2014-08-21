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
 * EvilPHP\MVC\Controller
 */

namespace EvilPHP\MVC {
    use EvilPHP\Util\Registry;
    use EvilPHP\Util\Generic;

    /**
     * Class Controller
     * @package EvilPHP\MVC
     */
    abstract class Controller
    {

        /**
         * @var bool Flag to route requests through Twig
         */
        public static $TWIG = false;
        /**
         * @var View
         */
        public $view;
        /**
         * @var string
         */
        public $viewBase;
        /**
         * @var bool
         */
        public $suppressView;

        /**
         * @var bool
         */
        public $cancelCurrentAction;

        /**
         *
         */
        public function __construct()
        {
            $this->view = new View();
            $this->viewBase = Router::viewPath() . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR . 'index';
            $this->suppressView = false;
            $this->cancelCurrentAction = false;
        }

        /**
         * Forwards control to another action within the same or different controller.
         * @param string $action The named action to forward to
         * @param \EvilPHP\Util\Generic $obj
         * @param string $controller The controller class
         * @param bool $eraseBuffer Flag that causes the current buffer to be cleared
         * @param bool $cancelPreLaunch Flag to suppress action prelaunch.  Note that pre and post launch are not called when forwarding to the same controller
         * @param bool $cancelPostLaunch Flag to suppress action postLaunch.  Note that pre and post launch are not called when forwarding to the same controller
         * @throws MVCException
         * @internal param \EvilPHP\Util\Generic $args Any arguments to pass to the receiver action. Note that the arguments must be pulled in the receiver using func_get_args
         */
        public final function forward($action, Generic $obj = null, $controller = null, $eraseBuffer = false, $cancelPreLaunch = false, $cancelPostLaunch = false)
        {
            $controllerObj = null;
            if (!isset($controller)) {
                $controllerObj = $this;
            } else {
                $controllerClass = controllerName($controller);
                if (!class_exists($controllerClass)) {
                    $path = Router::controllerPath() . DIRECTORY_SEPARATOR . $controllerClass . '.php';
                    if (!file_exists($path)) {
                        throw new MVCException("Unable to load controller: {$controllerClass}");
                    }
                    /** @noinspection PhpIncludeInspection */
                    include_once $path;
                }
                $controllerObj = new $controllerClass();
            }
            $actionMethod = actionName($action);
            if (!method_exists($controllerObj, $actionMethod)) {
                $class = get_class($controllerObj);
                throw new MVCException("{$class} does not implement {$actionMethod}");
            }
            if ($eraseBuffer) {
                ob_clean();
            }
            if ($controllerObj !== $this) {
                $controllerObj->viewBase = Router::viewPath() . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action;
                $this->viewBase = null;
                if (!$cancelPreLaunch) {
                    $controllerObj->preLaunch();
                }
                if (!$controllerObj->cancelCurrentAction) {
                    $controllerObj->$actionMethod($obj);
                    if (!($controllerObj->cancelCurrentAction || $cancelPostLaunch)) {
                        $controllerObj->postLaunch();
                    }
                    if (!$controllerObj->cancelCurrentAction) {
                        $controllerObj->renderView();
                    }
                }
            } else {
                $this->viewBase = Router::viewPath() . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action;
                if (!$this->cancelCurrentAction) {
                    $this->$actionMethod($obj);
                    $this->renderView();
                }
            }
        }

        /**
         * @return mixed
         */
        public abstract function preLaunch();

        /**
         * @return mixed
         */
        public abstract function postLaunch();

        /**
         *
         */
        public function renderView()
        {
            if (self::$TWIG) {
                $twig = Registry::get('twig');
                //  pop the last two items from the view base
                $e = explode(DIRECTORY_SEPARATOR, $this->viewBase);
                $sub = array_slice($e, -2);
                $template = $twig->loadTemplate(implode(DIRECTORY_SEPARATOR, $sub) . '.twig');
                /** @noinspection PhpUndefinedMethodInspection */
                echo $template->render($this->view->declaredProperties());
            } else {

                if ($this->suppressView || !isset($this->viewBase)) {
                    return;
                }
                $this->view->render($this->viewBase . '.' . View::$extension);
            }
        }
    }
}