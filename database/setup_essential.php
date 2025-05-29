<?php 
try {
    $pdo = new PDO('mysql:host=localhost;dbname=phichaia_app', 'root', '');
    echo "Creating essential tables for multi-role system...\n";
    
    // Create departments table
    $sql = "CREATE TABLE IF NOT EXISTS departments (
        department_id INT PRIMARY KEY AUTO_INCREMENT,
        department_code VARCHAR(20) UNIQUE NOT NULL,
        department_name VARCHAR(100) NOT NULL,
        department_description TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Departments table created\n";
    
    // Create roles table
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        role_id INT PRIMARY KEY AUTO_INCREMENT,
        role_code VARCHAR(50) UNIQUE NOT NULL,
        role_name VARCHAR(100) NOT NULL,
        display_name VARCHAR(100) NOT NULL,
        description TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Roles table created\n";
      // Create user_roles table
    $sql = "CREATE TABLE IF NOT EXISTS user_roles (
        user_role_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        role_id INT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_role (user_id, role_id)
    )";
    $pdo->exec($sql);
    echo "✅ User_roles table created\n";
    
    // Modify users table to add new columns if they don't exist
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('employee_id', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN employee_id VARCHAR(20) NULL UNIQUE AFTER email");
        echo "✅ Added employee_id column to users\n";
    }
    
    if (!in_array('department_id', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN department_id INT NULL AFTER employee_id");
        echo "✅ Added department_id column to users\n";
    }
    
    if (!in_array('is_active', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER phone_number");
        echo "✅ Added is_active column to users\n";
    }
    
    // Insert sample departments
    $pdo->exec("INSERT IGNORE INTO departments (department_code, department_name, department_description) VALUES 
        ('ADMIN', 'ฝ่ายบริหาร', 'ฝ่ายบริหารงานทั่วไป'),
        ('ACADEMIC', 'ฝ่ายวิชาการ', 'ฝ่ายการเรียนการสอน'),
        ('STUDENT_AFFAIRS', 'ฝ่ายกิจการนักเรียน', 'ฝ่ายดูแลกิจกรรมนักเรียน'),
        ('FINANCE', 'ฝ่ายการเงิน', 'ฝ่ายการเงินและบัญชี')");
    echo "✅ Sample departments inserted\n";
      // Insert sample roles
    $pdo->exec("INSERT IGNORE INTO roles (role_code, role_name, role_description) VALUES 
        ('admin', 'ผู้ดูแลระบบ', 'ผู้ดูแลระบบทั้งหมด'),
        ('director', 'ผู้อำนวยการ', 'ผู้อำนวยการสถานศึกษา'),
        ('vp', 'รองผู้อำนวยการ', 'รองผู้อำนวยการ'),
        ('hod', 'หัวหน้าแผนก', 'หัวหน้าแผนก/ฝ่าย'),
        ('teacher', 'ครู', 'บุคลากรสอน'),
        ('officer', 'เจ้าหน้าที่', 'เจ้าหน้าที่ทั่วไป'),
        ('student', 'นักเรียน', 'นักเรียน'),
        ('parent', 'ผู้ปกครอง', 'ผู้ปกครองนักเรียน')");
    echo "✅ Sample roles inserted\n";
    
    echo "\n🎉 Essential tables setup completed!\n";
    
    // Verify tables
    echo "\nVerifying tables:\n";
    $stmt = $pdo->query("SHOW TABLES");
    foreach($stmt->fetchAll(PDO::FETCH_COLUMN) as $table) {
        echo "- $table\n";
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
