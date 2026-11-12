<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION['feedback_visitor'] ??= bin2hex(random_bytes(32));
$_SESSION['feedback_csrf'] ??= bin2hex(random_bytes(32));

function feedback_db(): PDO
{
    static $db = null;
    if ($db instanceof PDO) {
        return $db;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $name = getenv('DB_NAME') ?: 'portfolio_feedback';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    $db = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $db;
}

function feedback_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function feedback_projects(): array
{
    return ['enrollment' => 'Student Enrollment Portal'];}

function feedback_namespace(): string
{
    return 'dencil-montales';
}

function feedback_redirect(string $project): never
{
    header('Location: index.php#feedback-' . rawurlencode($project), true, 303);
    exit;
}

function feedback_handle_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $project = (string) ($_POST['project'] ?? '');
    $projects = feedback_projects();
    if (!isset($projects[$project])) {
        http_response_code(400);
        exit('Unknown project.');
    }
    $storageProject = feedback_namespace() . ':' . $project;
    $csrf = (string) ($_POST['csrf'] ?? '');
    if (!hash_equals((string) $_SESSION['feedback_csrf'], $csrf)) {
        http_response_code(403);
        exit('Your session expired. Reload the page and try again.');
    }

    try {
        $db = feedback_db();
        $visitor = (string) $_SESSION['feedback_visitor'];
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'rate') {
            $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
            if ($rating === false || $rating < 1 || $rating > 5) {
                throw new InvalidArgumentException('Choose a rating from 1 to 5.');
            }
            $query = $db->prepare('INSERT INTO portfolio_ratings (project_key, visitor_key, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), updated_at = CURRENT_TIMESTAMP');
            $query->execute([$storageProject, $visitor, $rating]);
            $_SESSION['feedback_message'] = 'Your rating was saved.';
        } elseif ($action === 'clear_rating') {
            $query = $db->prepare('DELETE FROM portfolio_ratings WHERE project_key = ? AND visitor_key = ?');
            $query->execute([$storageProject, $visitor]);
            $_SESSION['feedback_message'] = 'Your rating was removed.';
        } elseif ($action === 'add_comment') {
            $comment = trim((string) ($_POST['comment'] ?? ''));
            if ($comment === '' || mb_strlen($comment) > 300) {
                throw new InvalidArgumentException('Write a comment of 1 to 300 characters.');
            }
            $query = $db->prepare('INSERT INTO portfolio_comments (project_key, visitor_key, comment_text) VALUES (?, ?, ?)');
            $query->execute([$storageProject, $visitor, $comment]);
            $_SESSION['feedback_message'] = 'Your comment was added.';
        } elseif ($action === 'edit_comment' || $action === 'delete_comment') {
            $commentId = filter_var($_POST['comment_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$commentId) {
                throw new InvalidArgumentException('That comment could not be found.');
            }
            if ($action === 'edit_comment') {
                $comment = trim((string) ($_POST['comment'] ?? ''));
                if ($comment === '' || mb_strlen($comment) > 300) {
                    throw new InvalidArgumentException('Write a comment of 1 to 300 characters.');
                }
                $query = $db->prepare('UPDATE portfolio_comments SET comment_text = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND project_key = ? AND visitor_key = ?');
                $query->execute([$comment, $commentId, $storageProject, $visitor]);
                $_SESSION['feedback_message'] = $query->rowCount() ? 'Your comment was updated.' : 'No changes were made.';
            } else {
                $query = $db->prepare('DELETE FROM portfolio_comments WHERE id = ? AND project_key = ? AND visitor_key = ?');
                $query->execute([$commentId, $storageProject, $visitor]);
                $_SESSION['feedback_message'] = $query->rowCount() ? 'Your comment was deleted.' : 'That comment could not be found.';
            }
        } else {
            throw new InvalidArgumentException('Unknown feedback action.');
        }
    } catch (Throwable $error) {
        $_SESSION['feedback_message'] = $error instanceof InvalidArgumentException
            ? $error->getMessage()
            : 'Feedback could not be saved. Check the MySQL connection and try again.';
    }

    feedback_redirect($project);
}

function render_feedback(string $project, string $owner): void
{
    $storageProject = feedback_namespace() . ':' . $project;
    $message = (string) ($_SESSION['feedback_message'] ?? '');
    unset($_SESSION['feedback_message']);
    $rating = null;
    $comments = [];
    $databaseError = false;
    try {
        $db = feedback_db();
        $query = $db->prepare('SELECT rating FROM portfolio_ratings WHERE project_key = ? AND visitor_key = ?');
        $query->execute([$storageProject, $_SESSION['feedback_visitor']]);
        $rating = $query->fetchColumn();
        $query = $db->prepare('SELECT id, visitor_key, comment_text, created_at, updated_at FROM portfolio_comments WHERE project_key = ? ORDER BY created_at DESC, id DESC LIMIT 100');
        $query->execute([$storageProject]);
        $comments = $query->fetchAll();
    } catch (Throwable) {
        $databaseError = true;
    }
    $csrf = feedback_escape((string) $_SESSION['feedback_csrf']);
    ?>
    <div class="feedback" id="feedback-<?= feedback_escape($project) ?>">
      <h4>Rate this project</h4>
      <p class="feedback-note">Choose a rating and leave a comment.</p>
      <?php if ($message !== ''): ?><p class="feedback-message" role="status"><?= feedback_escape($message) ?></p><?php endif; ?>
      <?php if ($databaseError): ?>
        <p class="feedback-message" role="status">Feedback database is not connected yet. Follow the setup instructions in README.md.</p>
      <?php else: ?>
        <form class="server-form" method="post" action="index.php#feedback-<?= feedback_escape($project) ?>">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          <input type="hidden" name="project" value="<?= feedback_escape($project) ?>">
          <input type="hidden" name="action" value="rate">
          <fieldset class="rating-options" aria-label="Your rating">
            <?php for ($score = 1; $score <= 5; $score++): ?>
              <label><input type="radio" name="rating" value="<?= $score ?>" required <?= (int) $rating === $score ? 'checked' : '' ?>><span aria-hidden="true">★</span><span class="sr-only"><?= $score ?> out of 5 stars</span></label>
            <?php endfor; ?>
            <button class="save-comment" type="submit">Save rating</button>
          </fieldset>
        </form>
        <?php if ($rating !== false && $rating !== null): ?>
          <form class="inline-action" method="post" action="index.php#feedback-<?= feedback_escape($project) ?>">
            <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="project" value="<?= feedback_escape($project) ?>"><input type="hidden" name="action" value="clear_rating">
            <span class="rating-status">Your rating: <?= (int) $rating ?> out of 5</span><button class="comment-action" type="submit">Remove rating</button>
          </form>
        <?php else: ?><p class="rating-status">No rating saved yet.</p><?php endif; ?>
        <form class="comment-form" method="post" action="index.php#feedback-<?= feedback_escape($project) ?>">
          <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="project" value="<?= feedback_escape($project) ?>"><input type="hidden" name="action" value="add_comment">
          <label for="comment-<?= feedback_escape($project) ?>">Your comment</label>
          <textarea id="comment-<?= feedback_escape($project) ?>" name="comment" maxlength="300" placeholder="Share your thoughts" required></textarea>
          <div class="feedback-actions"><button class="save-comment" type="submit">Add comment</button></div>
        </form>
        <ul class="comment-list" aria-label="Comments">
          <?php foreach ($comments as $item): ?>
            <li class="comment-item">
              <div><?= nl2br(feedback_escape($item['comment_text'])) ?></div>
              <time class="comment-date" datetime="<?= feedback_escape((string) $item['created_at']) ?>"><?= feedback_escape(date('M j, Y', strtotime((string) $item['created_at']))) ?><?= $item['updated_at'] !== $item['created_at'] ? ' · edited' : '' ?></time>
              <?php if (hash_equals((string) $_SESSION['feedback_visitor'], (string) $item['visitor_key'])): ?>
                <details class="comment-edit"><summary>Edit your comment</summary>
                  <form class="comment-form" method="post" action="index.php#feedback-<?= feedback_escape($project) ?>">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="project" value="<?= feedback_escape($project) ?>"><input type="hidden" name="action" value="edit_comment"><input type="hidden" name="comment_id" value="<?= (int) $item['id'] ?>">
                    <textarea name="comment" maxlength="300" required><?= feedback_escape($item['comment_text']) ?></textarea><button class="comment-action" type="submit">Save edit</button>
                  </form>
                </details>
                <form class="inline-action" method="post" action="index.php#feedback-<?= feedback_escape($project) ?>">
                  <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="project" value="<?= feedback_escape($project) ?>"><input type="hidden" name="action" value="delete_comment"><input type="hidden" name="comment_id" value="<?= (int) $item['id'] ?>"><button class="comment-action" type="submit">Delete</button>
                </form>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="feedback-footnote">Feedback is stored in the shared MySQL database. Visitors can edit or delete comments they posted in their current browser session.</p>
      <?php endif; ?>
    </div>
    <?php
}

feedback_handle_post();




