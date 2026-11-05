-- ============================================
-- INTEGRAÇÃO WG COMPLETA - Todos os Jogos
-- ============================================

-- 1. Adicionar colunas se não existem
ALTER TABLE casino_games ADD COLUMN IF NOT EXISTS provider VARCHAR(20) DEFAULT 'PGSOFT';
ALTER TABLE casino_games ADD COLUMN IF NOT EXISTS game_path VARCHAR(255) DEFAULT NULL;
ALTER TABLE casino_games ADD COLUMN IF NOT EXISTS wg_game_id VARCHAR(20) DEFAULT NULL;

-- 2. Atualizar jogos existentes
UPDATE casino_games SET provider = 'PGSOFT' WHERE provider IS NULL;

-- 3. Remover jogos antigos do WG
DELETE FROM casino_games WHERE provider = 'WG';

-- 4. Inserir TODOS os jogos do WG com imagens -wg.avif
INSERT INTO casino_games (name, game_code, game_id, category, provider, game_path, wg_game_id, image, rtp, active, featured, ordem) VALUES
-- Fortune Rabbit 2 (3049)
('Fortune Rabbit 2', 'fortune-rabbit2', '3049', 'WG', 'WG', '/games/WG/public/fortune-rabbit2', '3049', '/imagens/jogos/fortune-rabbit2-wg.avif', 96.50, 1, 1, 1),
-- Fortune Tiger (3031)
('Fortune Tiger', 'fortune-tiger', '3031', 'WG', 'WG', '/games/WG/public/fortune-tiger', '3031', '/imagens/jogos/fortune-tiger-wg.avif', 96.50, 1, 1, 2),
-- Fortune Rabbit 1 (3035)
('Fortune Rabbit', 'fortune-rabbit', '3035', 'WG', 'WG', '/games/WG/public/fortune-rabbit', '3035', '/imagens/jogos/fortune-rabbit-wg.avif', 96.50, 1, 1, 3),
-- Fortune House (3060)
('Fortune House', 'fortune-house', '3060', 'WG', 'WG', '/games/WG/public/fortune-house', '3060', '/imagens/jogos/fortune-house-wg.avif', 96.50, 1, 0, 4),
-- Fortune Snake (3038)
('Fortune Snake', 'fortune-snake', '3038', 'WG', 'WG', '/games/WG/public/fortune-snake', '3038', '/imagens/jogos/fortune-snake-wg.avif', 96.50, 1, 0, 5),
-- Treasure Dragon (3023)
('Treasure Dragon', 'treasure-dragon', '3023', 'WG', 'WG', '/games/WG/public/treasure-dragon', '3023', '/imagens/jogos/treasure-dragon-wg.avif', 96.50, 1, 0, 6),
-- Fortune Ox (3033)
('Fortune Ox', 'fortune-ox', '3033', 'WG', 'WG', '/games/WG/public/fortune-ox', '3033', '/imagens/jogos/fortune-ox-wg.avif', 96.50, 1, 1, 7),
-- Fortune Mouse (3042)
('Fortune Mouse', 'fortune-mouse', '3042', 'WG', 'WG', '/games/WG/public/fortune-mouse', '3042', '/imagens/jogos/fortune-mouse-wg.avif', 96.50, 1, 0, 8),
-- Dragon's Treasure 2 (3028)
('Dragon\'s Treasure 2', 'dragons-treasure2', '3028', 'WG', 'WG', '/games/WG/public/dragons-treasure2', '3028', '/imagens/jogos/dragons-treasure2-wg.avif', 96.50, 1, 0, 9),
-- Leopard of Gold (3010)
('Leopard of Gold', 'leopard-gold', '3010', 'WG', 'WG', '/games/WG/public/leopard-gold', '3010', '/imagens/jogos/leopard-gold-wg.avif', 96.50, 1, 0, 10);

-- 5. Verificar jogos cadastrados
SELECT id, name, game_code, provider, category, active, image FROM casino_games WHERE provider = 'WG' ORDER BY ordem ASC;