<?php

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

loadEnv(__DIR__ . '/.env');

ini_set('upload_max_filesize', getenv('UPLOAD_MAX_FILESIZE') ?: '500M');
ini_set('post_max_size', getenv('POST_MAX_SIZE') ?: '500M');
ini_set('memory_limit', getenv('MEMORY_LIMIT') ?: '512M');

define('ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASS', getenv('ADMIN_PASS') ?: 'mudar123');
define('GUEST_USER', getenv('GUEST_USER') ?: 'amigos');
define('GUEST_PASS', getenv('GUEST_PASS') ?: 'amigos_A_R-2026');
define('BASE_DIR', getenv('BASE_DIR') ?: 'galeria/');

session_start();

define('ALLOWED_IMAGES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_VIDEOS', ['mp4', 'webm', 'ogg']);

if (!is_dir(BASE_DIR)) {
    mkdir(BASE_DIR, 0755, true);
}

// Conexão PDO MySQL
$dbHost = getenv('DB_HOST') ?: 'db';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'galeria_db';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: 'root_password';

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Processamento de Login (Admin e Amigos)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['logged_admin'] = true;
        unset($_SESSION['logged_guest']);
        header('Location: index.php');
        exit;
    } elseif ($user === GUEST_USER && $pass === GUEST_PASS) {
        $_SESSION['logged_guest'] = true;
        unset($_SESSION['logged_admin']);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuário ou senha inválidos.';
    }
}

// Verificação Global de Autenticação (Fim do Acesso Anônimo/Público)
$isLoggedIn = isset($_SESSION['logged_admin']) || isset($_SESSION['logged_guest']);
$isAdmin = isset($_SESSION['logged_admin']);

// Se não estiver logado, obriga a exibição da tela de login
if (!$isLoggedIn) {
    $_GET['login_screen'] = true;
}

// Listar Álbuns do MySQL
$albuns = [];
if ($isLoggedIn) {
    $stmt = $pdo->query("SELECT * FROM albuns ORDER BY nome ASC");
    $albuns = $stmt->fetchAll();
}

// Definir álbum atual
$album_atual_slug = $_GET['album'] ?? ($albuns[0]['slug'] ?? '');
$album_atual = null;

if ($isLoggedIn && !empty($album_atual_slug)) {
    $stmt = $pdo->prepare("SELECT * FROM albuns WHERE slug = :slug LIMIT 1");
    $stmt->execute(['slug' => $album_atual_slug]);
    $album_atual = $stmt->fetch() ?: null;
}

// Criar Novo Álbum (Apenas Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_album']) && $isAdmin) {
    $nome_album = trim($_POST['nome_album'] ?? '');
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '-', strtolower($nome_album)));

    if (!empty($nome_album) && !empty($slug)) {
        $stmt = $pdo->prepare("SELECT id FROM albuns WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);

        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO albuns (nome, slug) VALUES (:nome, :slug)");
            $stmt->execute(['nome' => $nome_album, 'slug' => $slug]);

            $path_novo_album = BASE_DIR . $slug;
            if (!is_dir($path_novo_album)) {
                mkdir($path_novo_album, 0755, true);
            }

            header('Location: index.php?album=' . urlencode($slug));
            exit;
        }
    }
}

// Upload de Mídia no Álbum Selecionado (Apenas Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload']) && $isAdmin && $album_atual) {
    if (isset($_FILES['arquivos']) && is_array($_FILES['arquivos']['name'])) {
        $totalFiles = count($_FILES['arquivos']['name']);
        $allowedExtensions = array_merge(ALLOWED_IMAGES, ALLOWED_VIDEOS);
        $current_album_path = BASE_DIR . $album_atual['slug'] . '/';

        for ($i = 0; $i < $totalFiles; $i++) {
            if ($_FILES['arquivos']['error'][$i] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['arquivos']['tmp_name'][$i];
                $originalName = $_FILES['arquivos']['name'][$i];
                $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                if (in_array($fileExtension, $allowedExtensions, true)) {
                    $newFileName = md5(time() . $originalName . $i) . '.' . $fileExtension;
                    $destPath = $current_album_path . $newFileName;
                    $tipo = in_array($fileExtension, ALLOWED_IMAGES, true) ? 'image' : 'video';

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $stmt = $pdo->prepare("INSERT INTO midias (album_id, nome_original, nome_arquivo, caminho, tipo, is_public) VALUES (:album_id, :nome_original, :nome_arquivo, :caminho, :tipo, 0)");
                        $stmt->execute([
                            'album_id' => $album_atual['id'],
                            'nome_original' => $originalName,
                            'nome_arquivo' => $newFileName,
                            'caminho' => $destPath,
                            'tipo' => $tipo
                        ]);
                    }
                }
            }
        }
    }
    header('Location: index.php?album=' . urlencode($album_atual['slug']));
    exit;
}

// Atualizar Visibilidade Pública (Apenas Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_visibilidade']) && $isAdmin && $album_atual) {
    $publicosIds = array_map('intval', $_POST['publicos'] ?? []);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE midias SET is_public = 0 WHERE album_id = :album_id");
        $stmt->execute(['album_id' => $album_atual['id']]);

        if (!empty($publicosIds)) {
            $inQuery = implode(',', array_fill(0, count($publicosIds), '?'));
            $stmt = $pdo->prepare("UPDATE midias SET is_public = 1 WHERE album_id = ? AND id IN ({$inQuery})");
            $stmt->execute(array_merge([$album_atual['id']], $publicosIds));
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }

    header('Location: index.php?album=' . urlencode($album_atual['slug']));
    exit;
}

// Exclusão de Arquivo (Apenas Admin)
if (isset($_GET['delete']) && $isAdmin && $album_atual) {
    $mediaId = (int) $_GET['delete'];

    $stmt = $pdo->prepare("SELECT * FROM midias WHERE id = :id AND album_id = :album_id LIMIT 1");
    $stmt->execute(['id' => $mediaId, 'album_id' => $album_atual['id']]);
    $midia = $stmt->fetch();

    if ($midia) {
        if (file_exists($midia['caminho']) && is_file($midia['caminho'])) {
            unlink($midia['caminho']);
        }
        $stmt = $pdo->prepare("DELETE FROM midias WHERE id = :id");
        $stmt->execute(['id' => $mediaId]);
    }

    header('Location: index.php?album=' . urlencode($album_atual['slug']));
    exit;
}

// Regra de Exibição das Mídias
$midias = [];
if ($album_atual && $isLoggedIn) {
    if ($isAdmin) {
        // Admin visualiza TUDO (Públicos e Privados)
        $stmt = $pdo->prepare("SELECT * FROM midias WHERE album_id = :album_id ORDER BY id DESC");
        $stmt->execute(['album_id' => $album_atual['id']]);
    } else {
        // Usuário 'amigos' visualiza APENAS as mídias com is_public = 1
        $stmt = $pdo->prepare("SELECT * FROM midias WHERE album_id = :album_id AND is_public = 1 ORDER BY id DESC");
        $stmt->execute(['album_id' => $album_atual['id']]);
    }
    $midias = $stmt->fetchAll();
}

require_once __DIR__ . '/view.php';
