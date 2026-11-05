<?php

namespace app\modules\admin\controllers\config;

use app\core\Controller;
use app\models\DadosModel;
use app\models\HistoricoModel;
use app\modules\admin\Admin;
use app\vo\admin\MenuVO;
use Exception;

class personalizacaoController extends Controller
{

    function indexAction(MenuVO $p)
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        $webhookUrl = rtrim($protocol . $host, '/') . '/' . ltrim(url('pixup/webhook', null, 'api'), '/');

        $this->view('admin/configuracoes/personalizacao', [
            'config' => DadosModel::get(),
            'temas' => DadosModel::getTemas(),
            'temaPadrao' => 'site1',
            'webhookUrl' => $webhookUrl,
        ]);
    }

    public function insertAction()
    {
        if (!IS_AJAX) {
            location();
        }

        try {

            if (!Admin::isMaster() && !Admin::isAdmin()) {
                throw new Exception('Acesso restrito a administradores.');
            }

            $post = inputPost();
            if (isset($post['tema'])) {
                $temasValidos = array_keys(DadosModel::getTemas());
                if (!in_array($post['tema'], $temasValidos, true)) {
                    throw new Exception('Tema selecionado é inválido.');
                }
            }

            if (isset($post['exibirescudos'])) {
                $post['exibirescudos'] = ((int)$post['exibirescudos'] === 1) ? 1 : 0;
            }

            if (isset($post['exibircassino'])) {
                $post['exibircassino'] = ((int)$post['exibircassino'] === 1) ? 1 : 0;
            }

            if (isset($post['tiposlideshome'])) {
                $tiposValidos = ['mesclado', 'banners', 'jogos', 'futebol_banners', 'cassino_banners', 'desativado'];
                if (!in_array($post['tiposlideshome'], $tiposValidos, true)) {
                    $post['tiposlideshome'] = 'mesclado';
                }
            }

            if (isset($post['tiposlidescassino'])) {
                $tiposValidosCassino = ['mesclado', 'banners', 'jogos', 'desativado'];
                if (!in_array($post['tiposlidescassino'], $tiposValidosCassino, true)) {
                    $post['tiposlidescassino'] = 'mesclado';
                }
            }

            $config = DadosModel::get();

            $config
                ->save($post);

            HistoricoModel::add("Alterou a personalização do sistema (temas, botões, dimensões e PixUp)", $config, Admin::getLogged());

            return [
                'message' => 'Personalização atualizada com sucesso.',
                'result' => 1,
            ];
        } catch (Exception $ex) {
            return $ex;
        }
    }

}
