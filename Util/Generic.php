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
 * EvilPHP\Util\Generic
 * This class exists as a safe wrapper for argument passing among loosely coupled classes such as the EventDispatcher
 * or MVC\Controller.  Implicit gets that fail will return null and not trigger errors
 *
 * Note that this class only supports one level of depth, so if multiple levels are needed, you should be using stdClass
 * e.g. $obj->foo->bar = $something; //doesnt work here
 */
namespace EvilPHP\Util {
    /**
     * Class Generic
     *
     * @package EvilPHP\Util
     */
    class Generic
    {
        /**
         * @var array
         */
        private $_internals;

        /**
         *
         */
        public function __construct ()
        {
            $this->_internals = array();
        }

        /**
         * @param $key
         * @return null
         */
        public function __get ($key)
        {
            return isset($this->_internals[$key]) ? $this->_internals[$key] : null;
        }

        /**
         * @param $key
         * @param $val
         */
        public function __set ($key, $val)
        {
            if (isset($key{0})) {
                if (isset($val)) {
                    $this->_internals[$key] = $val;
                } else {
                    unset($this->_internals[$key]);
                }
            }
        }

        /**
         * @param $key
         * @return bool
         */
        public function __isset ($key)
        {
            return isset($this->_internals[$key]);
        }

        /**
         * @param $key
         */
        public function __unset ($key)
        {
            if (isset($this->_internals[$key])) {
                unset($this->_internals[$key]);
            }
        }
    }
}