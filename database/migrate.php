<?php
/**
 * Database Migration Script
 * Checks and applies database migrations for the enhanced role permission system
 */

require_once __DIR__ . '/../config/Database.php';

use App\Database;

class DatabaseMigrator {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function checkTableExists($tableName) {
        try {
            $query = "SHOW TABLES LIKE ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$tableName]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
      public function runMigration($filePath) {
        try {
            $sql = file_get_contents($filePath);
            if (!$sql) {
                throw new Exception("Could not read migration file: $filePath");
            }
            
            echo "📝 File content length: " . strlen($sql) . " characters\n";
            
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            echo "📊 Found " . count($statements) . " SQL statements\n";
            
            foreach ($statements as $index => $statement) {
                if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                    echo "  Executing statement " . ($index + 1) . "...\n";
                    try {
                        $this->db->exec($statement);
                        echo "  ✅ Success\n";
                    } catch (Exception $e) {
                        echo "  ❌ Error: " . $e->getMessage() . "\n";
                        echo "  Statement: " . substr($statement, 0, 100) . "...\n";
                        throw $e;
                    }
                }
            }
            
            return true;
        } catch (Exception $e) {
            echo "Error running migration: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function migrate() {
        echo "=== Database Migration Tool ===\n\n";
        
        // Check if new tables exist
        $tablesRequired = ['roles', 'user_roles', 'permissions', 'role_permissions', 'user_permissions'];
        $missingTables = [];
        
        foreach ($tablesRequired as $table) {
            if (!$this->checkTableExists($table)) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            echo "✅ All required tables exist. Database is up to date.\n";
            
            // Check if we need to add new columns to users table
            $this->checkUsersTableColumns();
            return true;
        }
        
        echo "❌ Missing tables: " . implode(', ', $missingTables) . "\n";
        echo "🔄 Running migrations...\n\n";
        
        // Run migrations in order
        $migrations = [
            '001_enhanced_role_permission_system.sql',
            '002_sample_roles_permissions_data.sql',
            '003_migrate_existing_users.sql'
        ];
        
        foreach ($migrations as $migration) {
            $filePath = __DIR__ . "/migrations/$migration";
            
            if (!file_exists($filePath)) {
                echo "⚠️  Migration file not found: $migration\n";
                continue;
            }
            
            echo "📄 Running migration: $migration\n";
            
            if ($this->runMigration($filePath)) {
                echo "✅ Successfully applied: $migration\n";
            } else {
                echo "❌ Failed to apply: $migration\n";
                return false;
            }
        }
        
        echo "\n🎉 All migrations completed successfully!\n";
        return true;
    }
    
    public function checkUsersTableColumns() {
        try {
            // Check if users table has the new columns
            $query = "DESCRIBE users";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $requiredColumns = ['employee_id', 'department_id', 'is_active'];
            $missingColumns = array_diff($requiredColumns, $columns);
            
            if (!empty($missingColumns)) {
                echo "🔄 Adding missing columns to users table...\n";
                
                foreach ($missingColumns as $column) {
                    $alterSQL = '';
                    switch ($column) {
                        case 'employee_id':
                            $alterSQL = "ALTER TABLE users ADD COLUMN employee_id VARCHAR(20) NULL UNIQUE AFTER email";
                            break;
                        case 'department_id':
                            $alterSQL = "ALTER TABLE users ADD COLUMN department_id INT NULL AFTER employee_id";
                            break;
                        case 'is_active':
                            $alterSQL = "ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER phone_number";
                            break;
                    }
                    
                    if ($alterSQL) {
                        $this->db->exec($alterSQL);
                        echo "✅ Added column: $column\n";
                    }
                }
            }
        } catch (Exception $e) {
            echo "⚠️  Error checking users table: " . $e->getMessage() . "\n";
        }
    }
}

// Run the migration
try {
    $migrator = new DatabaseMigrator();
    $migrator->migrate();
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
?>
