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
 * EvilPHP\Event\EventBlock
 */
namespace EvilPHP\Event {

    use EvilPHP\Util\Generic;

    /**
     * Class EventBlock
     * @package EvilPHP\Event
     */
    class EventBlock
    {
        /**
         *  Indicates that the EventBlock is ready to execute
         */
        const STATE_READY = 0;
        /**
         *  Indicates that the EventBlock has finished executing
         */
        const STATE_COMPLETE = 1;
        /**
         * @var \Closure The function to be executed during the callback phase
         */
        private $_closure;
        /**
         * @var string The event name that will trigger the closure
         */
        private $_blockName;
        /**
         * @var int The ready state of the EventBlock
         */
        private $_status;

        /**
         * Constructor
         * @param $name string The event name that will trigger the closure
         * @param \Closure $cl The function to be executed during the callback phase
         */
        public function __construct($name, \Closure $cl)
        {
            $this->_blockName = $name;
            $this->_closure = $cl;
            $this->_status = self::STATE_READY;
        }

        /**
         * Anonymous callback.  If for some reason this object receives a __call to something
         * other than what was registered in the constructor, an assertion will fail.  You can
         * optionally handle this by using the assert_options function
         * @param $name string
         * @param $obj Generic
         * @return mixed
         */
        public function __call($name, Generic $obj)
        {
            if (assert('$name === $this->_blockName')) {
                /** @noinspection PhpUndefinedMethodInspection */
                $ret = $this->_closure->__invoke($obj);
                $this->_status = self::STATE_COMPLETE;
                return $ret;
            } else {
                return null;
            }
        }

        /**
         * Get the exec status of the EventBlock
         * @return int
         */
        public function status()
        {
            return $this->_status;
        }

        /**
         * Get this EventBlock's trigger name
         * @return string
         */
        public function blockName()
        {
            return $this->_blockName;
        }
    }
}