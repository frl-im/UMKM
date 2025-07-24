<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keamanan Akun - KreasiLokal.id</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Umum */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { font-size: 1.5rem; font-weight: bold; color: #2e8b57; text-decoration: none; }
        .container { max-width: 900px; margin: 2rem auto; padding: 2rem; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .page-header { border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 2rem; display: flex; align-items: center; }
        .page-header i { color: #2e8b57; font-size: 1.5rem; margin-right: 1rem; }
        .page-header h2 { margin: 0; font-size: 1.8rem; font-weight: 600; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #555; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #2e8b57; }
        .btn { display: inline-block; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-primary { background-color: #2e8b57; color: white; }
        footer { text-align: center; margin-top: 3rem; padding: 1rem; color: #888; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="profilpembeli.php" class="logo">KreasiLokal.id</a>
    </nav>
    <div class="container">
        <div class="page-header">
            <i class="fas fa-shield-alt"></i>
            <h2>Keamanan Akun</h2>
        </div>
        <h4>Ubah Password</h4>
        <form action="#" method="POST">
            <div class="form-group">
                <label for="old_password">Password Lama</label>
                <input type="password" id="old_password" name="old_password">
            </div>
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" id="new_password" name="new_password">
            </div>
             <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            <div style="text-align: right; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Ubah Password</button>
            </div>
        </form>
    </div>
    <footer>
        <p>&copy; 2025 KreasiLokal.id</p>
    </footer>
</body>
</html>