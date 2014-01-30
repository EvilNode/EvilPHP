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
 * EvilNode\Util\CSS
 */

namespace EvilNode\Util{
    /**
     * Class CSS
     *
     * @package EvilNode\Util
     */
    class CSS {
        /**
         * @var \array
         */
        private $_explicitStyleQueue = null;
        /**
         * @var \array
         */
        private $_includesQueue = null;

        /**
         *
         */
        public function __construct()
        {
            $explicitQueue = Registry::get('__EXPLCSSQUEUE');
            $includesQueue = Registry::get('__INCLCSSQUEUE');
            if (!isset($explicitQueue)) {
                $explicitQueue = array();
                $includesQueue = array();
                Registry::set('__EXPLCSSQUEUE', $explicitQueue);
                Registry::set('__INCLCSSQUEUE', $includesQueue);
            }
            $this->_explicitStyleQueue = $explicitQueue;
            $this->_includesQueue = $includesQueue;
        }

        /**
         * @param string $cssSnippet
         */
        public function addExplicitStyle($cssSnippet)
        {
            if (!is_string($cssSnippet)) {
                return;
            }

            $this->_explicitStyleQueue[] = $cssSnippet;
        }

        /**
         * @param $cssAbsPath
         */
        public function addInclude($cssAbsPath)
        {
            $this->_includesQueue[] = $cssAbsPath;
        }

        /**
         * Gets the explicitly created styles
         * @return string
         */
        public function explicitStyles()
        {
            $out = '';
            while(isset($this->_explicitStyleQueue[0])) {
                $out .= array_shift($this->_explicitStyleQueue) .chr(10);
            }
            return (strlen($out) > 0) ? $out : null;
        }

        /**
         * @return null|string
         */
        public function includes()
        {
            $out = '';
            foreach($this->_includesQueue as $css) {
                $out .= <<<EOT
<link href="{$css}" rel="stylesheet" media="screen">

EOT;

            }
            return (strlen($out) > 0) ? $out : null;
        }
    }
}