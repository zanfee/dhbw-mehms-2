<?php
// Define path to php folder for includes
$ROOT = '../../';

require_once '../Database.php';
$db = new Database($ROOT);

$id = $_POST['id'];
$db->database->query("DELETE FROM comments WHERE ID = " . $id);