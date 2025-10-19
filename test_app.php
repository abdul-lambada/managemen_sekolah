<?php
// Test script untuk memverifikasi aplikasi
echo "=== TESTING APLIKASI MANAJEMEN SEKOLAH ===\n\n";

// Test 1: Cek konfigurasi database
echo "1. Testing database connection...\n";
try {
    require_once 'config/database.php';
    $pdo = db();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 2: Cek autoloader
echo "\n2. Testing autoloader...\n";
try {
    require_once 'autoload.php';
    echo "✅ Autoloader loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Autoloader failed: " . $e->getMessage() . "\n";
}

// Test 3: Cek helper files
echo "\n3. Testing helper files...\n";
$helpers = ['app.php', 'view.php', 'formatter.php'];
foreach ($helpers as $helper) {
    $file = "helpers/$helper";
    if (file_exists($file)) {
        echo "✅ Helper $helper exists\n";
    } else {
        echo "❌ Helper $helper missing\n";
    }
}

// Test 4: Cek controller files
echo "\n4. Testing controller files...\n";
$controllers = ['DashboardController', 'AuthController', 'GuruController'];
foreach ($controllers as $controller) {
    $file = "app/controllers/{$controller}.php";
    if (file_exists($file)) {
        echo "✅ Controller $controller exists\n";
    } else {
        echo "❌ Controller $controller missing\n";
    }
}

// Test 5: Cek model files
echo "\n5. Testing model files...\n";
$models = ['User', 'Guru', 'Siswa'];
foreach ($models as $model) {
    $file = "app/models/{$model}.php";
    if (file_exists($file)) {
        echo "✅ Model $model exists\n";
    } else {
        echo "❌ Model $model missing\n";
    }
}

echo "\n=== TESTING SELESAI ===\n";
?>
