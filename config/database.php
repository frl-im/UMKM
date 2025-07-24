<?php
// config/database.php

$host = '127.0.0.1';
$db   = 'kreasidb'; // Pastikan nama ini sama dengan database yang Anda buat
$user = 'root';
$pass = ''; // Kosongkan jika tidak ada password di XAMPP Anda
$charset = 'utf8mb4';

try {
     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
     die("Koneksi ke database gagal: " . $e->getMessage());
}

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Set timezone
    $pdo->exec("SET time_zone = '+07:00'"); // WIB timezone
    
} catch (PDOException $e) {
    // Log error (in production, don't show sensitive information)
    error_log('Database connection failed: ' . $e->getMessage());
    
    // Show user-friendly error
    die('Database connection failed. Please try again later.');
}

// Function to test database connection
function testDatabaseConnection() {
    global $pdo;
    try {
        $stmt = $pdo->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Function to initialize database tables
function initializeTables() {
    global $pdo;
    
    try {
        // Users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                fullname VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('pembeli', 'penjual', 'admin') DEFAULT 'pembeli',
                store_name VARCHAR(255) NULL,
                phone VARCHAR(20) NULL,
                address TEXT NULL,
                profile_image VARCHAR(255) NULL,
                is_active BOOLEAN DEFAULT TRUE,
                email_verified BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_email (email),
                INDEX idx_role (role),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Products table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INT PRIMARY KEY AUTO_INCREMENT,
                seller_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(15,2) NOT NULL,
                stock INT NOT NULL DEFAULT 0,
                category VARCHAR(100) NOT NULL,
                image_url VARCHAR(500) NULL,
                status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_seller (seller_id),
                INDEX idx_category (category),
                INDEX idx_status (status),
                INDEX idx_price (price)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Messages table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS messages (
                id INT PRIMARY KEY AUTO_INCREMENT,
                sender_id INT NOT NULL,
                receiver_id INT NOT NULL,
                message TEXT NOT NULL,
                message_type ENUM('text', 'image', 'file') DEFAULT 'text',
                chat_type ENUM('user', 'support') DEFAULT 'user',
                product_id INT NULL,
                ticket_id INT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
                
                INDEX idx_conversation (sender_id, receiver_id),
                INDEX idx_receiver_unread (receiver_id, is_read),
                INDEX idx_created_at (created_at),
                INDEX idx_chat_type (chat_type),
                INDEX idx_product_id (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Support tickets table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS support_tickets (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                subject VARCHAR(255) NOT NULL,
                category ENUM('general', 'order', 'product', 'technical') DEFAULT 'general',
                status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
                priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_status (user_id, status),
                INDEX idx_created_at (created_at),
                INDEX idx_category (category),
                INDEX idx_priority (priority)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Cart table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS cart (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                UNIQUE KEY unique_user_product (user_id, product_id),
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Orders table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                total_amount DECIMAL(15,2) NOT NULL,
                status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
                payment_method VARCHAR(50) NOT NULL,
                shipping_address TEXT NOT NULL,
                notes TEXT NULL,
                tracking_number VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_status (user_id, status),
                INDEX idx_created_at (created_at),
                INDEX idx_tracking (tracking_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Order items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                seller_id INT NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_order (order_id),
                INDEX idx_seller (seller_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Add foreign key for support tickets (after messages table is created)
        try {
            $pdo->exec("
                ALTER TABLE messages 
                ADD CONSTRAINT fk_messages_ticket_id 
                FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE SET NULL
            ");
        } catch (PDOException $e) {
            // Foreign key might already exist, ignore
        }

        return true;
    } catch (PDOException $e) {
        error_log('Table initialization failed: ' . $e->getMessage());
        return false;
    }
}

// Function to seed initial data
function seedInitialData() {
    global $pdo;
    
    try {
        // Check if admin user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount == 0) {
            // Create default admin user
            $stmt = $pdo->prepare("
                INSERT INTO users (fullname, email, password, role) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                'Customer Service Admin',
                'admin@kreasilokal.id',
                password_hash('admin123456', PASSWORD_DEFAULT),
                'admin'
            ]);
        }
        
        // Check if customer service user exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'cs@kreasilokal.id'");
        $stmt->execute();
        $csCount = $stmt->fetchColumn();
        
        if ($csCount == 0) {
            // Create default customer service user
            $stmt = $pdo->prepare("
                INSERT INTO users (fullname, email, password, role) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                'Customer Service',
                'cs@kreasilokal.id',
                password_hash('cs123456', PASSWORD_DEFAULT),
                'admin'
            ]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Data seeding failed: ' . $e->getMessage());
        return false;
    }
}

// Auto-initialize tables and seed data if needed
if (isset($_GET['init_db']) && $_GET['init_db'] === 'true') {
    if (initializeTables() && seedInitialData()) {
        echo "Database initialized successfully!";
    } else {
        echo "Database initialization failed!";
    }
    exit;
}

?>
