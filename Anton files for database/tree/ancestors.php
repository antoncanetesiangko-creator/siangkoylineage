<?php

require_once "../config/database.php";

$id = intval($_GET["id"] ?? 0);

if (!$id) {
    die("Person ID required.");
}

$stmt = $pdo->prepare("
    WITH RECURSIVE ancestors AS (

        SELECT
            p.id,
            p.first_name,
            p.middle_name,
            p.last_name,
            p.sex,
            0 AS generation

        FROM people p

        WHERE p.id = ?

        UNION ALL

        SELECT
            parent.id,
            parent.first_name,
            parent.middle_name,
            parent.last_name,
            parent.sex,
            ancestors.generation + 1

        FROM ancestors

        INNER JOIN parent_child_relationships pc
            ON pc.child_id = ancestors.id

        INNER JOIN people parent
            ON parent.id = pc.parent_id

    )

    SELECT *
    FROM ancestors

    ORDER BY generation
");

$stmt->execute([$id]);

$ancestors = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html>

<head>

<title>Ancestors</title>

<link rel="stylesheet"
      href="../assets/css/style.css">

</head>

<body>

<div class="container">

<h1>Ancestors</h1>

<?php foreach ($ancestors as $person): ?>

<?php

$indent = $person["generation"] * 40;

$name = trim(
    $person["first_name"] . " " .
    ($person["middle_name"] ?? "") . " " .
    ($person["last_name"] ?? "")
);

?>

<div
    class="tree-person"
    style="margin-left: <?= $indent ?>px;"
>

<a href="../people/view.php?id=<?= $person["id"] ?>">

<?= htmlspecialchars($name) ?>

</a>

<span>
Generation <?= $person["generation"] ?>
</span>

</div>

<?php endforeach; ?>

</div>

</body>

</html>
