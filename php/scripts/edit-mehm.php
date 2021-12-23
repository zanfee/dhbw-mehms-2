<?php
// Define path to php folder for includes
$ROOT = '../';

require_once 'Database.php';
$db = new Database($ROOT);
// Entscheidet anhand eingegangener Action, ob es sich um approve oder decline handelt
print_r($_POST);
$toChange = $_POST['changed'];
if ($toChange == 'title') {
    changeTitle($_POST['id'], $_POST['new']);
} else {
    changeDesc($_POST['id'], $_POST['new']);
}

/**
 * Ändert den Titel zu $new des Mehms $id in der Datenbank.
 * @param int $id -> ID des Mehms
 * @param string $new -> Neuer Titel
 * @return void
 */
function changeTitle(int $id, string $new)
{
    global $db;
    $db->database->query("UPDATE mehms SET Title = '" . $new . "' WHERE ID = " . $id);
}

/**
 * Ändert die Beschreibung zu $new des Mehms $id in der Datenbank.
 * @param int $id -> ID des Mehms
 * @param string $new -> Neue Beschreibung
 * @return void
 */
function changeDesc(int $id, string $new)
{
    global $db;
    $db->database->query("UPDATE mehms SET Description = '" . $new . "' WHERE ID = " . $id);
}
