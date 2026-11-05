<?php
$mysqlBin = 'C:\\xampp\\mysql\\bin\\mysql.exe';
$sqlFile = 'C:\\xampp\\htdocs\\games\\pragmatic\\database e install\\apipragmatic_fixed.sql';

$descriptors = [
    0 => ['file', $sqlFile, 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];

$process = proc_open("\"$mysqlBin\" -u root -pcarol11071107 --default-character-set=utf8mb4 apipragmatic", $descriptors, $pipes);

if (is_resource($process)) {
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    echo "Exit code: $exitCode\n";
    if ($stdout) echo "Stdout: $stdout\n";
    if ($stderr) echo "Stderr: $stderr\n";
} else {
    echo "Failed to start process\n";
}

$pdo = new PDO("mysql:host=127.0.0.1;dbname=apipragmatic;charset=utf8", "root", "carol11071107", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in apipragmatic: " . count($tables) . "\n";
echo implode(', ', $tables) . "\n";
