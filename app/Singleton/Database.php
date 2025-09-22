<?php
namespace App\Singleton;

use PDO;

// PATRÓN DE DISEÑO SINGLETON
// Una sola instancia de la base de datos
class Database
{
    /** @var PDO|null Instancia única de la conexión PDO. */
    private static ?PDO $instance = null;

    private function __construct(){} // Constructor privado para evitar la instanciación directa.

    /**
    * Devuelve la instancia única de PDO. Si aún no existe, la crea.
    *
    * @return PDO
    */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            self::$instance = new PDO($dsn, DB_USER, DB_PASS);
            $errMode = APP_DEBUG ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, $errMode);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return self::$instance;
    }

    private function __clone(): void {} // Evitar clonación
}
