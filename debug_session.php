<?php
// debug_session.php - File untuk debugging session flow
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Session Flow</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .step { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        button { padding: 10px 15px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔍 Debug Session Flow - KreasiLokal.id</h1>
    
    <div class="step info">
        <h3>Step 1: PHP Session Configuration</h3>
        <pre>
Session Status: <?php echo session_status(); ?> (1=disabled, 2=active)
Session Module Name: <?php echo session_module_name(); ?>
Session Save Path: <?php echo session_save_path(); ?>
Session Cookie Params: <?php print_r(session_get_cookie_params()); ?>
        </pre>
    </div>

    <?php
    echo "<div class='step info'>";
    echo "<h3>Step 2: Include Session Helper</h3>";
    echo "<pre>Attempting to include session_helper.php...</pre>";
    
    try {
        require_once 'session_helper.php';
        echo "<div class='success'>✅ session_helper.php loaded successfully</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error loading session_helper.php: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
    ?>

    <div class="step">
        <h3>Step 3: Session Data Analysis</h3>
        <pre>
Session ID: <?php echo session_id(); ?>
Session Data:
<?php print_r($_SESSION); ?>
        </pre>
    </div>

    <div class="step">
        <h3>Step 4: Function Tests</h3>
        <?php
        echo "<strong>Testing is_logged_in():</strong> ";
        if (function_exists('is_logged_in')) {
            $logged_in = is_logged_in();
            echo $logged_in ? "✅ TRUE" : "❌ FALSE";
            echo "<br>";
        } else {
            echo "❌ Function not found<br>";
        }

        echo "<strong>Testing get_logged_user():</strong> ";
        if (function_exists('get_logged_user')) {
            $user = get_logged_user();
            if ($user) {
                echo "✅ User data found<br>";
                echo "<pre>" . print_r($user, true) . "</pre>";
            } else {
                echo "❌ No user data<br>";
            }
        } else {
            echo "❌ Function not found<br>";
        }
        ?>
    </div>

    <div class="step">
        <h3>Step 5: Cookie Analysis</h3>
        <pre>
All Cookies:
<?php print_r($_COOKIE); ?>
        </pre>
    </div>

    <div class="step">
        <h3>Step 6: Server Environment</h3>
        <pre>
Server Software: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
PHP Version: <?php echo PHP_VERSION; ?>
Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?>
Current Script: <?php echo $_SERVER['SCRIPT_NAME'] ?? 'Unknown'; ?>
Request URI: <?php echo $_SERVER['REQUEST_URI'] ?? 'Unknown'; ?>
HTTP Host: <?php echo $_SERVER['HTTP_HOST'] ?? 'Unknown'; ?>
        </pre>
    </div>

    <div class="step">
        <h3>Step 7: Test Navigation</h3>
        <p>Test navigasi antar halaman:</p>
        <button onclick="location.href='index.php'">🏠 Ke Beranda</button>
        <button onclick="location.href='loginpenjual.php'">👤 Login Penjual</button>
        <button onclick="location.href='loginpembeli.php'">🛍️ Login Pembeli</button>
        <button onclick="location.href='test_session.php'">🔧 Test Session</button>
        <button onclick="location.href='logout.php'">🚪 Logout</button>
    </div>

    <div class="step">
        <h3>Step 8: AJAX Session Check</h3>
        <button onclick="testAjaxSession()">Test AJAX Session</button>
        <div id="ajaxResult"></div>
    </div>

    <div class="step warning">
        <h3>⚠️ Common Issues & Solutions</h3>
        <ul>
            <li><strong>Session ID berubah:</strong> Periksa session.cookie_domain dan session.cookie_path</li>
            <li><strong>Session hilang antar halaman:</strong> Pastikan session_start() dipanggil di setiap halaman</li>
            <li><strong>Headers already sent:</strong> Jangan ada output sebelum session_start()</li>
            <li><strong>Function conflicts:</strong> Periksa nama fungsi yang bentrok dengan PHP built-in</li>
        </ul>
    </div>

    <script>
        function testAjaxSession() {
            const resultDiv = document.getElementById('ajaxResult');
            resultDiv.innerHTML = 'Testing...';
            
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    resultDiv.innerHTML = `
                        <div class="${data.valid ? 'success' : 'error'}">
                            <strong>AJAX Result:</strong><br>
                            Valid: ${data.valid}<br>
                            User ID: ${data.user_id || 'None'}<br>
                            Role: ${data.role || 'None'}<br>
                            Message: ${data.message}
                        </div>
                    `;
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="error">AJAX Error: ${error}</div>`;
                });
        }

        // Auto refresh every 30 seconds to monitor session
        let autoRefresh = false;
        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            if (autoRefresh) {
                setInterval(() => location.reload(), 30000);
                alert('Auto refresh enabled (30s)');
            }
        }
    </script>

    <button onclick="toggleAutoRefresh()">Toggle Auto Refresh</button>
    <button onclick="location.reload()">🔄 Refresh</button>
</body>
</html>