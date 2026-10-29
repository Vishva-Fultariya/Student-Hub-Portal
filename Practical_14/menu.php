
<?php
require_once 'db.php';

function get_role_menu(PDO $pdo, string $role): array
{
    $allowedRoles = [
        'user' => ['user'],
        'editor' => ['user', 'editor'],
        'admin' => ['user', 'editor', 'admin']
    ];

    if (!isset($allowedRoles[$role])) {
        return [];
    }

    $roles = $allowedRoles[$role];
    $placeholders = implode(',', array_fill(0, count($roles), '?'));

    $stmt = $pdo->prepare(
        "SELECT title, link
         FROM menu_items
         WHERE min_role IN ($placeholders)
         ORDER BY id"
    );

    $stmt->execute($roles);

    $items = $stmt->fetchAll();

    return array_values(array_filter(
        $items,
        static function (array $item): bool {
            $link = $item['link'] ?? '';

            return is_string($link)
                && basename($link) === $link
                && is_file(__DIR__ . DIRECTORY_SEPARATOR . $link);
        }
    ));
}
