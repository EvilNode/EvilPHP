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
 * EvilNode\Util\JQuery
 */

namespace EvilNode\Util {
    /**
     * Class JQuery
     *
     * @package EvilNode\Util
     */
    class JQuery
    {
        /**
         * @var \SplQueue
         */
        private $_documentReadyQueue = null;
        /**
         * @var array|mixed|null
         */
        private $_includesQueue = null;

        /**
         *
         */
        public function __construct()
        {
            $documentReadyQueue = Registry::get('__JQDOCREADYQUEUE');
            $includesQueue = Registry::get('__JQINCQUEUE');
            if (!isset($documentReadyQueue, $includesQueue)) {
                $documentReadyQueue = array();
                Registry::set('__JQDOCREADYQUEUE', $documentReadyQueue);
                $includesQueue = array();
                Registry::set('__JQINCQUEUE', $includesQueue);
            }
            $this->_documentReadyQueue = $documentReadyQueue;
            $this->_includesQueue = $includesQueue;
        }

        /**
         * @param string $snippet
         */
        public function addDocumentReadySnippet($snippet)
        {
            if (!is_string($snippet)) {
                return;
            }

            $this->_documentReadyQueue[] = $snippet;
        }

        /**
         * Gets the document.ready handlers
         * @return string
         */
        public function documentReady()
        {
            $out = '';
            while(isset($this->_documentReadyQueue[0])) {
                $out .= array_shift($this->_documentReadyQueue) .chr(10);
            }
            return (strlen($out) > 0) ? $out : null;
        }

        /**
         * @param $absPath
         */
        public function addInclude($absPath)
        {
            $this->_includesQueue[] = $absPath;
        }

        /**
         * @return null|string
         */
        public function includes()
        {
            $out = '';
            foreach($this->_includesQueue as $js) {
                $out .= <<<EOT
<script type="text/javascript" src="{$js}"></script>

EOT;

            }
            return (strlen($out) > 0) ? $out : null;
        }
    }
}


