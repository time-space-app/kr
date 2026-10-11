<?php
/**
 * DbSessionHandler.php
 * @description  로그인 시도 횟수와 잠금 상태를 관리하기 위한 클래스
 * @package     Tiny File Manager
 * @author      Tiny File Manager Team
 * @license     MIT License
 **/
class DbSessionHandler { // implements SessionHandlerInterface 사용하지 않음
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getLoginLockoutRemaining($username, $maxAttempts, $lockoutTime): int {
        $id = $this->getLoginAttemptId($username);
        $stmt = $this->db->prepare("SELECT data, access FROM sessions WHERE id = ?");
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare login attempt lookup: ' . $this->db->error);
        }
        $stmt->bind_param("s", $id);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Failed to read login attempts: ' . $error);
        }
        $result = $stmt->get_result();
        if (!$result) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Failed to read login attempts: ' . $error);
        }
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row || (int) $row['data'] < $maxAttempts) {
            return 0;
        }

        return max(0, $lockoutTime - (time() - (int) $row['access']));
    }

    public function recordLoginFailure($username, $lockoutTime): void {
        $id = $this->getLoginAttemptId($username);
        $now = time();
        $resetBefore = $now - $lockoutTime;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt = $this->db->prepare("
            INSERT INTO sessions (id, access, data, ip_address, user_agent)
            VALUES (?, ?, '1', ?, ?)
            ON DUPLICATE KEY UPDATE
                data = IF(access <= ?, '1', CAST(CAST(data AS UNSIGNED) + 1 AS CHAR)),
                access = ?,
                ip_address = ?,
                user_agent = ?
        ");
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare login attempt update: ' . $this->db->error);
        }
        $stmt->bind_param("sissiiss", $id, $now, $ip, $ua, $resetBefore, $now, $ip, $ua);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Failed to record login attempt: ' . $error);
        }
        $stmt->close();
    }

    public function resetLoginAttempts($username): void {
        $id = $this->getLoginAttemptId($username);
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare login attempt reset: ' . $this->db->error);
        }
        $stmt->bind_param("s", $id);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Failed to reset login attempts: ' . $error);
        }
        $stmt->close();
    }

    private function getLoginAttemptId($username): string {
        return 'login_attempt_' . hash('sha256', $username);
    }
    
    //사용되지 않지만 SessionHandlerInterface를 구현한다면 필요함(아래)
    /*
    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }    
    public function read($id): string {
		$access = time();
        $stmt = $this->db->prepare("SELECT data FROM sessions WHERE id = ? AND access > ?");
		$stmt->bind_param("si", $id, $access);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['data'];
        }
        return "";
    }

    public function write($id, $data): bool {
        $access = time();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Use prepared statements to prevent SQL injection
        $stmt = $this->db->prepare("
            INSERT INTO sessions (id, access, data, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE access = ?, data = ?, ip_address = ?, user_agent = ?
        ");
        $stmt->bind_param("sisssisss", $id, $access, $data, $ip, $ua, $access, $data, $ip, $ua);
        return $stmt->execute();
    }

    public function destroy($id): bool {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
		$stmt->bind_param("s", $id);
        return $stmt->execute();
    }
    
    public function gc($maxlifetime): int|false {
        $old = time() - $maxlifetime;
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE access < ?");
		$stmt->bind_param("i", $old);
        if ($stmt->execute()) {
            return $stmt->rowCount();
        }
        return false;
    }
    */
}
