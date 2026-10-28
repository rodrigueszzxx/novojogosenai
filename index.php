<?php

require_once __DIR__ . '/php/conexao.php';
try { $pdo = conectarBanco(); } catch (Throwable $e) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Hero Jump</title><p>Não foi possível conectar ao banco do jogo. Tente novamente mais tarde.</p><a href="pages/sobre-nos.php">Conheça a VORTEX</a></html>';
    exit;
}

/**
 * Lista de personagens jogáveis.
 * Para adicionar um novo: coloque as imagens em assets/characters/ e inclua um item aqui.
 *  - 'run'  = imagem do personagem correndo (PNG, GIF ou SVG)
 *  - 'dead' = imagem do personagem quando perde (Game Over)
 *  - 'cenario' = imagem de fundo do jogo desse personagem
 *  - 'coleta' = imagem do item que concede pontos ao personagem
 *  - 'obstaculo' = imagem do obstáculo desse personagem
 */
$spritesColetaveis = json_decode(file_get_contents(__DIR__ . '/assets/collectibles/frames.json'), true, 512, JSON_THROW_ON_ERROR);

$personagens = [
    ['id' => 'pikachu', 'coleta_sprite' => 'pikachu', 'coleta' => 'assets/collectibles/pikachu.png', 'nome' => 'Pikachu', 'run' => 'assets/characters/pikachu.gif', 'dead' => 'assets/characters/pikachu.gif', 'cenario' => 'assets/cenario-pikachu.jpg', 'obstaculo' => 'assets/obstaculo-pikachu.png'],
    ['id' => 'bart', 'coleta_sprite' => 'bart', 'coleta' => 'assets/collectibles/bart.png', 'nome' => 'Bart', 'run' => 'assets/characters/bart.gif', 'dead' => 'assets/characters/bart.gif', 'cenario' => 'assets/cenario-bart.png', 'obstaculo' => 'assets/obstaculo-bart.png'],
    ['id' => 'sasuke', 'coleta_sprite' => 'sasuke', 'coleta' => 'assets/collectibles/sasuke.png', 'nome' => 'Sasuke', 'run' => 'assets/characters/sasuke.gif', 'dead' => 'assets/characters/sasuke.gif', 'cenario' => 'assets/cenario-sasuke.png', 'obstaculo' => 'assets/obstaculo-sasuke.png'],
    ['id' => 'sonic', 'coleta' => 'assets/collectibles/sonic.gif', 'nome' => 'Sonic', 'run' => 'assets/characters/sonic.gif', 'dead' => 'assets/characters/sonic.gif', 'cenario' => 'assets/cenario-sonic.jpg', 'obstaculo' => 'assets/obstaculo-sonic.png'],
];

// Busca inicial do ranking para carregar rápido na primeira abertura
$rankingInicial = [];
try {
    $stmt = $pdo->query("
        SELECT id, nome, pontos, DATE_FORMAT(data_registro, '%d/%m/%Y %H:%i') as data 
        FROM ranking 
        ORDER BY pontos DESC, id ASC 
        LIMIT 10
    ");
    $rankingInicial = $stmt->fetchAll();
} catch (Exception $e) {
    // Mantém a tela disponível se a consulta falhar (ranking vazio não lança exceção).
    $rankingInicial = [];
}

// Recorde (1º lugar) mostrado na tela de abertura
$recorde = !empty($rankingInicial) ? $rankingInicial[0] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Jump | VORTEX</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=raichu-5">
</head>
<body>

    <div class="game-container">
        
        <header class="hud-bar">
            <div class="player-info">
                <span class="label">JOGADOR:</span>
                <span class="value" id="hud-player-name">---</span>
            </div>
            <div class="speed-info">
                <span class="label">VEL:</span>
                <span class="value" id="speed-display">1.0x</span>
            </div>
            <div class="collect-info">
                <span class="label">ITENS:</span>
                <span class="value" id="collect-display">0</span>
            </div>
            <div class="score-info">
                <span class="label">PONTOS:</span>
                <span class="value" id="score-display">0</span>
            </div>
        </header>

        
        <main class="game-board" id="game-board">

            <img src="<?= htmlspecialchars($personagens[0]['run']) ?>" class="hero" alt="Personagem">
            <img src="<?= htmlspecialchars($personagens[0]['obstaculo']) ?>" class="pipe" alt="Obstáculo">
            <div class="collect-feedback" id="collect-feedback" role="status" aria-live="polite"></div>
            <span class="jump-tip" id="jump-tip">Pressione ESPAÇO ou clique para pular</span>
        </main>
    </div>

    <!-- 0. TELA INICIAL (aparece ao entrar no site e ao clicar em "Voltar à tela inicial") -->
    <div class="splash active" id="splash-screen" role="dialog" aria-label="Hero Jump - tela inicial">
        <div class="splash-bg" aria-hidden="true"></div>

        <div class="splash-content">
            <div class="studio-signature" aria-label="VORTEX apresenta">
                <span class="vortex-mark"><img src="assets/brand/vortex.jpg" alt="Logotipo VORTEX" width="2814" height="1536"></span>
                <span class="studio-name">VORTEX<span class="studio-caption">APRESENTA</span></span>
            </div>
            <h1 class="splash-title">HERO<br>JUMP</h1>

            <p class="splash-record">🏆 RECORDE: <span id="splash-record-text"><?= $recorde ? htmlspecialchars($recorde['nome']) . ' - ' . (int)$recorde['pontos'] . ' PTS' : '---' ?></span></p>

            <button type="button" class="btn btn-primary splash-play" id="btn-splash-play">INICIAR JOGO</button>
            <a class="about-link" href="pages/sobre-nos.php">Sobre nós <span aria-hidden="true">→</span></a>
        </div>
    </div>

    <!-- 0.1 COMO JOGAR (abre ao clicar em "Iniciar jogo" na tela inicial) -->
    <div class="modal-overlay" id="howto-modal" role="dialog" aria-modal="true" aria-labelledby="howto-title">
        <div class="modal-card howto-card">
            <h2 class="game-title" id="howto-title">COMO JOGAR</h2>

            <ul class="howto-list">
                <li>
                    <span class="howto-label">PULAR</span>
                    <span>Aperte <span class="key">ESPAÇO</span> ou <span class="key">&uarr;</span>, ou clique/toque no cenário.</span>
                </li>
                <li>
                    <span class="howto-label">DESVIAR</span>
                    <span>Pule por cima dos obstáculos. Bateu em um, acabou!</span>
                </li>
                <li>
                    <span class="howto-label">PONTUAR</span>
                    <span>Quanto mais tempo você sobreviver, mais pontos faz. Colete os itens do seu personagem para ganhar 50 pontos extras por item.</span>
                </li>
                <li>
                    <span class="howto-label">RANKING</span>
                    <span>Sua pontuação é salva e os 10 melhores aparecem no ranking.</span>
                </li>
            </ul>

            <div class="modal-actions">
                <button type="button" class="btn btn-primary" id="btn-howto-ok">OK</button>
            </div>
        </div>
    </div>

    <!-- 1. TELA DE INÍCIO / INSERIR NOME / ESCOLHER PERSONAGEM -->
    <div class="modal-overlay" id="start-modal">
        <div class="modal-card start-card">
            <div class="modal-header">
                <h1 class="game-title">HERO JUMP</h1>
                <p class="game-subtitle">Corra pela cidade, desvie dos obstáculos e dispute o topo do ranking!</p>
            </div>

            <form class="start-form" id="start-form">
                <div class="input-group">
                    <label for="player-input">DIGITE SEU NOME:</label>
                    <input 
                        type="text" 
                        id="player-input" 
                        placeholder="Ex: Seu Nome" 
                        maxlength="30" 
                        required 
                        autocomplete="off"
                        autofocus
                    >
                </div>

                <div class="input-group">
                    <span class="field-label" id="character-label">ESCOLHA SEU PERSONAGEM:</span>
                    <div class="character-select" role="radiogroup" aria-labelledby="character-label">
                        <?php foreach ($personagens as $i => $p): ?>
                            <label class="character-card">
                                <input
                                    type="radio"
                                    name="character"
                                    value="<?= htmlspecialchars($p['id']) ?>"
                                    data-coleta="<?= htmlspecialchars($p['coleta']) ?>"
                                    data-run="<?= htmlspecialchars($p['run']) ?>"
                                    data-dead="<?= htmlspecialchars($p['dead']) ?>"
                                    data-cenario="<?= htmlspecialchars($p['cenario']) ?>"
                                    data-coleta-sprite="<?= htmlspecialchars($p['coleta_sprite'] ?? '') ?>"
                                    data-obstaculo="<?= htmlspecialchars($p['obstaculo']) ?>"
                                    <?= $i === 0 ? 'checked' : '' ?>
                                >
                                <span class="card-body">
                                    <img src="<?= htmlspecialchars($p['run']) ?>" alt="">
                                    <span class="card-name"><?= htmlspecialchars($p['nome']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">INICIAR JOGO</button>
                    <button type="button" class="btn btn-secondary" id="btn-view-ranking">VER RANKING</button>
                </div>
            </form>

            <div class="server-status">
                <span class="status-dot online"></span> Conectado ao MySQL
            </div>
        </div>
    </div>

    <!-- 2. TELA DE GAME OVER & RANKING -->
    <div class="modal-overlay" id="gameover-modal">
        <div class="modal-card gameover-card">
            <div class="modal-header">
                <h2 class="gameover-title">GAME OVER</h2>
                <div class="final-score-box">
                    <div class="final-score-label">SUA PONTUAÇÃO</div>
                    <div class="final-score-num" id="final-score-value">0</div>
                    <div class="save-status" id="save-status-msg"></div>
                </div>
            </div>

            <!-- Tabela com o TOP 10 -->
            <div class="ranking-container">
                <h3 class="ranking-title">🏆 TOP 10 MELHORES PONTUAÇÕES</h3>
                <div class="table-wrapper">
                    <table class="ranking-table">
                        <thead>
                            <tr>
                                <th>POS</th>
                                <th>JOGADOR</th>
                                <th>PONTUAÇÃO</th>
                            </tr>
                        </thead>
                        <tbody id="ranking-body">
                            <?php if (!empty($rankingInicial)): ?>
                                <?php foreach ($rankingInicial as $i => $row): ?>
                                    <tr>
                                        <td class="ranking-pos">
                                            <?= $i === 0 ? '🥇 1º' : ($i === 1 ? '🥈 2º' : ($i === 2 ? '🥉 3º' : ($i + 1) . 'º')) ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($row['nome']) ?></strong></td>
                                        <td><?= (int)$row['pontos'] ?> pts</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="loading-cell">Nenhuma pontuação gravada ainda. Jogue para ser o 1º!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-primary" id="btn-restart">JOGAR NOVAMENTE</button>
                <button type="button" class="btn btn-secondary" id="btn-change-player">TROCAR JOGADOR</button>
                <button type="button" class="btn btn-secondary" id="btn-home">VOLTAR À TELA INICIAL</button>
            </div>
        </div>
    </div>

   
    <script type="application/json" id="collectible-sprites"><?= json_encode($spritesColetaveis, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <script src="js/script.js?v=raichu-5" defer></script>
</body>
</html>
