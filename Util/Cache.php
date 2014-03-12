<?php
/**
 *  Cache.php
 *  This file is part of the EvilPHP Framework
 *  (c)2014 Steve High <steve@evilnode.com>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 *  and associated documentation files (the "Software"), to deal in the Software without restriction,
 *  including without limitation the rights to use, copy, modify, merge, publish, distribute,
 *  sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all copies or substantial
 *  portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
 *  NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 *  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
 *  OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
 
namespace EvilPHP\Util {

    /**
     * Class Cache
     * @package EvilPHP\Util
     */
    class Cache
    {
        /**
         * @var string
         */
        protected $path;
        /**
         * @var bool
         */
        protected $hushed;
        /**
         * @var null
         */
        protected $parentpath;

        /**
         * @param $context  The storage context for getting and putting data
         * @param $create Create directory if it doesnt exist
         */
        public function __construct($context, $create=true)
        {
            $this->path = EVILPHP_CACHE_PATH . DIRECTORY_SEPARATOR . preg_replace('/[^a-z0-9]/', '', strtolower($context));
            if (!(is_dir($this->path) || $create)) {
                if (!mkdir($this->path)) {
                    trigger_error('Cache could not be instantiated', E_USER_WARNING);
                }
            }
            $this->hushed = (defined('EVILPHP_CACHE_DISABLE') && true === EVILPHP_CACHE_DISABLE);
            $this->parentpath = null;
            $this->parentident = null;
        }

        /**
         * @param $path
         * @param $ident
         * @return $this
         */
        public function setParentCacheReference($path, $ident)
        {
            $this->parentpath = EVILPHP_CACHE_PATH . DIRECTORY_SEPARATOR .
                preg_replace('/[^a-z0-9]/', '', strtolower($path)) . DIRECTORY_SEPARATOR . md5("{$ident}");

            return $this;
        }

        /**
         * Fetch an object from the cache.  If the $null_callback is supplied, the
         * return value for it will be stored in the cache and returned.
         * Note that the callback takes no arguments, so everything in the closure scope
         * must be visible to that scope.
         * @param $label
         * @param $lifetime
         * @param callable $null_callback
         * @return mixed|null
         */
        public function get($label, $lifetime, \Closure $null_callback = null)
        {
            if (!$this->hushed) {
                $datafile = md5("{$label}");
                $path = $this->path . DIRECTORY_SEPARATOR . $datafile;
                if (file_exists($path)) {
                    $created = filemtime($path);
                    $expires = $created + $lifetime;
                    $now = time();
                    if ($now < $expires) {
                        $ret = file_get_contents($path);
                        $store = unserialize($ret);
                        $this->parentpath = $store->parentpath;
                        return $store->data;
                    }
                }
            }
            if (isset($null_callback)) {
                $store = $null_callback->__invoke();
                if (isset($store)) {
                    $this->put($store, $label);
                    return $store;
                }
            }

            return null;
        }

        /**
         * Caches data.  It is serialized when stored and retrieved.
         * @param $data mixed   The data to store
         * @param $label string The data reference
         *
         * @return \EvilPHP\Util\Cache
         */
        public function put($data, $label)
        {
            if ($this->hushed) {
                return $this;
            }
            $store = new \stdClass();
            $store->parentpath = $this->parentpath;
            $store->data = $data;
            $serial = serialize($store);
            $datafile = md5("{$label}");
            $path = $this->path . DIRECTORY_SEPARATOR . $datafile;
            $fp = fopen($path, 'wb');
            if (flock($fp, LOCK_EX)) {
                fwrite($fp, $serial, strlen($serial));
                flock($fp, LOCK_UN);
            }
            fclose($fp);
            self::clipPath($this->parentpath);
            return $this;
        }

        public function invalidate($label)
        {
            $datafile = md5("{$label}");
            $path = $this->path . DIRECTORY_SEPARATOR . $datafile;
            self::clipPath($path);
        }

        private static function clipPath($path)
        {
            if (isset($path) && file_exists($path) && is_writeable($path)) {
                $store = unserialize(file_get_contents($path));
                unlink($path);
                if (isset($store->parentpath)) {
                    self::clipPath($store->parentpath);
                }
            }
        }
    }
}

 
