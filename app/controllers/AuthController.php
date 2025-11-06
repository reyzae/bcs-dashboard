<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/BaseController.php';

/**
 * Bytebalok Authentication Controller
 * Handles user authentication and session management
 */

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->userModel = new User($pdo);
    }
    
    /**
     * Handle login request
     */
    public function login() {
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getRequestData();
        $this->validateRequired($data, ['username', 'password']);
        
        $username = $this->sanitizeInput($data['username']);
        $password = $data['password'];
        
        $user = $this->userModel->login($username, $password);
        
        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            if (isset($user['email'])) {
                $_SESSION['user_email'] = $user['email'];
            }
            // Initialize session activity timestamp
            $_SESSION['last_activity'] = time();
            
            $this->logAction('login', 'users', $user['id']);
            $this->sendSuccess($user, 'Login successful');
        } else {
            $this->sendError('Invalid username or password', 401);
        }
    }
    
    /**
     * Handle logout request
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            $this->logAction('logout', 'users', $_SESSION['user_id']);
        }
        
        session_destroy();
        $this->sendSuccess(null, 'Logout successful');
    }
    
    /**
     * Get current user info
     */
    public function me() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Not authenticated', 401);
        }
        
        $user = $this->userModel->find($_SESSION['user_id']);
        if ($user) {
            $user = $this->userModel->hideFields($user);
            $this->sendSuccess($user);
        } else {
            $this->sendError('User not found', 404);
        }
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getRequestData();
        $this->validateRequired($data, ['current_password', 'new_password']);
        // Enforce strong password policy
        $this->validate($data, [
            'new_password' => ['password']
        ]);
        
        $currentPassword = $data['current_password'];
        $newPassword = $data['new_password'];
        
        // Verify current password
        $user = $this->userModel->find($_SESSION['user_id']);
        if (!password_verify($currentPassword, $user['password'])) {
            $this->sendError('Current password is incorrect', 400);
        }
        
        // Update password
        $success = $this->userModel->updatePassword($_SESSION['user_id'], $newPassword);
        
        if ($success) {
            $this->logAction('change_password', 'users', $_SESSION['user_id']);
            $this->sendSuccess(null, 'Password changed successfully');
        } else {
            $this->sendError('Failed to change password', 500);
        }
    }
    
    /**
     * Update profile
     */
    public function updateProfile() {
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getRequestData();
        $this->validateRequired($data, ['full_name']);
        
        $updateData = [
            'full_name' => $this->sanitizeInput($data['full_name']),
            'phone' => isset($data['phone']) ? $this->sanitizeInput($data['phone']) : null,
            'email' => isset($data['email']) ? $this->sanitizeInput($data['email']) : null
        ];
        
        // Check if email is being changed and if it already exists
        if (isset($data['email']) && $data['email'] !== $_SESSION['email']) {
            if ($this->userModel->emailExists($data['email'], $_SESSION['user_id'])) {
                $this->sendError('Email already exists', 400);
            }
        }
        
        $oldUser = $this->userModel->find($_SESSION['user_id']);
        $success = $this->userModel->update($_SESSION['user_id'], $updateData);
        
        if ($success) {
            $this->logAction('update_profile', 'users', $_SESSION['user_id'], $oldUser, $updateData);
            $this->sendSuccess(null, 'Profile updated successfully');
        } else {
            $this->sendError('Failed to update profile', 500);
        }
    }
    
    // ========================================
    // USER MANAGEMENT METHODS (ADMIN ONLY)
    // ========================================
    
    /**
     * Check if current user is admin
     */
    protected function requireAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->sendError('Unauthorized. Admin access required.', 403);
        }
    }
    
    /**
     * Get all users (Admin only)
     */
    public function getUsers() {
        $this->requireAdmin();
        
        try {
            $stmt = $this->pdo->query("
                SELECT id, username, full_name, email, role, is_active, created_at, updated_at
                FROM users
                ORDER BY created_at DESC
            ");
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert is_active (1/0) to status (active/inactive) for frontend
            foreach ($users as &$user) {
                $user['status'] = $user['is_active'] ? 'active' : 'inactive';
                unset($user['is_active']);
            }
            
            $this->sendSuccess($users);
        } catch (PDOException $e) {
            $this->sendError('Failed to fetch users: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Alias for getUsers to match api_dashboard routing
     */
    public function listUsers() {
        $this->getUsers();
    }
    
    /**
     * Get single user by ID (Admin only)
     */
    public function getUser() {
        $this->requireAdmin();
        
        $userId = $_GET['id'] ?? null;
        
        if (!$userId) {
            $this->sendError('User ID is required', 400);
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, full_name, email, role, is_active, created_at, updated_at
                FROM users
                WHERE id = ?
            ");
            
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->sendError('User not found', 404);
            }
            
            // Convert is_active (1/0) to status (active/inactive)
            $user['status'] = $user['is_active'] ? 'active' : 'inactive';
            unset($user['is_active']);
            
            $this->sendSuccess($user);
        } catch (PDOException $e) {
            $this->sendError('Failed to fetch user: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get user statistics (Admin only)
     */
    public function getUserStats() {
        $this->requireAdmin();
        
        try {
            // Total users
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users");
            $totalUsers = $stmt->fetchColumn();
            
            // Active users
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
            $activeUsers = $stmt->fetchColumn();
            
            // Inactive users
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 0");
            $inactiveUsers = $stmt->fetchColumn();
            
            // Admins count
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $adminCount = $stmt->fetchColumn();
            
            // Users by role
            $stmt = $this->pdo->query("
                SELECT role, COUNT(*) as count
                FROM users
                GROUP BY role
            ");
            $usersByRole = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->sendSuccess([
                'total' => (int)$totalUsers,
                'active' => (int)$activeUsers,
                'inactive' => (int)$inactiveUsers,
                'admin_count' => (int)$adminCount,
                'by_role' => $usersByRole
            ]);
        } catch (PDOException $e) {
            $this->sendError('Failed to fetch user stats: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Create new user (Admin only)
     */
    public function createUser() {
        $this->requireAdmin();
        
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getRequestData();
        
        // Validate required fields
        $this->validateRequired($data, ['username', 'full_name', 'email', 'role', 'password']);
        
        // Validate password
        if (strlen($data['password']) < 6) {
            $this->sendError('Password must be at least 6 characters', 400);
        }
        
        // Check if username exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$data['username']]);
        if ($stmt->fetch()) {
            $this->sendError('Username already exists', 400);
        }
        
        // Check if email exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            $this->sendError('Email already exists', 400);
        }
        
        // Validate role
        $validRoles = ['admin', 'manager', 'cashier', 'staff'];
        if (!in_array($data['role'], $validRoles)) {
            $this->sendError('Invalid role', 400);
        }
        
        try {
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Convert status (active/inactive) to is_active (1/0)
            $isActive = isset($data['status']) && $data['status'] === 'active' ? 1 : 0;
            
            // Insert user
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, password, full_name, email, role, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['username'],
                $hashedPassword,
                $data['full_name'],
                $data['email'],
                $data['role'],
                $isActive
            ]);
            
            $userId = $this->pdo->lastInsertId();
            
            // Log action
            $this->logAction('create_user', 'users', $userId);
            
            $this->sendSuccess(['id' => $userId], 'User created successfully');
        } catch (PDOException $e) {
            $this->sendError('Failed to create user: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update user (Admin only)
     */
    public function updateUser() {
        $this->requireAdmin();
        
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getRequestData();
        
        // Validate required fields
        $this->validateRequired($data, ['id', 'username', 'full_name', 'email', 'role', 'status']);
        
        $userId = $data['id'];
        
        // Check if user exists
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingUser) {
            $this->sendError('User not found', 404);
        }
        
        // Check if username is taken by another user
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$data['username'], $userId]);
        if ($stmt->fetch()) {
            $this->sendError('Username already exists', 400);
        }
        
        // Check if email is taken by another user
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->fetch()) {
            $this->sendError('Email already exists', 400);
        }
        
        // Validate role
        $validRoles = ['admin', 'manager', 'cashier', 'staff'];
        if (!in_array($data['role'], $validRoles)) {
            $this->sendError('Invalid role', 400);
        }
        
        try {
            // Convert status (active/inactive) to is_active (1/0)
            $isActive = isset($data['status']) && $data['status'] === 'active' ? 1 : 0;
            
            // Update user
            if (isset($data['password']) && !empty($data['password'])) {
                // Update with new password
                if (strlen($data['password']) < 6) {
                    $this->sendError('Password must be at least 6 characters', 400);
                }
                
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                
                $stmt = $this->pdo->prepare("
                    UPDATE users
                    SET username = ?, password = ?, full_name = ?, email = ?, 
                        role = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $data['username'],
                    $hashedPassword,
                    $data['full_name'],
                    $data['email'],
                    $data['role'],
                    $isActive,
                    $userId
                ]);
            } else {
                // Update without changing password
                $stmt = $this->pdo->prepare("
                    UPDATE users
                    SET username = ?, full_name = ?, email = ?, 
                        role = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $data['username'],
                    $data['full_name'],
                    $data['email'],
                    $data['role'],
                    $isActive,
                    $userId
                ]);
            }
            
            // Log action
            $this->logAction('update_user', 'users', $userId, $existingUser, $data);
            
            $this->sendSuccess(null, 'User updated successfully');
        } catch (PDOException $e) {
            $this->sendError('Failed to update user: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Delete user (Admin only)
     */
    public function deleteUser() {
        $this->requireAdmin();
        
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getRequestData();
        $this->validateRequired($data, ['id']);
        
        $userId = $data['id'];
        
        // Prevent deleting self
        if ($userId == $_SESSION['user_id']) {
            $this->sendError('Cannot delete your own account', 400);
        }
        
        // Check if user exists
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $this->sendError('User not found', 404);
        }
        
        try {
            // Delete user
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            // Log action
            $this->logAction('delete_user', 'users', $userId, $user);
            
            $this->sendSuccess(null, 'User deleted successfully');
        } catch (PDOException $e) {
            $this->sendError('Failed to delete user: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Reset user password (Admin only)
     */
    public function resetPassword() {
        $this->requireAdmin();
        
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getRequestData();
        $this->validateRequired($data, ['id', 'new_password']);
        
        $userId = $data['id'];
        $newPassword = $data['new_password'];
        
        // Validate password length
        if (strlen($newPassword) < 6) {
            $this->sendError('Password must be at least 6 characters', 400);
        }
        
        // Check if user exists
        $stmt = $this->pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $this->sendError('User not found', 404);
        }
        
        try {
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $this->pdo->prepare("
                UPDATE users
                SET password = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([$hashedPassword, $userId]);
            
            // Log action
            $this->logAction('reset_password', 'users', $userId);
            
            $this->sendSuccess(null, 'Password reset successfully');
        } catch (PDOException $e) {
            $this->sendError('Failed to reset password: ' . $e->getMessage(), 500);
        }
    }
}

// Handle requests
$authController = new AuthController($pdo);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $authController->login();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'me':
        $authController->me();
        break;
    case 'change-password':
        $authController->changePassword();
        break;
    case 'update-profile':
        $authController->updateProfile();
        break;
    
    // User Management (Admin Only)
    case 'getUsers':
        $authController->getUsers();
        break;
    case 'createUser':
        $authController->createUser();
        break;
    case 'updateUser':
        $authController->updateUser();
        break;
    case 'deleteUser':
        $authController->deleteUser();
        break;
    case 'resetPassword':
        $authController->resetPassword();
        break;
    
    default:
        $authController->sendError('Invalid action', 400);
}