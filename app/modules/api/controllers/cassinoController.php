<?php

namespace app\modules\api\controllers;

use app\core\Controller;
use app\core\crud\Conn;
use app\models\CassinoModel;
use PDO;
use Exception;

class cassinoController extends Controller
{
    /**
     * Webhook de consulta de saldo antes da rodada
     * URL: http://dominio/api/cassino/balance
     */
    public function balanceAction()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: $_POST;

        $userCode = trim($data['user_code'] ?? ($data['user_id'] ?? ''));
        if (empty($userCode)) {
            return ['status' => 1, 'msg' => 'SUCCESS'];
        }

        $pdo = Conn::getConn();
        $stmt = $pdo->prepare("SELECT credito FROM sis_users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$userCode]);
        $credito = (float)$stmt->fetchColumn();

        return [
            'status' => 1,
            'msg' => 'SUCCESS',
            'user_balance' => round($credito, 2)
        ];
    }

    /**
     * Webhook de processamento de rodada (Aposta e Ganho) do PGV4
     * URL: http://dominio/api/cassino/callback
     */
    public function callbackAction()
    {
        try {
            $settings = CassinoModel::getSettings();
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;

            // Suporte unificado para chamadas Pragmatic direcionadas a /api/cassino/callback
            if ((isset($data['method']) && in_array($data['method'], ['user_balance', 'game_callback', 'transaction'])) 
                || (isset($data['provider_code']) && strtoupper($data['provider_code']) === 'PRAGMATICPLAY')) {
                return $this->processPragmaticWebhook();
            }

            // Validação de Secret Key
            $expectedSecret = trim($settings['secret_key']);
            $receivedSecret = trim($data['agent_secret'] ?? ($data['secretKey'] ?? ''));

            if (!empty($expectedSecret) && !empty($receivedSecret) && $expectedSecret !== $receivedSecret) {
                http_response_code(401);
                return ['status' => 0, 'message' => 'Unauthorized'];
            }

            $userCode = trim((string)($data['user_code'] ?? ''));
            $slot = is_array($data['slot'] ?? null) ? $data['slot'] : [];

            $transactionId = trim((string)($slot['txn_id'] ?? ($data['txn_id'] ?? '')));
            $roundId = trim((string)($slot['round_id'] ?? ($data['round_id'] ?? $transactionId)));
            $gameCode = trim((string)($slot['game_code'] ?? ($data['game_code'] ?? 'PGSOFT')));
            $bet = round((float)($slot['bet'] ?? ($data['bet'] ?? 0)), 2);
            $win = round((float)($slot['win'] ?? ($data['win'] ?? 0)), 2);

            if (empty($userCode) || empty($transactionId)) {
                return ['status' => 0, 'message' => 'Invalid parameters.'];
            }

            $pdo = Conn::getConn();

            // Evitar processamento duplo da mesma transação
            $chk = $pdo->prepare("SELECT id FROM casino_history WHERE txn_id = :tx LIMIT 1");
            $chk->execute(['tx' => $transactionId]);
            if ($chk->fetchColumn()) {
                $stmtBal = $pdo->prepare("SELECT credito FROM sis_users WHERE id = :id LIMIT 1");
                $stmtBal->execute(['id' => (int)$userCode]);
                $saldo = (float)$stmtBal->fetchColumn();

                return [
                    'status' => 1,
                    'msg' => 'SUCCESS',
                    'user_balance' => round($saldo, 2)
                ];
            }

            // Inicia transação segura no banco de dados
            $pdo->beginTransaction();

            $stmtUser = $pdo->prepare("SELECT id, credito, nome FROM sis_users WHERE id = :id FOR UPDATE");
            $stmtUser->execute(['id' => (int)$userCode]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $pdo->rollBack();
                return ['status' => 0, 'message' => 'User not found.'];
            }

            $saldoAnterior = (float)$user['credito'];

            if ($bet > $saldoAnterior) {
                $pdo->rollBack();
                return ['status' => 0, 'message' => 'Insufficient balance.'];
            }

            // Calcula novo saldo: deduz aposta e soma prêmio
            $novoSaldo = max(0, $saldoAnterior - $bet) + $win;

            // Atualiza o saldo do usuário
            $upd = $pdo->prepare("UPDATE sis_users SET credito = :saldo, `update` = NOW() WHERE id = :id");
            $upd->execute(['saldo' => $novoSaldo, 'id' => (int)$userCode]);

            // Busca nome do jogo para o relatório
            $game = CassinoModel::getGameByCode($gameCode);
            $gameNome = $game ? $game['name'] : $gameCode;

            // Registra débito da aposta se houver
            if ($bet > 0) {
                $insBet = $pdo->prepare("INSERT INTO casino_history (user_id, game_code, game_name, provider, round_id, txn_id, action, amount, balance_before, balance_after, status, created_at) 
                    VALUES (:uid, :gcode, :gname, 'PGSOFT', :round, :tx, 'bet', :amt, :bb, :ba, 'completed', NOW())");
                $insBet->execute([
                    'uid' => (int)$userCode,
                    'gcode' => $gameCode,
                    'gname' => $gameNome,
                    'round' => $roundId,
                    'tx' => $transactionId . '_bet',
                    'amt' => $bet,
                    'bb' => $saldoAnterior,
                    'ba' => $saldoAnterior - $bet
                ]);
            }

            // Registra premiação do ganho se houver
            if ($win > 0) {
                $insWin = $pdo->prepare("INSERT INTO casino_history (user_id, game_code, game_name, provider, round_id, txn_id, action, amount, balance_before, balance_after, status, created_at) 
                    VALUES (:uid, :gcode, :gname, 'PGSOFT', :round, :tx, 'win', :amt, :bb, :ba, 'completed', NOW())");
                $insWin->execute([
                    'uid' => (int)$userCode,
                    'gcode' => $gameCode,
                    'gname' => $gameNome,
                    'round' => $roundId,
                    'tx' => $transactionId,
                    'amt' => $win,
                    'bb' => $saldoAnterior - $bet,
                    'ba' => $novoSaldo
                ]);
            } else if ($bet > 0) {
                // Caso não tenha ganho, marca a transação principal para idempotência
                $insTx = $pdo->prepare("INSERT INTO casino_history (user_id, game_code, game_name, provider, round_id, txn_id, action, amount, balance_before, balance_after, status, created_at) 
                    VALUES (:uid, :gcode, :gname, 'PGSOFT', :round, :tx, 'bet', :amt, :bb, :ba, 'completed', NOW())");
                $insTx->execute([
                    'uid' => (int)$userCode,
                    'gcode' => $gameCode,
                    'gname' => $gameNome,
                    'round' => $roundId,
                    'tx' => $transactionId,
                    'amt' => $bet,
                    'bb' => $saldoAnterior,
                    'ba' => $novoSaldo
                ]);
            }

            $pdo->commit();

            return [
                'status' => 1,
                'msg' => 'SUCCESS',
                'user_balance' => round($novoSaldo, 2)
            ];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['status' => 0, 'message' => $e->getMessage()];
        }
    }

    /**
     * Webhook unificado da API Pragmatic Play
     * URL: http://dominio/api/cassino/pragmatic
     */
    public function pragmaticAction()
    {
        return $this->processPragmaticWebhook();
    }

    /**
     * Webhook com sufixo callback para compatibilidade de rotas
     * URL: http://dominio/api/cassino/pragmaticCallback
     */
    public function pragmaticCallbackAction()
    {
        return $this->processPragmaticWebhook();
    }

    /**
     * Processamento central de webhooks da Pragmatic Play (Seamless Wallet)
     */
    private function processPragmaticWebhook()
    {
        try {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: $_POST;

            $method = trim($data['method'] ?? '');
            $userCode = trim((string)($data['user_code'] ?? ($data['user_id'] ?? '')));

            $pdo = Conn::getConn();

            // MODO 1: Consulta de Saldo do Jogador (user_balance)
            if ($method === 'user_balance' || (empty($method) && !isset($data['slot']) && !isset($data['bet']))) {
                if (empty($userCode) || strpos($userCode, 'demo_') === 0) {
                    return [
                        'status' => 1,
                        'msg' => 'SUCCESS',
                        'user_balance' => 1000.00,
                        'balance' => 1000.00
                    ];
                }

                $stmt = $pdo->prepare("SELECT credito FROM sis_users WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => (int)$userCode]);
                $credito = (float)$stmt->fetchColumn();

                return [
                    'status' => 1,
                    'msg' => 'SUCCESS',
                    'user_balance' => round($credito, 2),
                    'balance' => round($credito, 2)
                ];
            }

            // MODO 2: Transação de Rodada (game_callback / transaction)
            $settings = \app\models\PragmaticModel::getSettings();
            $expectedSecret = trim($settings['secret_key'] ?? '');
            $receivedSecret = trim($data['agent_secret'] ?? ($data['secretKey'] ?? ''));

            if (!empty($expectedSecret) && !empty($receivedSecret) && $expectedSecret !== $receivedSecret) {
                http_response_code(401);
                return ['status' => 0, 'message' => 'Unauthorized'];
            }

            $slot = is_array($data['slot'] ?? null) ? $data['slot'] : [];

            $transactionId = trim((string)($slot['txn_id'] ?? ($data['txn_id'] ?? '')));
            $roundId = trim((string)($slot['round_id'] ?? ($data['round_id'] ?? $transactionId)));
            $gameCode = trim((string)($slot['game_code'] ?? ($data['game_code'] ?? 'PRAGMATIC')));
            $bet = round((float)($slot['bet'] ?? ($data['bet'] ?? 0)), 2);
            $win = round((float)($slot['win'] ?? ($data['win'] ?? 0)), 2);

            if (empty($userCode) || empty($transactionId)) {
                return ['status' => 0, 'message' => 'Invalid parameters.'];
            }

            // Jogador em modo demonstração
            if (strpos($userCode, 'demo_') === 0) {
                return [
                    'status' => 1,
                    'msg' => 'SUCCESS',
                    'user_balance' => 1000.00,
                    'balance' => 1000.00
                ];
            }

            // Idempotência: evitar duplicidade de débito/crédito
            $chk = $pdo->prepare("SELECT id FROM casino_history WHERE txn_id = :tx LIMIT 1");
            $chk->execute(['tx' => $transactionId]);
            if ($chk->fetchColumn()) {
                $stmtBal = $pdo->prepare("SELECT credito FROM sis_users WHERE id = :id LIMIT 1");
                $stmtBal->execute(['id' => (int)$userCode]);
                $saldo = (float)$stmtBal->fetchColumn();

                return [
                    'status' => 1,
                    'msg' => 'SUCCESS',
                    'user_balance' => round($saldo, 2),
                    'balance' => round($saldo, 2)
                ];
            }

            // Inicia transação segura no banco
            $pdo->beginTransaction();

            $stmtUser = $pdo->prepare("SELECT id, credito, nome FROM sis_users WHERE id = :id FOR UPDATE");
            $stmtUser->execute(['id' => (int)$userCode]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $pdo->rollBack();
                return ['status' => 0, 'message' => 'User not found.'];
            }

            $saldoAnterior = (float)$user['credito'];

            if ($bet > $saldoAnterior) {
                $pdo->rollBack();
                return ['status' => 0, 'message' => 'Insufficient balance.'];
            }

            // Calcula novo saldo
            $novoSaldo = max(0, $saldoAnterior - $bet) + $win;

            // Atualiza saldo do usuário
            $upd = $pdo->prepare("UPDATE sis_users SET credito = :saldo, `update` = NOW() WHERE id = :id");
            $upd->execute(['saldo' => $novoSaldo, 'id' => (int)$userCode]);

            // Busca nome amigável do jogo
            $game = CassinoModel::getGameByCode($gameCode);
            $gameNome = $game ? $game['name'] : $gameCode;

            // Registra aposta
            if ($bet > 0) {
                $insBet = $pdo->prepare("INSERT INTO casino_history (user_id, game_code, game_name, provider, round_id, txn_id, action, amount, balance_before, balance_after, status, created_at) 
                    VALUES (:uid, :gcode, :gname, 'PRAGMATIC', :round, :tx, 'bet', :amt, :bb, :ba, 'completed', NOW())");
                $insBet->execute([
                    'uid' => (int)$userCode,
                    'gcode' => $gameCode,
                    'gname' => $gameNome,
                    'round' => $roundId,
                    'tx' => $transactionId . '_bet',
                    'amt' => $bet,
                    'bb' => $saldoAnterior,
                    'ba' => $saldoAnterior - $bet
                ]);
            }

            // Registra ganho
            if ($win > 0) {
                $insWin = $pdo->prepare("INSERT INTO casino_history (user_id, game_code, game_name, provider, round_id, txn_id, action, amount, balance_before, balance_after, status, created_at) 
                    VALUES (:uid, :gcode, :gname, 'PRAGMATIC', :round, :tx, 'win', :amt, :bb, :ba, 'completed', NOW())");
                $insWin->execute([
                    'uid' => (int)$userCode,
                    'gcode' => $gameCode,
                    'gname' => $gameNome,
                    'round' => $roundId,
                    'tx' => $transactionId,
                    'amt' => $win,
                    'bb' => $saldoAnterior - $bet,
                    'ba' => $novoSaldo
                ]);
            } else if ($bet > 0) {
                $insTx = $pdo->prepare("INSERT INTO casino_history (user_id, game_code, game_name, provider, round_id, txn_id, action, amount, balance_before, balance_after, status, created_at) 
                    VALUES (:uid, :gcode, :gname, 'PRAGMATIC', :round, :tx, 'bet', :amt, :bb, :ba, 'completed', NOW())");
                $insTx->execute([
                    'uid' => (int)$userCode,
                    'gcode' => $gameCode,
                    'gname' => $gameNome,
                    'round' => $roundId,
                    'tx' => $transactionId,
                    'amt' => $bet,
                    'bb' => $saldoAnterior,
                    'ba' => $novoSaldo
                ]);
            }

            $pdo->commit();

            return [
                'status' => 1,
                'msg' => 'SUCCESS',
                'user_balance' => round($novoSaldo, 2),
                'balance' => round($novoSaldo, 2)
            ];

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['status' => 0, 'message' => $e->getMessage()];
        }
    }
}

