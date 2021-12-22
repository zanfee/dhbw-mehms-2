<?php

class Database
{
    public PDO $database;

    // Konstruktor der Klasse Database
    // Startet Verbindungsaufbau bei Instanziierung
    function __construct()
    {
        $this->connectToDatabase();
    }

    // connectToDatabase verbindet die hier spezifizierte Datenbank und speichert sie als PDO
    // Ist die Datenbank noch nicht initialisiert, wird setupDatabase() aufgerufen
    public function connectToDatabase()
    {
        $mysql_host = "localhost";
        $mysql_database = "main";
        $mysql_user = "root";
        $mysql_password = "";
        // MySQL mit PDO_MYSQL
        try {
            $this->database = new PDO("mysql:host=$mysql_host;dbname=$mysql_database", $mysql_user, $mysql_password);
        } catch (PDOException $exception) {
            // Datenbank ist noch nicht initialisiert worden.
            $this->setupDatabase(new PDO("mysql:host=$mysql_host;dbname=", $mysql_user, $mysql_password));
            $this->database = new PDO("mysql:host=$mysql_host;dbname=$mysql_database", $mysql_user, $mysql_password);

        }
    }

    // Datenbanksetup und Ausführen der init.sql in der $emptyDB
    // Parameter:
    // $emptyDB (database) -> Datenbank, die Setup benötigt
    private function setupDatabase($emptyDB)
    {

        $query = file_get_contents("scripts/init.sql");

        $stmt = $emptyDB->prepare($query);

        if ($stmt->execute()) {
            echo "<script>console.log('Database successfully created!');</script>";
        } else {
            echo "<script>console.log('Failed to create database!');</script>";
        }
    }

    // getMehms holt ein Array aus Mehms aus der Datenbank, sortiert nach den Parametern $sort and $desc sowie
    // gefiltert nach $filter und $category.
    // Ein Admin kann alle Mehms oder nur solche, die (noch) nicht approved sind, sehen
    // Parameter:
    // $filter (Array) -> ein Array der Struktur ['user' => (string), 'search' => (string)], notwendig wegen der Suchleiste
    // $category (string) -> die gewünschte Mehm-Kategorie ("Programmieren", "DHBW", "Andere")
    // $sort (string) -> der Parameter, nach dem sortiert werden soll ("date", "likes", "comments", "notVisibleOnly")
    // $desc (boolean) -> Reihenfolge: descending (true), oder ascending (false)
    // $admin (boolean) -> Adminansicht (true) oder normale Useransicht (false)
    // Rückgabewert:
    // (Array) -> alle Mehms, die von der Query erfasst wurden
    public function getMehms($filter, $category, $sort, $desc, $admin): array
    {
        $query = 'SELECT *, mehms.UserID as UserID, mehms.ID as ID, mehms.Type as Type FROM mehms';

        $hasConcatenatedFilter = false;

        switch ($sort) {
            case 'comments':
                $query .= ' LEFT JOIN comments c ON mehms.ID = c.MehmID';
                break;
            case 'likes':
                $query .= ' LEFT JOIN likes l ON mehms.ID = l.MehmID';
                break;
        }

        if ($filter['user'] != '' || $filter['search'] != '') {
            $query .= ' LEFT JOIN Users u ON mehms.UserID = u.ID';
        }

        if (!$admin) {
            if ($sort == 'notVisibleOnly') {
                $query .= ' WHERE Visible = FALSE';
            } else {
                $query .= ' WHERE Visible = TRUE';
            }
            $hasConcatenatedFilter = true;
        } else if ($sort == 'notVisibleOnly') {
            $query .= ' WHERE Visible = FALSE';
            $hasConcatenatedFilter = true;
        }

        if ($filter['user'] != '' || $filter['search'] != '') {
            if ($hasConcatenatedFilter) {
                $query .= ' AND';
            } else {
                $query .= ' WHERE';
            }
            $user = $filter['user'];
            $search = $filter['search'];
            $query .= " Title LIKE '%$search%' AND Name LIKE '%$user%'";
        }

        if ($category != '') {
            $appendix = '';
            if ($hasConcatenatedFilter) {
                $appendix .= ' AND';
            } else {
                $appendix .= ' WHERE';
            }
            switch ($category) {
                case "Programmieren":
                    $appendix .= " Type = 'Programmieren'";
                    $query .= $appendix;
                    break;
                case "DHBW":
                    $appendix .= " Type = 'DHBW'";
                    $query .= $appendix;
                    break;
                case "Andere":
                    $appendix .= " Type = 'Andere'";
                    $query .= $appendix;
                    break;
                default:
            }
        }

        switch ($sort) {
            case 'date':
                $query .= ' ORDER BY VisibleOn';
                break;
            case 'likes':
                $query .= ' GROUP BY mehms.ID ORDER BY count(l.MehmID)';
                break;
            case 'comments':
                $query .= ' GROUP BY mehms.ID ORDER BY count(c.MehmID)';
                break;
            default:
                return $this->database->query($query)->fetchAll();
        }

        if ($desc) {
            $query .= ' DESC';
        }
        return $this->database->query($query)->fetchAll();
    }

    public function getUser(int $id): array
    {
        if ($id == 0) {
            return $this->database->query("SELECT * FROM users")->fetchAll();
        }
        {
            return $this->database->query("SELECT * FROM users WHERE ID = '$id'")->fetchAll();
        }
    }

    public function updateUser(int $id, string $name, string $password, string $type) {
        $this->database->query("UPDATE users SET Name = '$name', Password = '$password', Type = '$type' WHERE ID = '$id'");
    }

}