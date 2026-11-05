<?php

namespace app\models;

use app\core\crud\Conn;
use PDO;

class CassinoModel
{
    /**
     * Retorna as configurações da API de Cassino
     */
    public static function getSettings(): array
    {
        $pdo = Conn::getConn();
        $stmt = $pdo->query("SELECT * FROM casino_settings LIMIT 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return [
                'id' => 1,
                'provider_key' => 'PGV4',
                'provider_name' => 'PG Soft (PGV4)',
                'api_url' => 'https://pg.0um.online',
                'agent_token' => 'af0483b0-4bfb-4191-b920-37804a38f53d',
                'secret_key' => 'ffba5566-f78d-4729-9f11-c03390fc5a48',
                'rtp_default' => 96.50,
                'rtp_influencer' => 99.00,
                'active' => 1
            ];
        }

        return $data;
    }

    /**
     * Atualiza as configurações de API do Cassino
     */
    public static function saveSettings(array $dados): bool
    {
        $pdo = Conn::getConn();
        $stmt = $pdo->prepare("UPDATE casino_settings SET 
            api_url = :api_url,
            agent_token = :agent_token,
            secret_key = :secret_key,
            rtp_default = :rtp_default,
            rtp_influencer = :rtp_influencer,
            active = :active
            WHERE id = :id");

        return $stmt->execute([
            'id' => (int)($dados['id'] ?? 1),
            'api_url' => trim($dados['api_url'] ?? 'https://pg.0um.online'),
            'agent_token' => trim($dados['agent_token'] ?? ''),
            'secret_key' => trim($dados['secret_key'] ?? ''),
            'rtp_default' => (float)($dados['rtp_default'] ?? 96.50),
            'rtp_influencer' => (float)($dados['rtp_influencer'] ?? 99.00),
            'active' => isset($dados['active']) ? (int)$dados['active'] : 1
        ]);
    }

    /**
     * Retorna a lista de jogos do catálogo
     */
    public static function getGames(bool $onlyActive = true, ?string $category = null, ?string $search = null): array
    {
        $pdo = Conn::getConn();
        $sql = "SELECT * FROM casino_games WHERE 1=1";
        $params = [];

        if ($onlyActive) {
            $sql .= " AND active = 1";
        }

        // Se nenhuma categoria foi passada, usa 'Destaque' como padrão
        $category = $category ?? 'Destaque';
        
        if ($category && $category !== 'all' && $category !== 'Todos') {
            if ($category === 'Destaque' || $category === 'featured' || $category === 'Destaques') {
                // Destaque: 33 jogos aleatórios (shuffled)
                $sql .= " AND active = 1 ORDER BY RAND() LIMIT 33";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else if ($category === 'PGSOFT') {
                $sql .= " AND provider = 'PGSOFT'";
            } else if ($category === 'POPOK') {
                $sql .= " AND provider = 'POPOK'";
            } else if ($category === 'WG') {
                $sql .= " AND provider = 'WG'";
            } else if ($category === 'PRAGMATIC' || $category === 'PRAGMATICPLAY' || $category === 'Pragmatic') {
                $sql .= " AND (provider = 'PRAGMATIC' OR provider = 'PRAGMATICPLAY')";
            } else if ($category === 'Fortune') {
                $sql .= " AND (category = 'Fortune' OR game_code LIKE 'fortune-%')";
            } else {
                $sql .= " AND category = :category";
                $params['category'] = $category;
            }
        }

        if ($search) {
            $sql .= " AND (name LIKE :search OR game_code LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $sql .= " ORDER BY featured DESC, ordem ASC, name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um jogo pelo seu ID ou Código
     */
    public static function getGameByCode(string $code): ?array
    {
        $pdo = Conn::getConn();
        $stmt = $pdo->prepare("SELECT * FROM casino_games WHERE game_code = :code OR game_id = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Alterna o status ativo/inativo de um jogo
     */
    public static function toggleGame(int $id, int $status): bool
    {
        $pdo = Conn::getConn();
        $stmt = $pdo->prepare("UPDATE casino_games SET active = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * Alterna o destaque de um jogo
     */
    public static function toggleFeatured(int $id, int $featured): bool
    {
        $pdo = Conn::getConn();
        $stmt = $pdo->prepare("UPDATE casino_games SET featured = :featured WHERE id = :id");
        return $stmt->execute(['featured' => $featured, 'id' => $id]);
    }

    /**
     * Atualiza o RTP específico de um jogo
     */
    public static function updateGameRtp(int $id, float $rtp): bool
    {
        $pdo = Conn::getConn();
        $stmt = $pdo->prepare("UPDATE casino_games SET rtp = :rtp WHERE id = :id");
        return $stmt->execute(['rtp' => $rtp, 'id' => $id]);
    }

    /**
     * Incrementa visualizações do jogo
     */
    public static function incrementViews(int $id): void
    {
        $pdo = Conn::getConn();
        $stmt = $pdo->prepare("UPDATE casino_games SET views_count = views_count + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    /**
     * Estatísticas gerais do Cassino para o painel de RTP e Lucro
     */
    public static function getStats(): array
    {
        $pdo = Conn::getConn();

        $stats = [
            'total_apostado' => 0.00,
            'total_pago' => 0.00,
            'ggr_lucro' => 0.00,
            'rtp_real' => 0.00,
            'total_rodadas' => 0,
            'total_jogadores' => 0,
            'total_jogos' => 0,
            'jogos_ativos' => 0,
        ];

        // Total de jogos
        $stats['total_jogos'] = (int)$pdo->query("SELECT count(*) FROM casino_games")->fetchColumn();
        $stats['jogos_ativos'] = (int)$pdo->query("SELECT count(*) FROM casino_games WHERE active = 1")->fetchColumn();

        // Movimentação financeira
        $stmtFin = $pdo->query("SELECT 
            COALESCE(SUM(CASE WHEN action = 'bet' THEN amount ELSE 0 END), 0) as total_apostado,
            COALESCE(SUM(CASE WHEN action = 'win' THEN amount ELSE 0 END), 0) as total_pago,
            COUNT(*) as total_rodadas,
            COUNT(DISTINCT user_id) as total_jogadores
            FROM casino_history");

        if ($fin = $stmtFin->fetch(PDO::FETCH_ASSOC)) {
            $stats['total_apostado'] = (float)$fin['total_apostado'];
            $stats['total_pago'] = (float)$fin['total_pago'];
            $stats['ggr_lucro'] = $stats['total_apostado'] - $stats['total_pago'];
            $stats['total_rodadas'] = (int)$fin['total_rodadas'];
            $stats['total_jogadores'] = (int)$fin['total_jogadores'];
            if ($stats['total_apostado'] > 0) {
                $stats['rtp_real'] = round(($stats['total_pago'] / $stats['total_apostado']) * 100, 2);
            }
        }

        return $stats;
    }

    /**
     * Histórico de rodadas/apostas com dados do usuário
     */
    public static function getHistory(?int $userId = null, int $page = 1, int $limit = 50): array
    {
        $pdo = Conn::getConn();
        $offset = ($page - 1) * $limit;

        $sql = "SELECT h.*, u.nome as user_nome, u.login as user_login, g.name as game_titulo, g.image as game_image
            FROM casino_history h
            LEFT JOIN sis_users u ON u.id = h.user_id
            LEFT JOIN casino_games g ON g.game_code = h.game_code
            WHERE 1=1";

        $params = [];
        if ($userId) {
            $sql .= " AND h.user_id = :uid";
            $params['uid'] = $userId;
        }

        $sql .= " ORDER BY h.id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lança uma sessão de jogo para um usuário autenticado ou em modo demonstração
     */
    public static function launchGame($user, array $game): array
    {
        $settings = self::getSettings();
        if (!$settings['active']) {
            return [
                'ok' => false,
                'message' => 'O cassino está temporariamente em manutenção.'
            ];
        }

        self::incrementViews((int)$game['id']);

        // Verifica se é jogo do Popok
        if (($game['provider'] ?? '') === 'POPOK') {
            return self::launchPopokGame($user, $game);
        }

        // Verifica se é jogo do WG
        if (($game['provider'] ?? '') === 'WG') {
            return \app\models\WGModel::launchGame($user, $game);
        }

        // Verifica se é jogo da Pragmatic Play
        if (($game['provider'] ?? '') === 'PRAGMATIC' || ($game['provider'] ?? '') === 'PRAGMATICPLAY') {
            return \app\models\PragmaticModel::launchGame($user, $game);
        }

        // Lançamento PGSoft (padrão existente)
        $apiUrl = rtrim($settings['api_url'] ?? '', '/');
        $agentToken = $settings['agent_token'] ?? '';
        $secretKey = $settings['secret_key'] ?? '';
        $userCode = $user ? (string)$user->getId() : ('demo_' . (session_id() ? substr(session_id(), 0, 8) : bin2hex(random_bytes(4))));
        $userBalance = $user ? (float)$user->getCredito() : 1000.00;

        $payload = [
            'agentToken' => $agentToken,
            'secretKey' => $secretKey,
            'user_code' => $userCode,
            'game_type' => 'slot',
            'provider_code' => 'PGSOFT',
            'game_code' => $game['game_code'],
            'user_balance' => round($userBalance, 2),
        ];

        // Lista de endpoints para tentar:
        // 1. Porta 3016 local caso o usuário tenha iniciado o servidor Node localmente
        // 2. URL configurada no admin ($apiUrl)
        $targets = [];
        $targets[] = 'http://127.0.0.1:3016/api/v1/game_launch';
        if (!empty($apiUrl) && $apiUrl !== 'http://127.0.0.1:3016' && $apiUrl !== 'http://localhost:3016') {
            $targets[] = $apiUrl . '/api/v1/game_launch';
        }

        if (function_exists('curl_init') && !empty($agentToken) && !empty($secretKey)) {
            foreach ($targets as $url) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                $body = curl_exec($ch);
                $httpStatus = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($body && $httpStatus >= 200 && $httpStatus < 300) {
                    $res = json_decode($body, true);
                    if (is_array($res) && isset($res['launch_url']) && !empty($res['launch_url'])) {
                        return [
                            'ok' => true,
                            'url' => $res['launch_url'],
                            'mode' => 'api'
                        ];
                    }
                }
            }
        }

        // Lançamento Local Autônomo (direto dos arquivos em /games/pgv4/)
        // Garante que o jogo abra 100% das vezes mesmo se a API externa estiver fora do ar
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = rtrim($protocol . $host, '/');

        $gameId = $game['game_id'] ?? '126';
        $token = bin2hex(random_bytes(16));
        $localPath = "{$baseUrl}/games/pgv4/src/public/{$gameId}/index.html";

        // Parâmetros oficiais PG Soft compatíveis com o cliente
        $launchUrl = "{$localPath}?operator_token=Zm9saWFiZXQ=&btt=1&t={$token}&or={$baseUrl}&api={$baseUrl}&gi={$gameId}&n={$gameId}&l=pt&ct=BRL";

        return [
            'ok' => true,
            'url' => $launchUrl,
            'mode' => 'local'
        ];
    }

    /**
     * Lança jogo do Popok via API local (porta 3030)
     */
    private static function launchPopokGame($user, array $game): array
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = rtrim($protocol . $host, '/');

        $userCode = $user ? (string)$user->getId() : ('demo_' . (session_id() ? substr(session_id(), 0, 8) : bin2hex(random_bytes(4))));
        $userBalance = $user ? (float)$user->getCredito() : 1000.00;

        $popokGameId = $game['popok_game_id'] ?? '167';
        $gameName = $game['game_code'] ?? 'yo-dragon';

        // Tenta API local do Popok (porta 3030)
        $popokApiUrl = 'http://127.0.0.1:3030/api/launch';

        if (function_exists('curl_init')) {
            $payload = [
                'gameId' => $popokGameId,
                'name' => $gameName,
                'balance' => round($userBalance, 2),
                'lang' => 'pt',
                'currency' => 'BRL',
                'user_id' => $userCode,
            ];

            $ch = curl_init($popokApiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);

            $body = curl_exec($ch);
            $httpStatus = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body && $httpStatus >= 200 && $httpStatus < 300) {
                $res = json_decode($body, true);
                if (is_array($res) && isset($res['launch_url']) && !empty($res['launch_url'])) {
                    return [
                        'ok' => true,
                        'url' => $res['launch_url'],
                        'mode' => 'popok_api'
                    ];
                }
            }
        }

        // Fallback: abre o jogo diretamente (modo demo)
        $gamePath = $game['game_path'] ?? '/games/popok/public/games/yo-dragon';
        $launchUrl = "{$baseUrl}{$gamePath}/index.html";

        return [
            'ok' => true,
            'url' => $launchUrl,
            'mode' => 'popok_local'
        ];
    }
}
