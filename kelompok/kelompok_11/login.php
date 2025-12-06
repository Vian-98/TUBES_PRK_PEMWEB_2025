<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bengkel UMKM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="bg-blue-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-tools text-4xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Bengkel UMKM</h1>
            <p class="text-gray-600 mt-2">Point of Sale System</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle"></i>
            <?php 
                if ($_GET['error'] == 'invalid') echo 'Email atau password salah!';
                elseif ($_GET['error'] == 'empty') echo 'Email dan password harus diisi!';
                else echo 'Terjadi kesalahan!';
            ?>
        </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-envelope text-gray-500"></i> Email
                </label>
                <input type="email" name="email" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="kasir@bengkel.com">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock text-gray-500"></i> Password
                </label>
                <input type="password" name="password" required 
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="••••••••">
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 rounded-lg shadow-lg transition transform hover:scale-105">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="mt-6 p-4 bg-gray-100 rounded-lg">
            <p class="text-xs text-gray-600 font-semibold mb-2">Demo Akun:</p>
            <p class="text-xs text-gray-600">
                <strong>Kasir:</strong> kasir1@bengkel.com / password<br>
                <strong>Admin:</strong> admin@bengkel.com / password
            </p>
        </div>

        <div class="mt-6 text-center text-xs text-gray-500">
            <p>&copy; 2025 Bengkel UMKM. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
