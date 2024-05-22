<?php
define('MYSQL_HOST', 'mysql');
define('MYSQL_USER', $_ENV['MYSQL_USER']);
define('MYSQL_PASSWORD', $_ENV['MYSQL_PASSWORD']);
define('MYSQL_DB', $_ENV['MYSQL_DATABASE']);

$conn = new PDO('mysql:host='.MYSQL_HOST.';port=3306;dbname='.MYSQL_DB, MYSQL_USER, MYSQL_PASSWORD);

$hotel_id = (int)($_GET['hotel_id'] ?? 1); // отель для которого делаем проверку

$hotel_query = $conn->prepare('SELECT * FROM `hotels` WHERE id = :hotel_id');

if (!$hotel_id) throw new Exception('hotel_id is not defined');

$hotel_query->execute(['hotel_id' => $hotel_id]);

$hotel = $hotel_query->fetch();

if (!$hotel) die('hotel_id is not defined');

echo '<ul>';
echo "$hotel[name] ($hotel[stars] звезд)<br>";
foreach ($conn->query('SELECT * FROM `agencies`') as $agency) {
    echo "<li><strong> $agency[id] </strong>$agency[name]</li>";
    echo '<ul>';
    foreach ($conn->query('SELECT * FROM `agency_rules` WHERE agency_id = ' . $agency['id']) as $row) {
        if (!$row['is_active']) continue;
        $init_query = "SELECT * FROM `hotels` left join agency_hotel_options on agency_hotel_options.agency_id = $agency[id] and agency_hotel_options.hotel_id = $hotel_id WHERE hotels.id = $hotel_id ";
        $rules = json_decode($row['rules'], true) ?? [];
        foreach ($rules as $rule) {
            $init_query .= "AND $rule[condition] $rule[op] $rule[value] ";
        }
        // var_dump($init_query); echo "<br>";
        $result = (bool)$conn->query($init_query)->rowCount();
        if ($result) {
            echo $row['text'] . "<br>";
        }
    }
    echo '</ul>';
}
echo '</ul>';
?>