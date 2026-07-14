<?php
/**
 * AuthController — Authentification avec protection anti-brute-force
 */
class AuthController extends Controller {

    private const MAX_ATTEMPTS  = 5;   // tentatives max avant blocage
    private const LOCKOUT_MIN   = 15;  // minutes de blocage

    public function loginForm(): void {
        if (Auth::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $flash = $this->getFlash();
        $this->view('auth/login', ['flash' => $flash], 'auth');
    }

    public function login(): void {
        CSRF::check();
        $ip    = $this->getClientIP();
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Veuillez remplir tous les champs.');
            $this->redirect('/login');
            return;
        }

        // Vérifier le blocage par IP (table security_logs)
        if ($this->isLockedOut($ip, $email)) {
            $this->logSecurityEvent('login_blocked', $ip, $email, 'Compte bloqué — trop de tentatives');
            $this->flash('error', "Trop de tentatives de connexion. Compte temporairement bloqué pour " . self::LOCKOUT_MIN . " minutes.");
            $this->redirect('/login');
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT u.*, r.code as role_code, r.libelle as role_lib 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.email = :email AND u.actif = 1 
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $this->logSecurityEvent('login_failed', $ip, $email, 'Mot de passe incorrect');
            $attempts = $this->countRecentFailures($ip, $email);
            $remaining = self::MAX_ATTEMPTS - $attempts;
            $msg = 'Email ou mot de passe incorrect.';
            if ($remaining <= 2 && $remaining > 0) {
                $msg .= " Attention : encore {$remaining} tentative(s) avant blocage.";
            }
            $this->flash('error', $msg);
            $this->redirect('/login');
            return;
        }

        // Succès : effacer les tentatives ratées
        $this->logSecurityEvent('login_success', $ip, $email, 'Connexion réussie');
        Auth::login($user);

        // Récupérer l'URL d'intention
        $intended = $_SESSION['intended_url'] ?? '';
        unset($_SESSION['intended_url']);

        if (empty($intended) || strpos($intended, '/login') !== false) {
            $this->redirect('/dashboard');
            return;
        }

        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        if ($basePath && strpos($intended, $basePath) === 0) {
            $chemin = substr($intended, strlen($basePath)) ?: '/dashboard';
            $this->redirect($chemin);
        } else {
            $this->redirect($intended ?: '/dashboard');
        }
    }

    public function logout(): void {
        $user = Auth::currentUser();
        $ip   = $this->getClientIP();
        if ($user) {
            $this->logSecurityEvent('logout', $ip, $user['email'] ?? '', 'Déconnexion');
        }
        Auth::logout();
        $this->redirect('/login');
    }

    // ─── Helpers anti-brute-force ──────────────────────────────────────────
    private function isLockedOut(string $ip, string $email): bool
    {
        try {
            $since = date('Y-m-d H:i:s', strtotime('-' . self::LOCKOUT_MIN . ' minutes'));
            $stmt  = $this->db->prepare(
                "SELECT COUNT(*) FROM security_logs
                 WHERE action = 'login_failed'
                   AND (ip_address = ? OR details LIKE ?)
                   AND created_at >= ?
                 LIMIT 1"
            );
            $stmt->execute([$ip, '%' . $email . '%', $since]);
            return (int)$stmt->fetchColumn() >= self::MAX_ATTEMPTS;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function countRecentFailures(string $ip, string $email): int
    {
        try {
            $since = date('Y-m-d H:i:s', strtotime('-' . self::LOCKOUT_MIN . ' minutes'));
            $stmt  = $this->db->prepare(
                "SELECT COUNT(*) FROM security_logs
                 WHERE action = 'login_failed'
                   AND (ip_address = ? OR details LIKE ?)
                   AND created_at >= ?"
            );
            $stmt->execute([$ip, '%' . $email . '%', $since]);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function logSecurityEvent(string $action, string $ip, string $email, string $details): void
    {
        try {
            $this->db->prepare(
                "INSERT INTO security_logs (user_id, action, ip_address, user_agent, details)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([
                Auth::userId(),
                $action,
                $ip,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                $email . ' — ' . $details,
            ]);
        } catch (\Exception $e) {
            // Silencieux si la table n'existe pas encore
        }
    }

    private function getClientIP(): string
    {
        foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }
}
