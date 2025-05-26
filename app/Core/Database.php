<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;
    private $statement;
    private $error;    private function __construct()
    {
        // Initialize logger
        $logFile = __DIR__ . '/../../storage/logs/database.log';
        Logger::setLogFile($logFile);
        
        // Get database configuration
        $dbHost = getenv('DB_HOST') ?: 'mysql';        $dbName = getenv('DB_DATABASE') ?: 'mapit';
        $dbUser = getenv('DB_USERNAME') ?: 'mapit_user';
        $dbPass = getenv('DB_PASSWORD') ?: 'mapit_password';
        $dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';
        
        // DSN string
        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
        
        // Options for PDO
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbCharset}"
        ];

        // Create PDO instance
        try {
            $this->connection = new PDO(
                $dsn, 
                $dbUser, 
                $dbPass, 
                $options
            );
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->connection = null;
            
            // Log the detailed error
            Logger::error('Database Connection Error: ' . $this->error, [
                'exception' => get_class($e),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);        }
    }
    
    // Singleton pattern - Get database instance
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Check if the database connection is active
     * 
     * @return bool
     */
    public function isConnected()
    {
        return $this->connection !== null;
    }
    
    /**
     * Get the last database error
     * 
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
    
    // Prepare statement with query
    public function query($sql)
    {
        if ($this->connection === null) {
            $errorMsg = 'Database connection failed: ' . $this->error;
            Logger::error($errorMsg);
            throw new \Exception($errorMsg);
        }
          try {
            $this->statement = $this->connection->prepare($sql);
            return $this;
        } catch (PDOException $e) {
            $errorMsg = 'SQL Preparation Error: ' . $e->getMessage();
            Logger::error($errorMsg, [
                'sql' => $sql,
                'exception' => get_class($e),
                'code' => $e->getCode()
            ]);
            throw new \Exception($errorMsg, 0, $e);
        }
    }

    // Bind values
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->statement->bindValue($param, $value, $type);
        return $this;
    }

    // Execute the prepared statement
    public function execute()
    {
        try {
            return $this->statement->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo 'Query Error: ' . $this->error;
            return false;
        }
    }

    // Get result set as array of objects
    public function resultSet()
    {
        $this->execute();
        return $this->statement->fetchAll();
    }

    // Get single record as object
    public function single()
    {
        $this->execute();
        return $this->statement->fetch();
    }

    // Get row count
    public function rowCount()
    {
        return $this->statement->rowCount();
    }    // Get last inserted ID
    public function lastInsertId()
    {
        if ($this->connection === null) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }
        return $this->connection->lastInsertId();
    }

    // Transactions
    public function beginTransaction()
    {
        if ($this->connection === null) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }
        return $this->connection->beginTransaction();
    }

    public function endTransaction()
    {
        if ($this->connection === null) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }
        return $this->connection->commit();
    }

    public function cancelTransaction()
    {
        if ($this->connection === null) {
            throw new \Exception('Database connection failed: ' . $this->error);
        }
        return $this->connection->rollBack();
    }

    // Check if database connection is healthy    // Connection status and error methods are defined earlier in this file
}