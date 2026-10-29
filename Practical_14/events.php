<?php
session_start();

require_once 'auth.php';
require_once 'db.php';
require_once 'menu.php';

require_login($pdo);
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$menu = get_role_menu($pdo, $_SESSION['role']);
$events = $pdo->query(
    'SELECT id, title, event_date, created_at
     FROM events
     ORDER BY event_date DESC, id DESC'
)->fetchAll();
$token = $isAdmin ? csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ($isAdmin): ?>
        <meta name="csrf-token" content="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <title>Events | StudentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="home.php">StudentHub</a>
        <div class="navbar-nav me-auto">
            <?php foreach ($menu as $item): ?>
                <a class="nav-link" href="<?= htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <span class="navbar-text text-white me-3">
            <?= htmlspecialchars($_SESSION['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>
<main class="container py-4">
    <h1 class="h3 mb-3">Events</h1>
    <?php if ($isAdmin): ?>
        <div id="event-message" class="alert d-none" role="status"></div>
        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5" id="form-heading">Create event</h2>
                <form id="event-form" class="row g-3">
                    <input type="hidden" id="event-id">
                    <div class="col-md-7">
                        <label for="event-title" class="form-label">Title</label>
                        <input class="form-control" id="event-title" maxlength="150" required>
                    </div>
                    <div class="col-md-3">
                        <label for="event-date" class="form-label">Event date</label>
                        <input class="form-control" type="date" id="event-date" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary" type="submit" id="save-event">Save</button>
                        <button class="btn btn-outline-secondary d-none" type="button" id="cancel-edit">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>
    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white"><strong>Event list</strong></div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="events-body">
                    <?php if (!$events): ?>
                        <tr><td colspan="<?= $isAdmin ? 4 : 3 ?>" class="text-center py-4">
                            No events found.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= (int) $event['id'] ?></td>
                                <td><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <?php if ($isAdmin): ?>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                                type="button" data-edit
                                                data-id="<?= (int) $event['id'] ?>"
                                                data-title="<?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-date="<?= htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8') ?>">
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                type="button" data-delete
                                                data-id="<?= (int) $event['id'] ?>"
                                                data-title="<?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>">
                                            Delete
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php if ($isAdmin): ?>
<script>
(() => {
    const form = document.getElementById('event-form');
    const body = document.getElementById('events-body');
    const message = document.getElementById('event-message');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const eventId = document.getElementById('event-id');
    const title = document.getElementById('event-title');
    const date = document.getElementById('event-date');
    const heading = document.getElementById('form-heading');
    const cancelEdit = document.getElementById('cancel-edit');

    function showMessage(text, isError = false) {
        message.textContent = text;
        message.className = `alert ${isError ? 'alert-danger' : 'alert-success'}`;
    }

    function resetForm() {
        form.reset();
        eventId.value = '';
        heading.textContent = 'Create event';
        document.getElementById('save-event').textContent = 'Save';
        cancelEdit.classList.add('d-none');
    }

    async function request(action, payload = null) {
        const options = { headers: {} };
        if (payload !== null) {
            options.method = 'POST';
            options.headers['Content-Type'] = 'application/json';
            options.headers['X-CSRF-Token'] = token;
            options.body = JSON.stringify({ action, ...payload });
        }

        const response = await fetch(`api.php?action=${encodeURIComponent(action)}`, options);
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'The request failed.');
        }
        return result;
    }

    function renderEvents(events) {
        body.replaceChildren();
        if (events.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 4;
            cell.className = 'text-center py-4';
            cell.textContent = 'No events found.';
            row.append(cell);
            body.append(row);
            return;
        }

        for (const event of events) {
            const row = document.createElement('tr');
            for (const value of [event.id, event.title, event.event_date]) {
                const cell = document.createElement('td');
                cell.textContent = String(value);
                row.append(cell);
            }

            const actions = document.createElement('td');
            const edit = document.createElement('button');
            edit.type = 'button';
            edit.className = 'btn btn-sm btn-outline-primary me-1';
            edit.textContent = 'Edit';
            edit.addEventListener('click', () => {
                eventId.value = event.id;
                title.value = event.title;
                date.value = event.event_date;
                heading.textContent = 'Update event';
                document.getElementById('save-event').textContent = 'Update';
                cancelEdit.classList.remove('d-none');
                title.focus();
            });

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger';
            remove.textContent = 'Delete';
            remove.addEventListener('click', async () => {
                if (!window.confirm(`Delete "${event.title}"? Events with registrations cannot be deleted.`)) {
                    return;
                }
                try {
                    const result = await request('delete_event', { id: event.id });
                    showMessage(result.message);
                    await loadEvents();
                } catch (error) {
                    showMessage(error.message, true);
                }
            });
            actions.append(edit, remove);
            row.append(actions);
            body.append(row);
        }
    }

    async function loadEvents() {
        const result = await request('events');
        renderEvents(result.events);
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = {
            title: title.value.trim(),
            event_date: date.value
        };
        const action = eventId.value === '' ? 'create_event' : 'update_event';
        if (action === 'update_event') {
            payload.id = Number(eventId.value);
        }
        try {
            const result = await request(action, payload);
            showMessage(result.message);
            resetForm();
            await loadEvents();
        } catch (error) {
            showMessage(error.message, true);
        }
    });

    cancelEdit.addEventListener('click', resetForm);
    loadEvents().catch((error) => showMessage(error.message, true));
})();
</script>
<?php endif; ?>
</body>
</html>
