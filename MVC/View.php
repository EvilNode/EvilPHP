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
 * EvilPHP\MVC\View
 */
namespace EvilPHP\MVC {
    /**
     * Class View
     * @package EvilPHP\MVC
     */
    class View
    {
        /**
         * @var string
         */
        public static $extension = 'php';
        /**
         * @var array
         */
        private $_internals;

        /**
         *
         */
        public function __construct()
        {
            $this->_internals = array();
        }

        /**
         * @param $htmlFile
         */
        public function renderHTMLFile($htmlFile)
        {
            if (file_exists($htmlFile)) {
                /** @noinspection PhpIncludeInspection */
                include $htmlFile;
            }
        }

        /**
         * @param $path
         */
        public function render($path)
        {
            if (file_exists($path)) {
                /** @noinspection PhpIncludeInspection */
                include $path;
            }
        }

        /**
         * Magic getter
         * @param $key
         * @return null
         */
        public function __get($key)
        {
            return isset($key{0}, $this->_internals[$key]) ? $this->_internals[$key] : null;
        }

        /**
         * Magic setter.  Will unset anything if the passed value is null.
         * @param $key
         * @param $val
         */
        public function __set($key, $val)
        {
            if (isset($key{0}, $val)) {
                $this->_internals[$key] = $val;
            } elseif (isset($this->_internals[$key])) {
                unset($this->_internals[$key]);
            }
        }

        /**
         * @param $key
         * @return bool
         */
        public function __isset($key)
        {
            return isset($key{0}, $this->_internals[$key]);
        }

        /**
         * Returns the internals array to be rendered in other templating setups such as Twig
         * @return array
         */
        public function declaredProperties()
        {
            return $this->_internals;
        }
    }
}