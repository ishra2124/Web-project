<?php

// Authentication Helper Functions

/**
 * Check if the user is logged in.
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Log the user in by setting session variables.
 * @param int $userId
 */
function loginUser($userId) {
    $_SESSION['user_id'] = $userId;
}

/**
 * Log the user out by destroying the session.
 */
function logoutUser() {
    session_destroy();
}

/**
 * Get the currently logged-in user ID.
 * @return int|null
 */
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

?>