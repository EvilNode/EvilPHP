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
 * EvilPHP\Event\EventDispatcher
 */
namespace EvilPHP\Event {
    /**
     * Class EventDispatcher
     * @package EvilPHP\Event
     */
    class EventDispatcher
    {
        /**
         *  Most important priority
         */
        const PRIORITY_URGENT = 3;
        /**
         *  Important priority
         */
        const PRIORITY_HIGH = 2;
        /**
         *  Default priority
         */
        const PRIORITY_NORMAL = 1;
        /**
         *  Low priority
         */
        const PRIORITY_LOW = 0;
        /**
         * @var EventDispatcher
         */
        private static $_instance = null;
        /**
         * @var string
         */
        public $stopPropagatingEventName = null;
        /**
         * @var array
         */
        private $_observerTree;

        /**
         *  Constructor
         */
        private function __construct()
        {
            $this->_observerTree = array();
        }

        /**
         * EventDispatcher Shared Singleton
         * @return EventDispatcher|null
         */
        public static function sharedDispatcher()
        {
            if (!isset(self::$_instance)) {
                self::$_instance = new EventDispatcher();
            }
            return self::$_instance;
        }

        /**
         * Add an EventBlock to the dispatcher.  It will bubble up or down in sequence based on
         * its priority
         * @param EventBlock $block
         * @param int $priority
         */
        public function addEventBlock(EventBlock $block, $priority = self::PRIORITY_NORMAL)
        {
            $name = $block->blockName();
            if (!isset($this->_observerTree[$name])) {
                $this->_observerTree[$name] = new \SplPriorityQueue();
            }
            $queue = $this->_observerTree[$name];
            $queue->insert($block, $priority);
        }

        /**
         * Triggers the named event.
         * Higher priority events can cancel the propagation of lower level events
         * @param $name
         * @param $args
         */
        public function trigger($name, Generic $obj)
        {
            if (isset($this->_observerTree[$name])) {
                $queue = $this->_observerTree[$name];
                $queue->rewind();
                while ($queue->valid()) {
                    if ($name === $this->stopPropagatingEventName) {
                        $this->stopPropagatingEventName = null;
                        unset($this->_observerTree[$name]);
                        return;
                    }
                    $eventBlock = $queue->extract();
                    $eventBlock->$name($obj);
                }
                unset($this->_observerTree[$name]);
            }
        }

        /**
         * Removes an event from the observer tree
         * @param $name
         */
        public function clear($name)
        {
            unset($this->_observerTree[$name]);
        }
    }
}