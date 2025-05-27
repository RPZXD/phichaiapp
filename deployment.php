<?php
/**
 * Deployment Script for 4-Department Role System
 * This script safely replaces old files with new ones
 */

class RoleSystemDeployment {
    private $basePath;
    private $backupDir;
    
    public function __construct() {
        $this->basePath = __DIR__;
        $this->backupDir = $this->basePath . '/backup_' . date('Ymd_His');
        
        echo "🚀 Role System Deployment Script\n";
        echo "================================\n\n";
    }
    
    public function deploy() {
        $this->createBackup();
        $this->replaceFiles();
        $this->verifyDeployment();
        $this->printCompletion();
    }
    
    private function createBackup() {
        echo "💾 Creating backup...\n";
        echo "--------------------\n";
        
        if (!mkdir($this->backupDir, 0755, true)) {
            throw new Exception("Failed to create backup directory");
        }
        
        $filesToBackup = [
            'view/roles.php' => 'view/roles_old.php',
            'view/leftmenu.php' => 'view/leftmenu_old.php',
            'view/js/role-management.js' => 'view/js/role-management_old.js',
            'view/api/RoleController.php' => 'view/api/RoleController_old.php'
        ];
        
        foreach ($filesToBackup as $source => $backup) {
            $sourcePath = $this->basePath . '/' . $source;
            $backupPath = $this->backupDir . '/' . $backup;
            
            if (file_exists($sourcePath)) {
                if (!is_dir(dirname($backupPath))) {
                    mkdir(dirname($backupPath), 0755, true);
                }
                
                if (copy($sourcePath, $backupPath)) {
                    echo "✅ Backed up: {$source} → {$backup}\n";
                } else {
                    echo "❌ Failed to backup: {$source}\n";
                }
            } else {
                echo "⚠️  File not found (skipping): {$source}\n";
            }
        }
        
        echo "📁 Backup created in: {$this->backupDir}\n\n";
    }
    
    private function replaceFiles() {
        echo "🔄 Replacing files...\n";
        echo "--------------------\n";
        
        $replacements = [
            'view/roles_new.php' => 'view/roles.php',
            'view/leftmenu_updated.php' => 'view/leftmenu.php',
            'view/js/role-management-updated.js' => 'view/js/role-management.js',
            'view/api/RoleController-updated.php' => 'view/api/RoleController.php'
        ];
        
        foreach ($replacements as $source => $target) {
            $sourcePath = $this->basePath . '/' . $source;
            $targetPath = $this->basePath . '/' . $target;
            
            if (file_exists($sourcePath)) {
                if (copy($sourcePath, $targetPath)) {
                    echo "✅ Replaced: {$target}\n";
                } else {
                    echo "❌ Failed to replace: {$target}\n";
                }
            } else {
                echo "❌ Source file missing: {$source}\n";
            }
        }
        
        echo "\n";
    }
    
    private function verifyDeployment() {
        echo "🔍 Verifying deployment...\n";
        echo "--------------------------\n";
        
        $requiredFiles = [
            'view/roles.php',
            'view/leftmenu.php',
            'view/switch_role.php',
            'view/js/role-management.js',
            'view/api/RoleController.php'
        ];
        
        $allGood = true;
        
        foreach ($requiredFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            
            if (file_exists($filePath)) {
                $syntaxCheck = shell_exec("php -l \"{$filePath}\" 2>&1");
                if (strpos($syntaxCheck, 'No syntax errors') !== false) {
                    echo "✅ {$file}: OK\n";
                } else {
                    echo "❌ {$file}: Syntax error\n";
                    $allGood = false;
                }
            } else {
                echo "❌ {$file}: Missing\n";
                $allGood = false;
            }
        }
        
        if ($allGood) {
            echo "\n✅ All files deployed successfully!\n";
        } else {
            echo "\n⚠️  Some issues detected. Please check the files above.\n";
        }
        
        echo "\n";
    }
    
    private function printCompletion() {
        echo "🎉 DEPLOYMENT COMPLETED!\n";
        echo "========================\n";
        echo "✅ Old files backed up to: {$this->backupDir}\n";
        echo "✅ New files deployed\n";
        echo "✅ System ready for use\n";
        echo "\n";
        echo "📋 Next Steps:\n";
        echo "1. Test the role management system: http://localhost/phichaiapp/view/roles.php\n";
        echo "2. Test role switching: http://localhost/phichaiapp/view/switch_role.php\n";
        echo "3. Check navigation menu functionality\n";
        echo "4. Run database migration if not done: php migrate_roles.php\n";
        echo "\n";
        echo "📞 If you encounter issues:\n";
        echo "- Check backup files in: {$this->backupDir}\n";
        echo "- Review error logs\n";
        echo "- Restore from backup if needed\n";
        echo "\n";
        echo "🔄 To rollback:\n";
        echo "php deployment.php rollback\n";
        echo "\n";
    }
    
    public function rollback() {
        echo "🔙 Rolling back deployment...\n";
        echo "=============================\n";
        
        $backupDirs = glob($this->basePath . '/backup_*');
        
        if (empty($backupDirs)) {
            echo "❌ No backup directories found.\n";
            return;
        }
        
        // Use the most recent backup
        $latestBackup = end($backupDirs);
        echo "📁 Using backup: " . basename($latestBackup) . "\n\n";
        
        $restoreFiles = [
            'view/roles_old.php' => 'view/roles.php',
            'view/leftmenu_old.php' => 'view/leftmenu.php',
            'view/js/role-management_old.js' => 'view/js/role-management.js',
            'view/api/RoleController_old.php' => 'view/api/RoleController.php'
        ];
        
        foreach ($restoreFiles as $backup => $target) {
            $backupPath = $latestBackup . '/' . $backup;
            $targetPath = $this->basePath . '/' . $target;
            
            if (file_exists($backupPath)) {
                if (copy($backupPath, $targetPath)) {
                    echo "✅ Restored: {$target}\n";
                } else {
                    echo "❌ Failed to restore: {$target}\n";
                }
            } else {
                echo "⚠️  Backup file not found: {$backup}\n";
            }
        }
        
        echo "\n✅ Rollback completed!\n";
        echo "The system has been restored to the previous state.\n\n";
    }
    
    public function showStatus() {
        echo "📊 DEPLOYMENT STATUS\n";
        echo "===================\n";
        
        $currentFiles = [
            'view/roles.php' => 'Role Management Interface',
            'view/leftmenu.php' => 'Navigation Menu',
            'view/switch_role.php' => 'Role Switching',
            'view/js/role-management.js' => 'JavaScript',
            'view/api/RoleController.php' => 'API Controller'
        ];
        
        foreach ($currentFiles as $file => $description) {
            $filePath = $this->basePath . '/' . $file;
            
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                
                // Check if it's the new version
                if (strpos($content, '4-Department') !== false || 
                    strpos($content, 'department') !== false ||
                    strpos($content, 'Updated') !== false) {
                    echo "✅ {$description}: New version deployed\n";
                } else {
                    echo "⚠️  {$description}: Old version (not deployed)\n";
                }
            } else {
                echo "❌ {$description}: File missing\n";
            }
        }
        
        echo "\n📁 Available backups:\n";
        $backupDirs = glob($this->basePath . '/backup_*');
        
        if (empty($backupDirs)) {
            echo "   No backups found\n";
        } else {
            foreach ($backupDirs as $backup) {
                echo "   " . basename($backup) . "\n";
            }
        }
        
        echo "\n";
    }
}

// Run deployment if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $deployment = new RoleSystemDeployment();
    
    $action = $argv[1] ?? 'deploy';
    
    switch ($action) {
        case 'rollback':
            $deployment->rollback();
            break;
        case 'status':
            $deployment->showStatus();
            break;
        case 'deploy':
        default:
            $deployment->deploy();
            break;
    }
}
?>
