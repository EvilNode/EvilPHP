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
 * EvilPHP\MVC\Route
 */

namespace EvilPHP\MVC {

    /**
     * Class Route
     * @package EvilPHP\MVC
     */
    class Route
    {
        /**
         * @var string The controller class name
         */
        public $controller;
        /**
         * @var string The named action within the controller
         */
        public $action;

        /**
         * @param string $controller
         * @param string $action
         */
        public function __construct($controller = 'index', $action = 'index')
        {
            $this->controller = $controller;
            $this->action = $action;
        }
    }
}