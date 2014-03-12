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
 * EvilPHP\Util\Registry
 */
namespace EvilPHP\Util {
    /**
     * Class Registry
     * @package HSB
     */
    class Registry
    {
        /**
         * @var array
         */
        private static $_internal = array();

        /**
         * @param string $key
         * @param string|null $default
         * @return mixed|null
         */
        public static function get($key, $default = null)
        {
            $_key = (string)$key;
            assert('isset($_key{0})');
            return isset(self::$_internal[$_key]) ? self::$_internal[$_key] : $default;
        }

        /**
         * @param string $key
         */
        public static function remove($key)
        {
            self::set($key, null);
        }

        /**
         * @param string $key
         * @param mixed|null $val
         * @param bool $ref
         */
        public static function set($key, $val = null, $ref = false)
        {
            $_key = (string)$key;
            assert('isset($_key{0})');
            if (!isset($val)) {
                self::$_internal[$_key] = null;
                unset(self::$_internal[$_key]);
            } elseif ($ref) {
                self::$_internal[$_key] = & $val;
            } else {
                self::$_internal[$_key] = $val;
            }
        }

        /**
         * Gets a representation of the internal state
         * @return array
         */
        public static function snapshot()
        {
            return self::$_internal;
        }
    }
}