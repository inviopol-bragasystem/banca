<?php
$f = fopen("C:/xampp/htdocs/games/pragmatic/database e install/apipragmatic_fixed.sql", "r");
$tables = [];
$agents = [];
while (($line = fgets($f)) !== false) {
    if (preg_match('/CREATE TABLE `?([a-zA-Z0-9_]+)`?/', $line, $m)) {
        $tables[] = $m[1];
    }
    if (stripos($line, 'INSERT INTO `agents`') !== false || stripos($line, "INSERT INTO agents") !== false) {
        $agents[] = substr($line, 0, 200);
    }
}
fclose($f);

echo "Tables found: " . implode(', ', $tables) . "\n";
echo "Agents count: " . count($agents) . "\n";
if (!empty($agents)) {
    echo "First agent line: " . $agents[0] . "\n";
}
