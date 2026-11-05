<?php

namespace app\modules\website\controllers;

use app\core\Controller;
use app\models\CassinoModel;
use app\models\DadosModel;
use app\modules\admin\Admin;
use Exception;

class cassinoController extends Controller
{
    protected $tpl = 'website/page/cassino';

    public function indexAction()
    {
        $dados = DadosModel::get();
        if (!$dados->getExibirCassino()) {
            location('');
        }

        $category = inputGet('category') ?: 'all';
        $search = inputGet('search');

        $jogos = CassinoModel::getGames(true, $category, $search);
        $user = Admin::getLogged();

        $this->view($this->tpl, [
            'jogos' => $jogos,
            'category' => $category,
            'search' => $search,
            'user' => $user,
            'pageTitle' => 'Cassino & Slots',
        ]);
    }

    public function jogarAction(?string $gameCode = null)
    {
        $dados = DadosModel::get();
        if (!$dados->getExibirCassino()) {
            location('');
        }

        if (empty($gameCode)) {
            $gameCode = url_parans(0) ?: (inputGet('code') ?: inputGet('game'));
        }

        $user = Admin::getLogged();
        $isDemo = false;
        if (!$user) {
            $isDemo = true;
        }

        $game = !empty($gameCode) ? CassinoModel::getGameByCode($gameCode) : null;
        if (!$game || !$game['active']) {
            if (IS_AJAX) {
                return [
                    'result' => 0,
                    'message' => 'Jogo indisponível ou código inválido.'
                ];
            }
            location('cassino');
        }

        $launch = CassinoModel::launchGame($user, $game);

        if (!$launch['ok']) {
            if (IS_AJAX) {
                return [
                    'result' => 0,
                    'message' => $launch['message'] ?? 'Falha ao iniciar jogo.'
                ];
            }
            location('cassino');
        }

        if (IS_AJAX) {
            return [
                'result' => 1,
                'url' => $launch['url'],
                'game' => $game,
                'isDemo' => $isDemo,
                'saldo' => $user ? $user->getCredito(true) : '1.000,00 (Demo)',
            ];
        }

        // Exibe a tela imersiva do jogo se acessado diretamente via URL
        $this->view('website9/page/cassino-player', [
            'game' => $game,
            'launchUrl' => $launch['url'],
            'user' => $user,
            'isDemo' => $isDemo,
        ]);
    }
}
