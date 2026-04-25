<?php

define('BASE_PATH', realpath(dirname(__DIR__)));
define('APP_DEBUG', true);

require BASE_PATH . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

echo "Resetting Database...\n";

try {
    $dbConfig = require BASE_PATH . '/config/database.php';
    
    $capsule = new Capsule;
    // Assuming 'mysql' is the connection name used in config
    $connectionConfig = $dbConfig['connections']['mysql'];
    // Map 'hostname' to 'host' if needed (Illuminate uses 'host')
    if (isset($connectionConfig['hostname']) && !isset($connectionConfig['host'])) {
        $connectionConfig['host'] = $connectionConfig['hostname'];
    }
    // Map 'type' to 'driver' if needed (Illuminate uses 'driver')
    if (isset($connectionConfig['type']) && !isset($connectionConfig['driver'])) {
        $connectionConfig['driver'] = $connectionConfig['type'];
    }
    
    $capsule->addConnection($connectionConfig);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    
    $pdo = $capsule->getConnection()->getPdo();
    
    // Read init.sql
    $initSql = file_get_contents(BASE_PATH . '/database/sql/init.sql');
    if (!$initSql) {
        die("Could not read init.sql");
    }
    
    // Read demo_data.sql
    $demoSql = file_get_contents(BASE_PATH . '/database/sql/demo_data.sql');
    if (!$demoSql) {
        die("Could not read demo_data.sql");
    }
    
    // Function to execute SQL with multiple statements
    $executeSql = function($sql) use ($pdo) {
        // Remove comments
        $sql = preg_replace('/-- .*$/m', '', $sql);
        // Split by ;
        $statements = explode(';', $sql);
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt)) {
                try {
                    $pdo->exec($stmt);
                } catch (\PDOException $e) {
                    echo "SQL Error: " . $e->getMessage() . "\nStatement: " . substr($stmt, 0, 50) . "...\n";
                }
            }
        }
    };
    
    echo "Executing init.sql...\n";
    $executeSql($initSql);
    
    echo "Executing demo_data.sql...\n";
    $executeSql($demoSql);
    
    echo "Database reset successfully!\n";
    
} catch (\Throwable $e) {
    echo "Error resetting database: " . $e->getMessage() . "\n";
    exit(1);
}
