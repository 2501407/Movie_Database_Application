<?php
session_start();

// Destroy all session data
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42840,
        $params["path"],
        $params["domain"],
        isset($params["secure"]),
        isset($params["httponly"])
    );
}
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
?>
