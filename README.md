# VORTEX · Hero Jump

## Executar

No Laragon, copie a pasta `jogosenai` para `www`, inicie PHP e MySQL e abra `http://localhost/jogosenai/`. PHP precisa de PDO MySQL e mbstring.

Ajuste a conexão em `php/conexao.php` se necessário. Também são aceitas as variáveis DB_HOST, DB_PORT, DB_USER, DB_PASSWORD e DB_NAME. O banco `hero_jump` e a tabela `ranking` são criados automaticamente na primeira conexão.

## Pastas usadas

- `index.php`: jogo e abertura com a marca VORTEX.
- `pages/sobre-nos.php`: história da empresa e do jogo; não depende de banco ou JavaScript.
- `php/conexao.php`: conexão e criação da estrutura do ranking.
- `php/obter-ranking.php`: consulta das dez maiores pontuações.
- `php/salvar-ranking.php`: gravação da pontuação ao encerrar uma partida.
- `js/script.js`: controles, animação dos coletáveis, pontuação e chamadas do ranking.
- `css/styles.css`: estilos do jogo, da abertura e da história.
- `assets/`: cenários, obstáculos, marca, personagens e coletáveis.

Não há pasta `api`, `database`, `config` ou `docs`, nem formulário, JavaScript de contato ou CSS separado do Sobre nós. A documentação está neste README.

## Jogo

Escolha um dos quatro personagens e pule com Espaço, seta para cima ou toque no cenário. Evite os obstáculos e colete itens: cada coleta concede 50 pontos, além da pontuação por sobrevivência. O contador é zerado em cada nova partida. A velocidade aumenta progressivamente; ainda não existem três níveis nomeados.

Doce, lata e pergaminho são coletáveis animados com folhas em `assets/collectibles/sprites/` e mapeamento em `assets/collectibles/frames.json`. Sonic usa um anel em GIF. Os PNGs de referência/fallback permanecem em `assets/collectibles/`. Obstáculos usam apenas as imagens `assets/obstaculo-*.png`.

## Sobre nós

A página contém somente a origem fictícia da VORTEX e a evolução do Hero Jump. Edite os textos diretamente em `pages/sobre-nos.php`. O logotipo fica em `assets/brand/vortex.jpg`.

## Atualizar uma versão antiga

Extraia este ZIP em uma pasta nova. Extrair por cima da pasta anterior não remove arquivos antigos. Preserve seus ajustes de conexão, aplicando-os ao novo `php/conexao.php`. Nenhuma pontuação ou mensagem já gravada no banco é apagada por esta atualização.

## Easter egg: Pikachu e Raichu

Somente o Pikachu pode evoluir. Ao alcançar pelo menos 1.000 pontos reais da partida, uma Pedra do Trovão substitui o próximo coletável. Ela usa a folha de 20 quadros em `assets/collectibles/sprites/thunderstone.png`, recortada na exibição pelo mapeamento em `assets/collectibles/frames.json`.

Coletar a pedra concede os 50 pontos do item e transforma o personagem em Raichu, usando o GIF original em `assets/characters/raichu.gif`. Cenário, obstáculos, velocidade e pontuação continuam na mesma partida. A caixa do personagem é preservada durante a troca, inclusive no pulo. Se perder a pedra, ela reaparece até ser coletada. Depois da evolução, os coletáveis normais retornam. Cada nova partida começa como Pikachu; o evento não se aplica aos outros três personagens.

Há um brilho breve na evolução, com respeito à preferência de movimento reduzido. O evento foi validado com o GIF original em computador e celular, incluindo o limiar de pontos, repetição da pedra perdida, coleta, manutenção do pulo, Game Over, ranking e reinício. A evolução foi testada com relógio controlado e obstáculos isolados; depois, uma colisão foi provocada para verificar o encerramento e o salvamento.
