<?php
require_once __DIR__ . '/Singleton/Database.php';

use App\Singleton\Database;

/**
 * Devuelve una instancia única de PDO para la conexión a la base de datos.
 *
 * @return PDO
 */
function db(): PDO {
    return Database::getInstance();
}