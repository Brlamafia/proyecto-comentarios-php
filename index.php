<?php

declare(strict_types=1);

session_start();
date_default_timezone_set('America/Santo_Domingo');

require_once __DIR__ . '/src/CommentRepository.php';
require_once __DIR__ . '/includes/functions.php';

$repository = new CommentRepository(__DIR__ . '/data/comments.json');
$errors = $_SESSION['errors'] ?? [];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['errors'], $_SESSION['flash']);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $role = trim((string) ($_POST['role'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $honeypot = trim((string) ($_POST['website'] ?? ''));
    $token = (string) ($_POST['csrf_token'] ?? '');

    $_SESSION['form_data'] = compact('name', 'role', 'message');

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['errors'] = ['general' => 'La sesión expiró. Actualiza la página e intenta nuevamente.'];
    } elseif ($honeypot !== '') {
        $_SESSION['errors'] = ['general' => 'No fue posible procesar la solicitud.'];
    } else {
        $validationErrors = validateComment(compact('name', 'role', 'message'));

        if ($validationErrors !== []) {
            $_SESSION['errors'] = $validationErrors;
        } else {
            try {
                $repository->add(compact('name', 'role', 'message'));
                $_SESSION['flash'] = 'Tu comentario fue publicado correctamente.';
                unset($_SESSION['form_data']);
            } catch (Throwable) {
                $_SESSION['errors'] = ['general' => 'No pudimos guardar el comentario. Inténtalo nuevamente.'];
            }
        }
    }

    header('Location: ./#comentarios');
    exit;
}

try {
    $comments = $repository->all();
} catch (Throwable) {
    $comments = [];
    $errors['general'] = 'Los comentarios no están disponibles temporalmente.';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Voces, un espacio para compartir comentarios y construir comunidad.">
    <title>Voces | Portal de comentarios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
<header class="site-header sticky-top">
    <nav class="navbar navbar-expand py-3" aria-label="Navegación principal">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-3 m-0" href="./" aria-label="Inicio de Voces">
                <span class="brand-mark"><i class="bi bi-chat-heart-fill" aria-hidden="true"></i></span>
                <span>
                    <strong class="brand-name d-block">Voces</strong>
                    <small class="text-secondary">Comunidad abierta</small>
                </span>
            </a>
            <a class="status-pill text-decoration-none" href="#participa">
                <i class="bi bi-circle-fill me-1" aria-hidden="true" style="font-size:.45rem"></i>
                Participa
            </a>
        </div>
    </nav>
</header>

<main>
    <section class="hero">
        <div class="container position-relative" style="z-index:1">
            <span class="eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Un espacio para escucharnos</span>
            <h1 class="display-title">Cada opinión puede abrir una conversación.</h1>
            <p class="hero-copy">Comparte tus ideas con respeto, descubre otros puntos de vista y participa en una comunidad construida entre todos.</p>
            <div class="hero-stat">
                <i class="bi bi-chat-square-quote fs-4" aria-hidden="true"></i>
                <span><strong><?= count($comments) ?></strong> <?= count($comments) === 1 ? 'comentario publicado' : 'comentarios publicados' ?></span>
            </div>
        </div>
    </section>

    <section class="main-content" id="participa">
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="status">
                    <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i><?= escape($flash) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger mb-4" role="alert"><?= escape($errors['general']) ?></div>
            <?php endif; ?>

            <div class="row g-4 align-items-start">
                <div class="col-lg-4">
                    <div class="panel form-panel">
                        <span class="panel-kicker">Tu voz cuenta</span>
                        <h2 class="panel-title h3">Deja un comentario</h2>
                        <p class="panel-copy mb-4">Escribe con claridad y respeto. Todos los campos son obligatorios.</p>

                        <form id="commentForm" method="post" action="./" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= escape($_SESSION['csrf_token']) ?>">
                            <div class="position-absolute opacity-0" style="left:-9999px" aria-hidden="true">
                                <label for="website">Sitio web</label>
                                <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="name">Nombre</label>
                                <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name') ?>" minlength="2" maxlength="60" placeholder="Ej. Bryant Romano" required>
                                <div class="invalid-feedback"><?= escape($errors['name'] ?? 'Escribe tu nombre.') ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="role">Participación</label>
                                <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" id="role" name="role" required>
                                    <option value="" disabled <?= old('role') === '' ? 'selected' : '' ?>>Selecciona una opción</option>
                                    <?php foreach (['Estudiante', 'Docente', 'Visitante'] as $role): ?>
                                        <option value="<?= $role ?>" <?= old('role') === $role ? 'selected' : '' ?>><?= $role ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"><?= escape($errors['role'] ?? 'Selecciona una opción.') ?></div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex justify-content-between gap-2">
                                    <label class="form-label" for="message">Comentario</label>
                                    <small class="text-secondary" id="characterCount">0 / 500</small>
                                </div>
                                <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" id="message" name="message" minlength="10" maxlength="500" placeholder="Comparte tu opinión..." required><?= old('message') ?></textarea>
                                <div class="invalid-feedback"><?= escape($errors['message'] ?? 'Escribe al menos 10 caracteres.') ?></div>
                            </div>

                            <p class="small text-secondary mb-3"><i class="bi bi-shield-check me-1" aria-hidden="true"></i>Tu comentario se almacena de forma segura en el servidor.</p>
                            <button class="btn btn-brand w-100" type="submit"><i class="bi bi-send-fill me-2" aria-hidden="true"></i>Publicar comentario</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8" id="comentarios">
                    <div class="panel comments-panel">
                        <div class="comments-toolbar d-flex justify-content-between align-items-center gap-3 mb-4">
                            <div>
                                <span class="panel-kicker">Conversaciones</span>
                                <h2 class="panel-title h3 mb-0">Comentarios recientes</h2>
                            </div>
                            <div class="search-box">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <input class="form-control" id="commentSearch" type="search" placeholder="Buscar comentarios" aria-label="Buscar comentarios">
                            </div>
                        </div>

                        <div class="d-grid gap-3" aria-live="polite">
                            <?php foreach ($comments as $comment): ?>
                                <article class="comment-card" data-comment>
                                    <div class="d-flex gap-3 align-items-start">
                                        <div class="avatar" aria-hidden="true"><?= escape(initials($comment['name'])) ?></div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <h3 class="h6 fw-bold mb-0"><?= escape($comment['name']) ?></h3>
                                                    <span class="role-badge"><?= escape($comment['role']) ?></span>
                                                </div>
                                                <time class="comment-time" datetime="<?= escape($comment['created_at']) ?>"><?= escape(formatDate($comment['created_at'])) ?></time>
                                            </div>
                                            <p class="comment-message"><?= escape($comment['message']) ?></p>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>

                            <?php if ($comments === []): ?>
                                <div class="empty-state">
                                    <span class="empty-icon"><i class="bi bi-chat-heart" aria-hidden="true"></i></span>
                                    <h3 class="h5">Inicia la conversación</h3>
                                    <p class="mb-0">Sé la primera persona en compartir un comentario.</p>
                                </div>
                            <?php endif; ?>

                            <div class="empty-state" id="emptySearch" hidden>
                                <span class="empty-icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                                <h3 class="h5">Sin coincidencias</h3>
                                <p class="mb-0">Prueba con otra palabra o nombre.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer py-4">
    <div class="container d-flex flex-column flex-sm-row justify-content-between gap-2 text-center text-sm-start">
        <span>© 2026 Voces · Proyecto grupal</span>
        <span>HTML · CSS · JavaScript · PHP</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>

