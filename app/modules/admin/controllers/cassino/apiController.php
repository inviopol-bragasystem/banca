<?php

namespace app\modules\admin\controllers\cassino;

use app\core\Controller;
use app\models\CassinoModel;
use app\models\HistoricoModel;
use app\modules\admin\Admin;
use app\vo\admin\MenuVO;
use Exception;

class apiController extends Controller
{
    public function indexAction(MenuVO $menu)
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        $baseUrl = rtrim($protocol . $host, '/');

        $callbackUrl = $baseUrl . '/api/cassino/callback';
        $balanceUrl = $baseUrl . '/api/cassino/balance';

        $this->view('admin/cassino/api', [
            'settings' => CassinoModel::getSettings(),
            'callbackUrl' => $callbackUrl,
            'balanceUrl' => $balanceUrl,
            'menu' => $menu,
        ]);
    }

    public function salvarAction()
    {
        if (!IS_AJAX) {
            location();
        }

        try {
            if (!Admin::isMaster() && !Admin::isAdmin()) {
                throw new Exception('Acesso restrito a administradores.');
            }

            $post = inputPost();
            $post['active'] = isset($post['active']) ? (int)$post['active'] : 1;

            CassinoModel::saveSettings($post);
            HistoricoModel::add("Atualizou as configurações da API de Cassino PG Soft", null, Admin::getLogged());

            return [
                'result' => 1,
                'message' => 'Configurações de API salvas com sucesso!'
            ];
        } catch (Exception $e) {
            return $e;
        }
    }

    public function testarAction()
    {
        if (!IS_AJAX) {
            location();
        }

        try {
            $settings = CassinoModel::getSettings();
            $apiUrl = rtrim($settings['api_url'], '/');

            if (empty($apiUrl)) {
                throw new Exception('Informe a URL da API para testar.');
            }

            $ch = curl_init($apiUrl . '/api/v1/user_balance');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'agent_secret' => $settings['secret_key'],
                'user_code' => '999999'
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                return [
                    'result' => 0,
                    'message' => "Falha na conexão: {$err}"
                ];
            }

            return [
                'result' => 1,
                'message' => "Servidor respondeu com HTTP {$httpCode}. Conexão estabelecida com sucesso!"
            ];
        } catch (Exception $e) {
            return $e;
        }
    }
}
