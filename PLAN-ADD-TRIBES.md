# Plano de Implementação: Adicionar 4 Novas Tribos

## Estratégia de Numeração (Opção B)

| ID | Tribo      | Unidades | Tipo     | Nome Const |
|----|------------|----------|----------|------------|
| 1  | Romanos    | 1-10     | Jogável  | TRIBE1 |
| 2  | Teutões    | 11-20    | Jogável  | TRIBE2 |
| 3  | Gauleses   | 21-30    | Jogável  | TRIBE3 |
| 4  | Nature     | 31-40    | NPC      | TRIBE4 |
| 5  | Natars     | 41-50    | NPC      | TRIBE5 |
| 6  | **Huns**   | **51-60**| Jogável  | TRIBE6 |
| 7  | **Egípcios**| **61-70** | Jogável | TRIBE7 |
| 8  | **Espartanos**| **71-80** | Jogável | TRIBE8 |
| 9  | **Vikings**| **81-90** | Jogável  | TRIBE9 |

> Monsters (ID 6) era código morto — os dados `u51`-`u60` foram substituídos pelos Huns.

---

## ✅ FASE 1: DADOS / BALANCE (PHP data files)

### `GameEngine/Data/unitdata.php`
- **Units `u51`-`u90`**: Stats completos (atk, di, dc, wood, clay, iron, crop, pop, speed, time, cap) para todas as 40 unidades das 4 tribos.
- **`$unitsbytype`**: Atualizado com todos os IDs 51-90 nas categorias: infantry, cavalry, siege, ram, catapult, expansion, scout, chief.
- **Heróis `$h51`-`$h86`**: 20 heróis com stats proporcionais à força da unidade (baseado nos ratios dos templates existentes). Templates usados: h1 (inf padrão), h2 (inf def), h3 (inf atk), h5 (cav média), h6 (cav pesada).

### `GameEngine/Data/hero_full.php`
- **`$h51_full`-`$h86_full`**: 20 arrays × 60 níveis de custo para reviver o herói. Escalonados dos templates existentes pela proporção do custo da unidade. Tempo de revive = `unit_time × 2 × (level+1)`.

### `GameEngine/Data/resdata.php`
- **Pesquisas `$r52`-`$r89`**: 32 pesquisas da Academia (8 por tribo). Dados da planilha `resdata`, tempos convertidos HH:MM:SS → segundos.
- **Upgrades `$ab51`-`$ab88`**: 32 arrays de ferreiro/armeiro × 20 níveis cada. Dados das planilhas `abdata51-58`, `abdata61-68`, `abdata71-78`, `abdata81-88`.

### `GameEngine/Data/buidata.php`
- **`$bid42` (Stone Wall)**: Substituiu o antigo Great Workshop. Dados da planilha `buildata`.
- **`$bid43` (Makeshift Wall)**: Muralha dos Huns.
- **`$bid44` (Command Center)**: Edifício único dos Huns.
- **`$bid45` (Waterworks)**: Edifício único dos Egípcios. `attri` = 0.05 a 1.0 (bônus de oásis).
- **`$bid46` (Hospital)**: Edifício genérico. `attri` = 1.0 a 0.135 (fator de cura).
- **`$bid47` (Defensive Wall)**: Muralha dos Spartans.
- **`$bid48` (Big Hospital)**: Edifício único dos Spartans. `attri` = 1.0 a 0.135.
- **`$bid49` (Great Workshop)**: Movido do antigo `$bid42`. Dados preservados.
- **`$bid50` (Barricade)**: Muralha dos Vikings.

---

## ✅ FASE 2: ENGINE PHP

### `GameEngine/Building.php`
- **`procResType()`** (linhas 280-309): Cases 42-50 adicionados com constantes de nome (STONEWALL, MAKESHIFTWALL, COMMANDCENTER, WATERWORKS, HOSPITAL, DEFENSIVEWALL, BIGHOSPITAL, GREATWORKSHOP, BARRICADE).
- **`constructBuilding()`** (linha 444): Slot de muralha expandido: `in_array($tid, [31,32,33,42,43,47,50])` em vez de apenas `31,32,33`.
- **`canBuild()`** (linhas 531-551): Restrições de tribo para gid 42-50:
  - `case 42`: Egípcios (7)
  - `case 43`: Huns (6)
  - `case 44`: Huns, Main Building 5, sem Residence/Palace
  - `case 45`: Egípcios, Hero's Mansion 10
  - `case 46`: Genérico, Main Building 10, Academy 15, sem Big Hospital
  - `case 47`: Spartans (8)
  - `case 48`: Spartans/Vikings (8|9), Rally 10, Stable 20, sem Hospital
  - `case 49`: Great Workshop (movido de 42)
  - `case 50`: Vikings (9)

### `GameEngine/Technology.php`
- **`$unarray`**: Adicionados U51-U90.
- **`getTrainingList()`**: Arrays `$barracks`, `$stables`, `$workshop`, `$residence`, `$greatbarracks`, `$greatstables`, `$greatworkshop` com os IDs das novas unidades. Great buildings usam offset **+1000** (em vez do antigo +60).
- **`$train['unit'] -= 1000`**: Nas seções de greatbarracks, greatstables, greatworkshop.
- **`meetTRequirement()`**: Cases 52-89 adicionados para as tribos 6-9, seguindo o padrão: primeira unidade e settler sem pesquisa, demais com `$this->getTech($unit)`.
- **`procTrain()`**: Adicionados `t59/t60`, `t69/t70`, `t79/t80`, `t89/t90` ao bloqueio de chefe+colono.
- **`trainUnit()`**: 
  - `global $bid49` (era $bid42)
  - `getTypeLevel(49)` para Great Workshop (era 42)
  - `$bid49[...]` para cálculo de tempo
  - `getTypeLevel(44)` adicionado como alternativa para treino de chefes/colonos (Command Center)
  - `$unit + ($great ? 1000 : 0)` em vez de `+ 60`
  - Arrays `$footies`, `$calvary`, `$workshop`, `$special` com os novos IDs.
- **`meetRRequirement()`**: Cases 52-89 mapeados para os grupos de requisitos de construção.

### `GameEngine/Automation.php`
- **Linha 916**: Capacidade do mercador expandida para as 4 novas tribos (mesma lógica do Market.php).
- **Linha 1494-1495**: `if($train['unit'] > 1000 && $train['unit'] != 99)` e `$train['unit'] - 1000` (offset great buildings).

### `GameEngine/Market.php`
- **Linha 77**: `$this->maxcarry` expandido:
  - Huns (6): 500
  - Egípcios (7): 750
  - Espartanos (8): 500
  - Vikings (9): 750

### `GameEngine/Generator.php`
- **`procDistanceTime()`** (linhas 46-51): Velocidade do mercador por tribo:
  - Huns (6): 20 campos/h
  - Egípcios (7): 16 campos/h
  - Spartans (8): 14 campos/h
  - Vikings (9): 18 campos/h

### `GameEngine/Battle.php`
- **Linha 450** (`calculateBattle`): Fatores de muralha:
  - Huns (Makeshift Wall): 1.015
  - Egípcios (Stone Wall): 1.030
  - Spartans (Defensive Wall): 1.028
  - Vikings (Barricade): 1.022
- **Linha 1289** (`calculateBattleSim`): Mesma lógica.

### `GameEngine/AttackHandler.php`
- **`gatherAttackerForces()`** (linhas 207-212): `$unitTypes` com IDs das novas unidades:
  - catapult: 58,68,78,88
  - ram: 57,67,77,87
  - chief: 59,69,79,89
  - spy: 53,64,72,84
- **`generateBattleReportStrings()`** (linhas 967-968): `array_fill(1, 9, ...)` em vez de `array_fill(1, 5, ...)`.
- **Linha 995**: `for ($i = 1; $i <= 9; $i++)` em vez de `<= 5`.
- **Linha 1003-1004**: `array_fill(1, 9, 0)` para heróis.
- **Linha 1070**: `$hidden_defender_units = array_fill(0, 209, '?')` em vez de 125.
- **Linha 1071**: `$hidden_heroes_by_tribe = array_fill(0, 18, '?')`.

---

## ✅ FASE 3: CSS

### `gpack/travian_default/lang/en/compact.css`
- **Sprite sheets**: `v6_monsters2.gif` → `v6_huns2.gif`. Adicionados blocos para `v7_egyptians2.gif`, `v8_spartans2.gif`, `v9_vikings2.gif`.
- **`background-position`**: U61-U90 adicionados aos grupos de posição (0, -19px, -38px, ..., -171px).

---

## ✅ FASE 4: LINGUAGEM

### `GameEngine/Lang/en.php`
- **TRIBE6**: 'Huns', TRIBE7: 'Egyptians', TRIBE8: 'Spartans', TRIBE9: 'Vikings'.
- **U51-U90**: Nomes das unidades das 4 tribos.
- **Constantes de edifícios**: STONEWALL, MAKESHIFTWALL, COMMANDCENTER, WATERWORKS, HOSPITAL, DEFENSIVEWALL, BIGHOSPITAL, BARRICADE + suas _DESC.

---

## ✅ FASE 5: IMAGENS

### `img/u/`
- `51.gif` a `90.gif` — Ícones das unidades (criados pelo usuário).
- `v6_huns2.gif` a `v9_vikings2.gif` — Sprites das tribos (criados pelo usuário).

---

## ✅ FASE 6: TEMPLATES DE RELATÓRIO

### `Templates/Notice/1.tpl`
- Todos os índices do `$dataarray` recalculados para 9 tribos (deslocamento de +84 índices nas seções pós-reforços).

### `Templates/Notice/3.tpl`
- Mesma atualização de índices.

### `Templates/dorf3/1.tpl`
- **Linhas 61-63**: `$key > 1000` e `$key - 1000` (offset great buildings).

---

## ✅ FASE 7: BOT (Android)

### `assets/training_troop_costs.sql`
- Units 51-90 com stats e `slot1/slot2` corretos (19/29 infantaria, 20/30 cavalaria, 21/42 cerco, 25/26 chefe/colono).

### `assets/research_troop_costs.sql`
- Pesquisas 51-90. Primeira unidade de cada tribo e settler com custo 0.

### `assets/upgrade_troop_costs.sql`
- Tipos 51-88 × 20 níveis = 640 INSERTs (IDs 501-1140). Dados da planilha.

### `assets/building_level_costs.sql`
- Building_id 42-50 × 20 níveis. Muralhas com `prod = NULL`, demais com `prod = attri`.

### `assets/images/t51.gif` a `t90.gif`
- 40 imagens de unidades copiadas de `img/u/` com prefixo `t`.

### `lib/core/models/edificio_utils.dart`
- Edifícios 42-50 adicionados ao map `edificios`.
- Tropas 31-90 adicionadas ao map `tropas`.

---

## 📊 MAPA DE EDIFÍCIOS

| gid | Edifício | Tribo | Tipo | bid | attri |
|-----|----------|-------|------|-----|-------|
| 31 | City Wall | Romano | Muralha | `$bid31` | defesa |
| 32 | Earth Wall | Teutão | Muralha | `$bid32` | defesa |
| 33 | Palisade | Gaulês | Muralha | `$bid33` | defesa |
| **42** | **Stone Wall** | **Egípcio** | **Muralha** | **`$bid42`** | **defesa** |
| **43** | **Makeshift Wall** | **Hun** | **Muralha** | **`$bid43`** | **defesa** |
| **47** | **Defensive Wall** | **Espartano** | **Muralha** | **`$bid47`** | **defesa** |
| **50** | **Barricade** | **Viking** | **Muralha** | **`$bid50`** | **defesa** |
| 35 | Brewery | Teutão | Único | `$bid35` | +1% atk/level |
| 36 | Trapper | Gaulês | Único | `$bid36` | armadilhas |
| 41 | Horse Drinking Trough | Romano | Único | `$bid41` | -crop cav |
| **44** | **Command Center** | **Hun** | **Único** | **`$bid44`** | **slots expansão** |
| **45** | **Waterworks** | **Egípcio** | **Único** | **`$bid45`** | **bónus oásis** |
| **46** | **Hospital** | **Genérico** | **Único** | **`$bid46`** | **fator cura** |
| **48** | **Big Hospital** | **Espartano** | **Único** | **`$bid48`** | **fator cura** |
| 49 | Great Workshop | (movido) | Único | `$bid49` | velocidade |
| 34 | Stonemason | Capital | Especial | `$bid34` | defesa edif |
| 37 | Hero's Mansion | - | - | `$bid37` | oásis |

---

## 📊 MERCADOR

| Tribo | Capacidade | Velocidade (f/h) |
|-------|-----------|-----------------|
| Romanos (1) | 500 | 16 |
| Teutões (2) | 1000 | 12 |
| Gauleses (3) | 750 | 24 |
| **Huns (6)** | **500** | **20** |
| **Egípcios (7)** | **750** | **16** |
| **Espartanos (8)** | **500** | **14** |
| **Vikings (9)** | **750** | **18** |

---

## 📊 FATORES DE MURALHA

| Muralha | Tribo | Fator |
|---------|-------|-------|
| City Wall | Romanos | 1.030 |
| Earth Wall | Teutões | 1.020 |
| Palisade | Gauleses | 1.025 |
| Makeshift Wall | Huns | **1.015** |
| Stone Wall | Egípcios | **1.030** |
| Defensive Wall | Spartans | **1.028** |
| Barricade | Vikings | **1.022** |

---

## 🚧 PENDENTE

### Registro & Interface
- [x] `anmelden.php:97-110` — Radio buttons para Huns (6), Egípcios (7), Spartans (8), Vikings (9).
- [x] `warsim.php:193-196` — Adicionar as 4 novas tribos ao simulador.
- [x] `procurar por loops de tropas no codigo` < 50 -> for($i = 1; $i <= 50; $i++)
- [x] `procurar por loops de tribos no codigo` < 5 -> for($i = 1; $i <= 5; $i++)

### Rankings & Filtros
- [x] `Templates/Ranking/player_top10.tpl:9,81,137,192` — `tribe<=3` → `tribe<=9`.
- [x] `Templates/News/newsbox1.tpl:5` — `tribe<=3` → `tribe<=9`.
- [x] `Templates/Simulator` - att e deff das tribos novas.
- [x] `Templates/a2b` - templates para tropas 6-9
- [x] `Imagens das Muralhas Novas` - Inserir também em `Admin/Templates/editVillage.tpl, village.tpl`
- [x] `winner.php:65,91,116` — `tribe <= 3` → `tribe <= 9`.
- [x] `Templates/Build/22` - Templates de academia ajustados
- [x] `GameEngine/Ranking.php:227,229` — `tribe <= 5` → `tribe <= 9`.
- [x] `GameEngine/Ranking.php:369,413` — `tribe <= 3` → incluir tribos 6-9.
- [x] `Manual - Tropas` - Criar templates das tropas novas.
- [x] `Imagens Tropas U2` - Criar imagens grandes das tropas para manual.
- [x] `Manual - Edificios` - Criar templates dos edificios novos.
- [x] `Estatísticas` - Criar filtros para as novas tribos, como os já existentes (geral, ataque e defesa, não filtrar por tribos no top10)

### Edificios novos
- [x] `gid44 - Command Center` - Criar UI e permitir treinos.
- [x] `gid46 - Hospital` - Criar UI e permitir heals, implementar funcionalidade (feridos, cura).
- [ ] `gid48 - Big Hospital` - Criar UI (precisa verificar).
- [ ] `gid45 - Waterworks` - Criar UI e Aplicar efeitos de bonus. (ref: https://travian.fandom.com/wiki/Waterworks)


### Database
- [x] `var/db/struct.sql` — Adicionar colunas `u51`..`u90` nas tabelas `units`, `enforcement` e `tdata`.
- [x] Executar ALTER TABLE no servidor.

### Bot — imagens de edifícios
- [x] `assets/images/g42.gif` etc. para os novos edifícios.

---

## Arquivos Modificados (lista completa)

```
GameEngine/Data/unitdata.php
GameEngine/Data/hero_full.php
GameEngine/Data/resdata.php
GameEngine/Data/buidata.php
GameEngine/Data/hero_full.php
GameEngine/Building.php
GameEngine/Technology.php
GameEngine/Market.php
GameEngine/Generator.php
GameEngine/Battle.php
GameEngine/AttackHandler.php
GameEngine/Automation.php
GameEngine/Lang/en.php
GameEngine/Ranking.php
GameEngine/Database.php
gpack/travian_default/lang/en/compact.css
Templates/Notice/1.tpl
Templates/Notice/3.tpl
Templates/dorf3/1.tpl
Templates/Simulator/att_6.tpl ~ att_9.tpl
Templates/Simulator/def_6.tpl ~ def_9.tpl
Templates/Simulator/res_6.tpl ~ res_9.tpl
Templates/Simulator/res_a6.tpl ~ res_a9.tpl
Templates/Simulator/res_d6.tpl ~ res_d9.tpl
Templates/Build/avaliable.tpl
Templates/Build/42.tpl ~ 50.tpl (exceto 49)
Templates/Build/49.tpl (atualizado)
Templates/Build/49_train.tpl
Templates/Build/avaliable/stonewall.tpl
Templates/Build/avaliable/makeshiftwall.tpl
Templates/Build/avaliable/commandcenter.tpl
Templates/Build/avaliable/waterworks.tpl
Templates/Build/avaliable/hospital.tpl
Templates/Build/avaliable/defensivewall.tpl
Templates/Build/avaliable/bighospital.tpl
Templates/Build/avaliable/barricade.tpl
Templates/Build/avaliable/greatworkshop.tpl (atualizado)
Templates/Build/soon/stonewall.tpl ~ soon/barricade.tpl (8 templates)
Templates/Build/soon/greatworkshop.tpl (atualizado)
warsim.php
index.php
winner.php
var/db/struct.sql
PLAN-ADD-TRIBES.md

Bot (Flutter):
  assets/training_troop_costs.sql
  assets/research_troop_costs.sql
  assets/upgrade_troop_costs.sql
  assets/building_level_costs.sql
  assets/images/t51.gif-t90.gif
  lib/core/models/edificio_utils.dart
