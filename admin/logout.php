<?php
session_start();

// 🔒 Hapus semua session
$_SESSION = [];
session_unset();
session_destroy();

// 🔧 Hapus cookie PHPSESSID (kalau ada)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 🚫 Mencegah caching halaman sebelumnya (biar tombol Back nggak bisa balik)
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// 🔁 Redirect ke halaman login dengan pesan sukses
header("Location: index.php?logout=1");
exit();
?>
