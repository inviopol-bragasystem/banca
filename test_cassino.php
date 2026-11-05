<?php
/**
 * Teste direto do CassinoModel via web
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('IS_CLI', false);
define('IS_LOCAL', true);

require_once __DIR__ . '/inc.config.php';

try {
    $games = \app\models\CassinoModel::getGames(true, 'Popok');
    echo "Total jogos Popok: " . count($games) . "\n\n";
    
    foreach ($games as $g) {
        echo "{$g['name']} ({$g['game_code']}) - cat: {$g['category']} - provider: {$g['provider']} - active: {$g['active']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}