<?php

/**
 * Created by Cheiselle Deloso
 * June 09, 2025
 */

class Database
{
    private $serverName = '192.168.1.81';
    private $port = '1433';
    protected $connectionInfo = [
        "Database"     => "PL-INSCUSTSTDB",
        "UID"          => "sa",
        "PWD"          => "df0rc3",
        "CharacterSet" => "UTF-8"
    ];
    protected $conn;

    public function __construct()
    {
        // Combine server IP and port correctly for SQL Server
        $fullServer = $this->serverName . ',' . $this->port;

        $this->conn = sqlsrv_connect($fullServer, $this->connectionInfo);
        if (!$this->conn) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    public function select($query, $params = [])
    {
        $stmt = sqlsrv_query($this->conn, $query, $params);
        $data = [];

        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        }

        return false;
    }

    public function insert($query, $params = [])
    {
        $stmt = sqlsrv_query($this->conn, $query, $params);
        return $stmt ? true : false;
    }

    public function update($query, $params = [])
    {
        $stmt = sqlsrv_query($this->conn, $query, $params);
        return $stmt ? true : false;
    }

    public function delete($query, $params = [])
    {
        $stmt = sqlsrv_query($this->conn, $query, $params);
        return $stmt ? true : false;
    }
}