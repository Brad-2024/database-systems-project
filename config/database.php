<?php

function getDatabaseConnection()
{
    static $connection = null;

    if ($connection !== null) {
        return $connection;
    }

    $databaseHost = '';
    $databaseName = '';
    $databaseUser = '';
    $databasePassword = '';

    $connection = mysqli_connect($databaseHost, $databaseUser, $databasePassword, $databaseName);

    if (!$connection) {
        exit('Unable to connect to the database. Please update config/database.php with your MySQL details.');
    }

    mysqli_set_charset($connection, 'utf8mb4');

    return $connection;
}