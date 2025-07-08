<?php
require_once 'config.php';

function authenticate($login, $password) {
    global $pdo;
    
    if (empty($login) || empty($password)) {
        error_log("Authentication failed: Empty credentials");
        return false;
    }

    $role_tables = [
        'admin' => ['table' => 'Administrateur', 'id_field' => 'id_admin'],
        'enseignant' => ['table' => 'Enseignant', 'id_field' => 'matricule'],
        'etudiant' => ['table' => 'Etudiant', 'id_field' => 'NCE']
    ];
    
    try {
        foreach ($role_tables as $role => $table_info) {
            error_log("Attempting to query {$table_info['table']} for login: $login");
            $stmt = $pdo->prepare("SELECT {$table_info['id_field']}, login, mot_de_passe 
                                   FROM {$table_info['table']} 
                                   WHERE LOWER(login) = LOWER(:login)");
            $stmt->bindParam(':login', $login, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user) {
                error_log("Found user in {$table_info['table']}: login={$user['login']}, stored_password_hash={$user['mot_de_passe']}");
                
                if (password_verify($password, $user['mot_de_passe'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user[$table_info['id_field']];
                    $_SESSION['role'] = $role;
                    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['last_activity'] = time();
                    
                    error_log("Authentication success for $login as $role, user_id={$_SESSION['user_id']}");
                    return true;
                } else {
                    error_log("Password verification failed for $login in {$table_info['table']}. Entered password: $password, stored hash: {$user['mot_de_passe']}");
                }
            } else {
                error_log("No user found in {$table_info['table']} for login: $login");
            }
        }
        
        error_log("Authentication failed: Invalid credentials for $login");
        return false;

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

function isLoggedIn() {
    $isValid = isset($_SESSION['user_id'], 
                     $_SESSION['role'],
                     $_SESSION['ip'],
                     $_SESSION['user_agent'],
                     $_SESSION['last_activity']) &&
               $_SESSION['ip'] === $_SERVER['REMOTE_ADDR'] &&
               $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'] &&
               (time() - $_SESSION['last_activity'] < 3600);
    error_log("isLoggedIn: " . ($isValid ? "True" : "False") . ", role=" . ($_SESSION['role'] ?? 'null'));
    return $isValid;
}

function redirectByRole() {
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
        $basePath = '/projet_soutenance';
        $redirectUrl = $basePath . "/" . $_SESSION['role'] . "/dashboard.php";
        error_log("Redirecting to: $redirectUrl");
        header("Location: http://localhost" . $redirectUrl);
        exit();
    }
    $basePath = '/projet_soutenance';
    $redirectUrl = $basePath . "/login.php";
    error_log("Redirecting to login page: $redirectUrl");
    header("Location: http://localhost" . $redirectUrl);
    exit();
}

function logout() {
    session_unset();
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    session_destroy();
    $basePath = '/projet_soutenance';
    $redirectUrl = $basePath . "/login.php";
    error_log("Logging out, redirecting to: $redirectUrl");
    header("Location: http://localhost" . $redirectUrl);
    exit();
}

if (isset($_GET['logout'])) {
    logout();
}
?>