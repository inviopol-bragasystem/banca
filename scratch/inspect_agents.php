<?php
$f = fopen("C:/xampp/htdocs/games/pragmatic/database e install/apipragmatic_fixed.sql", "r");
$capturing = false;
$createAgents = "";
$insertAgents = "";

while (($line = fgets($f)) !== false) {
    if (strpos($line, "CREATE TABLE `agents`") !== false) {
        $capturing = true;
    }
    if ($capturing) {
        $createAgents .= $line;
        if (strpos($line, ";") !== false && strpos($line, "ENGINE=") !== false) {
            $capturing = false;
        }
    }
    if (strpos($line, "INSERT INTO `agents`") !== false) {
        $insertAgents .= $line;
    }
}
fclose($f);

echo "CREATE TABLE agents:\n" . $createAgents . "\n\n";
echo "INSERT INTO agents:\n" . substr($insertAgents, 0, 1000) . "\n";
