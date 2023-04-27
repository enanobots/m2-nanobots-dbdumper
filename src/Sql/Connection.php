<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Sql;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\LocalizedException;

class Connection
{
    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    private AdapterInterface $connection;

    /** @var null|array  */
    private ?array $viewList = null;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface  */
    protected ScopeConfigInterface $scopeConfig;

    /** @var \Magento\Framework\App\DeploymentConfig  */
    protected \Magento\Framework\App\DeploymentConfig $deploymentConfig;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    ) {
        $this->connection = $resource->getConnection();
        $this->scopeConfig = $scopeConfig;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection(): AdapterInterface
    {
        return $this->connection;
    }

    /**
     * @param $tableName
     * @return string
     */
    public function getTableName($tableName): string
    {
        return $this->connection->getTableName($tableName);
    }

    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param mixed $sql
     * @param  mixed $bind An array of data or data itself to bind to the placeholders.
     * @return \Zend_Db_Statement_Interface
     */
    public function query($sql, array $bind = []): \Zend_Db_Statement_Interface
    {
        try {
            return $this->connection->query($sql, $bind);
        } catch (LocalizedException|\Zend_Db_Adapter_Exception $e) {
        }
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function select(): \Magento\Framework\DB\Select
    {
        return $this->connection->select();
    }

    /**
     * @param \Zend_Db_Select $sql
     * @param array $bind
     * @return string
     */
    public function fetchOne(\Zend_Db_Select $sql, array $bind = []): string
    {
        return $this->connection->fetchOne($sql, $bind);
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getCreateTable(string $tableName): string
    {
        return $this->connection->getCreateTable($tableName);
    }

    /**
     * @param string $tableName
     * @return string|null
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getAutoincrementField(string $tableName): ?string
    {
        $data = $this->deploymentConfig->get('db/connection/default');
        $dbName = $data['dbname'];

        $query = "select COLUMN_NAME from information_schema.columns where
            TABLE_SCHEMA='" . $dbName  . "' and TABLE_NAME='" . $tableName . "' and EXTRA like
            '%auto_increment%'";

        return $this->connection->fetchOne($query) ?: null;
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function isTableEmpty(string $tableName): bool
    {
        $query = "SELECT EXISTS (SELECT 1 FROM $tableName)";
        return $this->connection->fetchOne($query) === "0";
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function isTableAView(string $tableName): bool
    {
        if ($this->viewList === null) {
            $query = "SHOW FULL TABLES WHERE table_type = 'VIEW'";
            $this->viewList = $this->connection->fetchCol($query);
        }

        return in_array($tableName, $this->viewList);
    }

    /**
     * @return array
     */
    public function getAllTables(): array
    {
        $query = "SHOW TABLES";
        return $this->connection->fetchCol($query);
    }

    /**
     * @param string $tableName
     * @return int
     */
    public function getRowCount(string $tableName): int
    {
        return (int)$this->connection->fetchOne('SELECT COUNT(*) FROM `' . $tableName . '`');
    }

    /**
     * @param string $tableName
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function getTableSize(string $tableName): string
    {
        $data = $this->deploymentConfig->get('db/connection/default');
        $dbName = $data['dbname'];

        $query = "select ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS `Size (MB)`
                    FROM information_schema.TABLES WHERE
                        TABLE_SCHEMA='" . $dbName  . "' and TABLE_NAME='" . $tableName . "'";

        return $this->connection->fetchOne($query);
    }
}
