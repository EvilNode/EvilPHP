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
 * \EvilNode\Data\Database
 */
namespace EvilNode\Data {
    use EvilNode\Util\Config;
    use EvilNode\Util\Registry;

    /**
     * Class Database
     * @package HSB\Data
     */
    class Database
    {
        /**
         * @var mixed|null|\PDO
         */
        private $_pdo;
        /**
         * @var int
         */
        private $_affectedRows = 0;

        /**
         * @param $host
         * @param $username
         * @param $password
         * @param $dbname
         * @param $driver
         * @param null $port
         * @param null $socket
         * @param array $options
         * @param string $tag
         */
        public function __construct($host, $username, $password, $dbname, $driver, $port = null, $socket = null, $options = array(), $tag = '')
        {
            $registryAddress = "__PDO{$tag}__";
            $this->_pdo = Registry::get($registryAddress);

            if (!isset($this->_pdo)) {
                if (!isset($host, $username, $password, $dbname, $driver)) {
                    trigger_error('Could not instantiate database.  Invalid config params', E_USER_ERROR);
                }

                //  for PDO, you cant have a socket set AND a port/hostname
                //  i.e. the socket, if set, means that the port and hostname
                //  should NOT be set
                if (!is_null($socket)) {
                    $dsn = "{$driver}:unix_socket={$socket};dbname={$dbname}";
                } else {
                    if (is_null($port)) {
                        $port = 3306;
                    }
                    $dsn = "{$driver}:host={$host};port={$port};dbname={$dbname}";
                }
                $pdo = null;
                try {
                    $pdo = new \PDO($dsn, $username, $password, $options);
                    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                } catch (\PDOException $ex) {
                    trigger_error($ex->getMessage(), E_USER_WARNING);
                    return;
                }
                Registry::set($registryAddress, $pdo);
                $this->_pdo = & $pdo;
            }
        }

        /**
         * @param Config $config
         * @return Database
         */
        public static function fromConfig(Config $config)
        {
            $socket = isset($config->DATABASE->socket) ? $config->DATABASE->socket : null;
            $options = isset($config->DATABASE->options) ? unserialize($config->DATABASE->options) : array();
            return new Database(
                $config->DATABASE->host,
                $config->DATABASE->username,
                $config->DATABASE->password,
                $config->DATABASE->dbname,
                $config->DATABASE->driver,
                $config->DATABASE->port,
                $socket,
                $options,
                $config->tag
            );
        }

        /**
         * @param $sql
         * @return null
         */
        public function fetchOne($sql)
        {
            $args = func_get_args();
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return null;
            }

            $stmt = $stmtObj->stmt;
            /** @noinspection PhpUndefinedMethodInspection */
            return $stmt->fetchColumn();
        }

        /**
         * @param $sql
         * @return null
         */
        public function fetchRow($sql)
        {
            $args = func_get_args();
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return null;
            }

            $stmt = $stmtObj->stmt;
            /** @noinspection PhpUndefinedMethodInspection */
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        /**
         * @param $sql
         * @return null
         */
        public function fetchObject($sql)
        {
            $args = func_get_args();
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return null;
            }

            $stmt = $stmtObj->stmt;
            /** @noinspection PhpUndefinedMethodInspection */
            return $stmt->fetch(\PDO::FETCH_OBJ);
        }

        /**
         * @param $class
         * @param $sql
         * @return null
         */
        public function fetchObjectOfType($class, $sql)
        {
            $args = func_get_args();
            array_shift($args);
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return null;
            }

            $stmt = $stmtObj->stmt;
            if (class_exists($class)) {
                $interfaces = array_values(class_implements($class, false));
                if (in_array('EvilNode\Data\IDataMap', $interfaces, true)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $obj = $stmt->fetchObject();
                    return (false !== $obj) ? $class::fromGenericObject($obj) : null;
                }
            }

            /** @noinspection PhpUndefinedMethodInspection */
            return $stmt->fetchObject($class);
        }

        /**
         * @param $sql
         * @return null
         */
        public function fetchAll($sql)
        {
            $args = func_get_args();
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return null;
            }

            $stmt = $stmtObj->stmt;
            /** @noinspection PhpUndefinedMethodInspection */
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        /**
         * @param $sql
         * @return null
         */
        public function fetchObjects($sql)
        {
            $args = func_get_args();
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return null;
            }

            $stmt = $stmtObj->stmt;
            /** @noinspection PhpUndefinedMethodInspection */
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        }

        /**
         * @param $class
         * @param $sql
         * @return array|null
         */
        public function fetchObjectsOfType($class, $sql)
        {
            $args = func_get_args();
            array_shift($args);
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return null;
            }
            $stmt = $stmtObj->stmt;
            if (class_exists($class)) {
                $interfaces = array_values(class_implements($class, false));
                if (in_array('EvilNode\Data\IDataMap', $interfaces, true)) {
                    $ret = array();
                    /** @noinspection PhpUndefinedMethodInspection */
                    $objs = $stmt->fetchAll(\PDO::FETCH_OBJ);
                    $numObjs = count($objs);
                    for ($i = 0; $i < $numObjs; $i++) {
                        $ret[] = $class::fromGenericObject($objs[$i]);
                    }
                    unset($assoc);
                    return $ret;
                }
            }
            /** @noinspection PhpUndefinedMethodInspection */
            return $stmt->fetchAll(\PDO::FETCH_CLASS, $class);
        }

        /**
         * @param $sql
         * @return array|null
         */
        public function fetchColumn($sql)
        {
            $args = func_get_args();
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return null;
            }
            $stmt = $stmtObj->stmt;
            $ret = array();
            /** @noinspection PhpUndefinedMethodInspection */
            while ( $col = $stmt->fetchColumn()) {
                $ret[] = $col;
            }

            return $ret;
        }

        /**
         * @return string
         */
        public function lastInsertId()
        {
            return $this->_pdo->lastInsertId();
        }

        /**
         * @return int
         */
        public function affectedRows()
        {
            return $this->_affectedRows;
        }

        /**
         * @param $sql
         * @return bool
         */
        public function write($sql)
        {
            $args = func_get_args();
            $stmtObj = call_user_func_array(array($this, '_query'), $args);
            if (false === $stmtObj->exec) {
                return false;
            }
            $stmt = $stmtObj->stmt;
            $this->_affectedRows = $stmt->rowCount();
            return true;
        }

        /**
         * @return bool
         */
        public function startTransaction()
        {
            if ($this->_pdo->inTransaction()) {
                return false;
            }
            return $this->_pdo->beginTransaction();
        }

        /**
         * @return bool
         */
        public function commitTransaction()
        {
            if (!$this->_pdo->inTransaction()) {
                return false;
            }
            return $this->_pdo->commit();
        }

        /**
         * @return bool
         */
        public function rollbackTransaction()
        {
            if (!$this->_pdo->inTransaction()) {
                return false;
            }
            return $this->_pdo->rollBack();
        }

        /**
         * @return array
         */
        public function lastError()
        {
            return array(
                'errorCode' => $this->_pdo->errorCode(),
                'errorInfo' => $this->_pdo->errorInfo()
            );
        }

        /**
         * @param $sql
         * @return \stdClass
         * @throws DatabaseException
         * @throws UniqueKeyException
         */
        private function _query($sql)
        {
            try {
                $stmt = $this->_pdo->prepare($sql);

                $args = array_values(func_get_args());
                array_shift($args);

                $numArgs = count($args);
                for ($i = 0; $i < $numArgs; $i++) {
                    $p = $i + 1;
                    $t = gettype($args[$i]);
                    switch ($t) {
                        case 'integer':
                            $stmt->bindValue($p, $args[$i], \PDO::PARAM_INT);
                            break;
                        case 'string':
                            $stmt->bindValue($p, $args[$i], \PDO::PARAM_STR);
                            break;
                        case 'boolean':
                            $stmt->bindValue($p, $args[$i], \PDO::PARAM_BOOL);
                            break;
                        case 'array':
                        case 'object':
                            $stmt->bindValue($p, serialize($args[$i]), \PDO::PARAM_STR);
                            break;
                        case 'NULL':
                            $stmt->bindValue($p, null, \PDO::PARAM_NULL);
                            break;
                        default:
                            $stmt->bindValue($p, $args[$i], \PDO::PARAM_STR);
                    }
                }
                $ret = new \stdClass();
                $ret->stmt = $stmt;
                $ret->exec = $stmt->execute();

                return $ret;
            } catch (\PDOException $ex) {
                $error = (int)$ex->getCode();
                if (23000 === $error) {
                    throw new UniqueKeyException($ex->getMessage(), 23000, $ex);
                }
                throw new DatabaseException($ex->getMessage(), (int)$ex->getCode(), $ex);
            }
        }
    }
}