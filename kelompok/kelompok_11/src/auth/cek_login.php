<?php
/**
 * ================================================
 * CEK LOGIN - FINAL FIX
 * ================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function check_role($allowed_roles = []) {
    if (!is_logged_in()) {
        return false;
    }
    
    if (empty($allowed_roles)) {
        return true;
    }
    
    return in_array($_SESSION['role'], $allowed_roles);
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash_message'] = "Silakan login terlebih dahulu!";
        $_SESSION['flash_type'] = "warning";
        header("Location: ../auth/login.php");
        exit();
    }
}

function require_role($allowed_roles = []) {
    require_login();
    
    if (!check_role($allowed_roles)) {
        $_SESSION['flash_message'] = "Anda tidak memiliki akses!";
        $_SESSION['flash_type'] = "danger";
        header("Location: ../dashboard/index.php");
        exit();
    }
}

function redirect_if_logged_in() {
    if (is_logged_in()) {
        header("Location: ../dashboard/index.php");
        exit();
    }
}

function get_user_data() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role']
    ];
}

function set_user_session($user_data) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['full_name'] = $user_data['full_name'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['role'] = $user_data['role'];
    $_SESSION['login_time'] = time();
}

function destroy_user_session() {
    session_unset();
    session_destroy();
}

function set_flash($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return ['message' => $message, 'type' => $type];
    }
    
    return null;
}
?>