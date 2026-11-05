<?php
$f = fopen("C:/xampp/htdocs/games/pragmatic/database e install/apipragmatic_fixed.sql", "r");
$capturing = false;
$createGames = "";
$gameLines = [];

while (($line = fgets($f)) !== false) {
    if (strpos($line, "CREATE TABLE `games`") !== false) {
        $capturing = true;
    }
    if ($capturing) {
        $createGames .= $line;
        if (strpos($line, ";") !== false && strpos($line, "ENGINE=") !== false) {
            $capturing = false;
        }
    }
    if (strpos($line, "INSERT INTO `games`") !== false) {
        $gameLines[] = substr($line, 0, 500);
    }
}
fclose($f);

echo "CREATE TABLE games:\n" . $createGames . "\n";
echo "INSERT INTO games total lines: " . count($gameLines) . "\n";
if (!empty($gameLines)) {
    echo "First game line: " . $gameLines[0] . "\n";
}
