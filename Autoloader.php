<?php
/**
 * Created by PhpStorm.
 * User: shigh
 * Date: 10/28/13
 * Time: 5:26 PM
 */

namespace EvilNode {

    class EvilNode_Autoloader
    {
        static public function register()
        {
            ini_set('unserialize_callback_func', 'spl_autoload_call');
            spl_autoload_register(array(new self, 'autoload'));
        }

        /**
         * Handles autoloading of classes.
         *
         * @param  string  $class  A class name.
         *
         * @return boolean Returns true if the class has been loaded
         */
        static public function autoload($class)
        {
            if (0 !== strpos($class, 'EvilNode')) {
                return;
            }

            $path = explode('\\', $class);
            array_unshift($path, APP_LIB_PATH);
            $path = implode(DIRECTORY_SEPARATOR, $path) . '.php';
            if (is_file($path)) {
                /** @noinspection PhpIncludeInspection */
                require $path;
            }
        }
    }
}