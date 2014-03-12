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
 * EvilPHP\Util\Config
 */
namespace EvilPHP\Util {
    /**
     * Class Config
     * @package EvilPHP\Util
     */
    class Config
    {
        /**
         * @var null
         */
        public $tag = null;
        /**
         * @var array|null
         */
        private $_internal = null;

        /**
         * @param null $path
         */
        public function __construct($path = null)
        {
            $this->_internal = array();
            if (isset($path)) {
                $this->parseFile($path);
            }
        }

        /**
         * @param $key
         * @return null
         */
        public function __get($key)
        {
            return isset($this->_internal[$key]) ? $this->_internal[$key] : null;
        }

        /**
         * @param $key
         * @param $val
         */
        public function __set($key, $val)
        {
            if (!isset($val)) {
                $this->_internal[$key] = null;
                unset($this->_internal[$key]);
            } else {
                $this->_internal[$key] = $val;
            }
        }

        /**
         * @param $filePath
         * @return bool
         */
        public function parseFile($filePath)
        {
            $_filePath = (string)$filePath;
            if (!file_exists($_filePath)) {
                trigger_error(sprintf('Config file [%s] not found', $_filePath), E_USER_WARNING);
                return false;
            }
            $ini = parse_ini_file($_filePath, true);
            if (false === $ini) {
                trigger_error(sprintf('Could not parse ini file [%s]', $_filePath), E_USER_WARNING);
                return false;
            }
            foreach ($ini as $key => $value) {
                if (is_array($value)) {
                    $this->$key = new \stdClass();
                    foreach ($value as $_key => $_val) {
                        $this->$key->$_key = $_val;
                    }
                } else {
                    $this->$key = $value;
                }
            }
            return true;
        }
    }
}
   