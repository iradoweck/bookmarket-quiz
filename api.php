<?php
/**
 * BookMarket Research — API RESTful
 * Desenvolvido por Zedeck's IT
 */

session_start();

// Configuração de Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

$DATA_DIR = __DIR__ . '/data';
$INTERVIEWS_FILE = $DATA_DIR . '/interviews.json';
$SETTINGS_FILE = $DATA_DIR . '/settings.json';
$LOG_FILE = $DATA_DIR . '/app.log';

// Garantir diretório data
if (!file_exists($DATA_DIR)) {
    @mkdir($DATA_DIR, 0755, true);
}

// Inicializar Ficheiros se não existirem
if (!file_exists($SETTINGS_FILE)) {
    $defaultSettings = [
        'admin_user' => 'admin',
        'admin_pass_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'survey_name' => 'BookMarket Research Moçambique',
        'interviewer_default' => 'Pesquisador',
        'target_goal' => 10,
        'cities' => ['Nampula', 'Nacala', 'Pemba', 'Maputo', 'Matola', 'Beira', 'Chimoio', 'Quelimane', 'Tete', 'Xai-Xai', 'Inhambane', 'Vilankulo', 'Outra'],
        'currency' => 'MT'
    ];
    file_put_contents($SETTINGS_FILE, json_encode($defaultSettings, JSON_PRETTY_PRINT));
}

if (!file_exists($INTERVIEWS_FILE)) {
    $defaultData = [
        'version' => 1,
        'updated_at' => date('c'),
        'interviews' => []
    ];
    file_put_contents($INTERVIEWS_FILE, json_encode($defaultData, JSON_PRETTY_PRINT));
}

// Utilitários
function logger($message) {
    global $LOG_FILE;
    $time = date('Y-m-d H:i:s');
    @file_put_contents($LOG_FILE, "[$time] $message\n", FILE_APPEND | LOCK_EX);
}

function respondJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    if (is_string($data)) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

function checkAdminAuth() {
    if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        respondJson(['success' => false, 'error' => 'Acesso não autorizado'], 401);
    }
}

// Concorrência Segura para Gravação de JSON
function readJsonSafe($filePath) {
    if (!file_exists($filePath)) return [];
    $fp = fopen($filePath, 'r');
    if (!$fp) return [];
    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return json_decode($content, true) ?: [];
}

function writeJsonSafe($filePath, $data) {
    $fp = fopen($filePath, 'c+');
    if (!$fp) return false;
    
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
    fclose($fp);
    return false;
}

// Rate limit simples por sessão/IP
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateKey = 'rate_' . md5($ip);
if (!isset($_SESSION[$rateKey])) {
    $_SESSION[$rateKey] = ['count' => 1, 'start' => time()];
} else {
    if (time() - $_SESSION[$rateKey]['start'] < 60) {
        $_SESSION[$rateKey]['count']++;
        if ($_SESSION[$rateKey]['count'] > 60) { // limite de 60 requisições por min
            respondJson(['success' => false, 'error' => 'Demasiados pedidos. Tente mais tarde.'], 429);
        }
    } else {
        $_SESSION[$rateKey] = ['count' => 1, 'start' => time()];
    }
}

// Roteamento de Ações
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'health':
        respondJson([
            'success' => true,
            'server_time' => date('Y-m-d H:i:s'),
            'storage' => is_writable($DATA_DIR) ? 'ok' : 'read-only'
        ]);
        break;

    case 'login':
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        $user = trim($input['username'] ?? '');
        $pass = trim($input['password'] ?? '');

        $settings = readJsonSafe($SETTINGS_FILE);

        if ($user === $settings['admin_user'] && password_verify($pass, $settings['admin_pass_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            logger("Login bem-sucedido para o utilizador: $user");
            respondJson([
                'success' => true,
                'csrf_token' => $_SESSION['csrf_token'],
                'message' => 'Autenticação com sucesso'
            ]);
        } else {
            logger("Tentativa de login falhada para: $user de $ip");
            respondJson(['success' => false, 'error' => 'Credenciais inválidas'], 401);
        }
        break;

    case 'logout':
        session_destroy();
        respondJson(['success' => true, 'message' => 'Sessão encerrada']);
        break;

    case 'checkAuth':
        if (!empty($_SESSION['admin_logged_in'])) {
            respondJson(['success' => true, 'csrf_token' => $_SESSION['csrf_token'] ?? '']);
        } else {
            respondJson(['success' => false, 'logged' => false]);
        }
        break;

    case 'saveInterview':
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);

        if (!$payload || !is_array($payload)) {
            respondJson(['success' => false, 'error' => 'Dados inválidos recebidos.'], 400);
        }

        $cleanData = sanitizeInput($payload);

        // Garantir ID
        $dataStorage = readJsonSafe($INTERVIEWS_FILE);
        if (!isset($dataStorage['interviews'])) {
            $dataStorage['interviews'] = [];
        }

        $datePrefix = date('Ymd');
        $countToday = 1;
        foreach ($dataStorage['interviews'] as $item) {
            if (isset($item['id']) && strpos($item['id'], "ENT-$datePrefix") === 0) {
                $countToday++;
            }
        }
        
        $newId = sprintf("ENT-%s-%04d", $datePrefix, $countToday);
        $cleanData['id'] = $newId;
        $cleanData['createdAt'] = date('c');
        $cleanData['updatedAt'] = date('c');

        $dataStorage['interviews'][] = $cleanData;
        $dataStorage['updated_at'] = date('c');

        if (writeJsonSafe($INTERVIEWS_FILE, $dataStorage)) {
            logger("Nova entrevista gravada com ID $newId");
            respondJson(['success' => true, 'id' => $newId, 'message' => 'Entrevista gravada com sucesso!']);
        } else {
            respondJson(['success' => false, 'error' => 'Erro ao escrever no ficheiro de dados.'], 500);
        }
        break;

    case 'getInterviews':
        checkAdminAuth();
        $dataStorage = readJsonSafe($INTERVIEWS_FILE);
        $interviews = $dataStorage['interviews'] ?? [];
        
        // Ordenar do mais recente para o mais antigo
        usort($interviews, function($a, $b) {
            return strtotime($b['createdAt'] ?? 0) - strtotime($a['createdAt'] ?? 0);
        });

        respondJson([
            'success' => true,
            'updated_at' => $dataStorage['updated_at'] ?? date('c'),
            'total' => count($interviews),
            'interviews' => $interviews
        ]);
        break;

    case 'getStats':
        checkAdminAuth();
        $dataStorage = readJsonSafe($INTERVIEWS_FILE);
        $settings = readJsonSafe($SETTINGS_FILE);
        $interviews = $dataStorage['interviews'] ?? [];

        respondJson([
            'success' => true,
            'updated_at' => $dataStorage['updated_at'] ?? date('c'),
            'total' => count($interviews),
            'target_goal' => $settings['target_goal'] ?? 10,
            'interviews' => $interviews
        ]);
        break;

    case 'deleteInterview':
        checkAdminAuth();
        $id = $_GET['id'] ?? '';
        if (!$id) respondJson(['success' => false, 'error' => 'ID não especificado'], 400);

        $dataStorage = readJsonSafe($INTERVIEWS_FILE);
        $interviews = $dataStorage['interviews'] ?? [];
        $filtered = array_filter($interviews, fn($i) => ($i['id'] ?? '') !== $id);

        if (count($interviews) === count($filtered)) {
            respondJson(['success' => false, 'error' => 'Entrevista não encontrada'], 404);
        }

        $dataStorage['interviews'] = array_values($filtered);
        $dataStorage['updated_at'] = date('c');

        if (writeJsonSafe($INTERVIEWS_FILE, $dataStorage)) {
            logger("Entrevista $id eliminada");
            respondJson(['success' => true, 'message' => 'Entrevista eliminada com sucesso']);
        } else {
            respondJson(['success' => false, 'error' => 'Erro ao atualizar dados'], 500);
        }
        break;

    case 'getSettings':
        checkAdminAuth();
        $settings = readJsonSafe($SETTINGS_FILE);
        unset($settings['admin_pass_hash']);
        respondJson(['success' => true, 'settings' => $settings]);
        break;

    case 'saveSettings':
        checkAdminAuth();
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        $currentSettings = readJsonSafe($SETTINGS_FILE);

        if (!empty($input['survey_name'])) $currentSettings['survey_name'] = sanitizeInput($input['survey_name']);
        if (!empty($input['interviewer_default'])) $currentSettings['interviewer_default'] = sanitizeInput($input['interviewer_default']);
        if (isset($input['target_goal'])) $currentSettings['target_goal'] = max(1, (int)$input['target_goal']);
        
        if (!empty($input['new_password'])) {
            $currentSettings['admin_pass_hash'] = password_hash($input['new_password'], PASSWORD_DEFAULT);
        }

        if (writeJsonSafe($SETTINGS_FILE, $currentSettings)) {
            logger("Configurações atualizadas");
            respondJson(['success' => true, 'message' => 'Configurações atualizadas com sucesso']);
        } else {
            respondJson(['success' => false, 'error' => 'Erro ao guardar configurações'], 500);
        }
        break;

    case 'backup':
        checkAdminAuth();
        $dataStorage = readJsonSafe($INTERVIEWS_FILE);
        $settings = readJsonSafe($SETTINGS_FILE);
        unset($settings['admin_pass_hash']);

        respondJson([
            'success' => true,
            'backup_date' => date('c'),
            'version' => $dataStorage['version'] ?? 1,
            'settings' => $settings,
            'interviews' => $dataStorage['interviews'] ?? []
        ]);
        break;

    case 'restoreBackup':
        checkAdminAuth();
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!isset($input['interviews']) || !is_array($input['interviews'])) {
            respondJson(['success' => false, 'error' => 'Estrutura de backup inválida'], 400);
        }

        $dataStorage = readJsonSafe($INTERVIEWS_FILE);
        $existing = $dataStorage['interviews'] ?? [];
        $existingIds = array_column($existing, 'id');

        $importedCount = 0;
        foreach ($input['interviews'] as $item) {
            $clean = sanitizeInput($item);
            if (!empty($clean['id']) && !in_array($clean['id'], $existingIds)) {
                $existing[] = $clean;
                $importedCount++;
            }
        }

        $dataStorage['interviews'] = $existing;
        $dataStorage['updated_at'] = date('c');

        if (writeJsonSafe($INTERVIEWS_FILE, $dataStorage)) {
            logger("Backup restaurado. $importedCount novas entrevistas adicionadas.");
            respondJson(['success' => true, 'message' => "Backup importado com sucesso! $importedCount novas entrevistas adicionadas."]);
        } else {
            respondJson(['success' => false, 'error' => 'Erro ao gravar restauro de backup'], 500);
        }
        break;

    default:
        respondJson(['success' => false, 'error' => 'Ação não reconhecida'], 400);
        break;
}