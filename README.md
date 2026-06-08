# TravianF — v9.0.0 (fork personalizado)

Baseado no [TravianZ](https://github.com/shadowss/travianz) por Shadowss, este fork
adiciona melhorias significativas de performance, conteúdo e ferramentas de produção.

## Principais Melhorias

### 4 Novas Tribos Jogáveis
Hunos, Egípcios, Espartanos e Vikings — cada uma com 10 unidades únicas, herói,
muralha própria e edifício especial (Command Center, Waterworks, Hospital, Big Hospital).

### AttackHandler (classe dedicada)
Refatoração completa do sistema de batalha em uma classe OOP de 1604 linhas,
substituindo a lógica inline da Automation.php. Suporte a 9 tribos, evasão paga,
armadilhas, conquista e destruição de vilas.

### Automação em 4 Grupos com Cool-downs
- **Village_Units** (10s): movimentos, construções, pesquisas, treinos
- **Accounts_Alliances** (30s): clímbers, inativos, celebrações, bans
- **World_Maintenance** (300s): starvation, rotas, arquivamento
- **Game_Events** (900s): WW, artefatos, medalhas, ataques fake

Cada grupo com lock file independente e execução paralela segura.

### Movement Completion Sequencial
Processa movimentos um a um com optimistic locking (`UPDATE ... SET proc=1 WHERE proc=0`),
garantindo consistência sem race conditions.

### Scripts para Windows Task Scheduler
- `run_automation_continuously_t3.bat` — loop infinito a cada 10s
- `check_and_start_automation_t3.bat` — monitoramento e restart automático

### Starvation Otimizado
`starvationNew()` com `FORCE INDEX`, updates em batch de 500 registros,
processa apenas vilas sinalizadas com flag de starvation.

### Archive & Prune
Arquivamento automático de movimentos, ataques, relatórios e mensagens
com mais de 45 dias para tabelas `_archive`.

### Fake Attack Generator
Gera até 50 ataques falsos por ciclo para simular atividade no servidor.

### Medalhas Semanais
Categorias: ataque, defesa, climber, rank climber, robber. Streaks de
3/5/10x consecutivos e combos ATK+DEF.
- As medalhas top3 são adicionadas automaticamente no perfil do user.

### Banco de Dados Otimizado
- Schema estendido: colunas `u51`–`u90` em `units` e `enforcement`
- Novos índices estratégicos em `vdata`, `movement`, `users`, `hero`, `artefacts`, `ndata`, `bdata`
- Total de 1675 linhas em InnoDB utf8mb3

### Android Companion App
Bot em Flutter disponível em:
https://github.com/fernandomed93-byte/travian_bot_windows

## Requisitos Mínimos
- PHP 8.3.16+
- MySQL 8.0+ ou MariaDB 10.5+
- Apache 2.4 (recomendado Laragon no Windows)
- Windows Task Scheduler (para automação headless)

## Instalação

### Automática (recomendada)
1. Clone o repositório e aponte o domínio para a pasta raiz
2. Abra `http://seu-dominio/install` no navegador
3. Siga o assistente de instalação
4. Configure o Task Scheduler ou cron job para executar
   `check_and_start_automation_t3.bat` a cada 5 minutos

### Manual
1. Clone o repositório
2. Importe `var/db/struct.sql` no MySQL
3. Configure `GameEngine/config.php` (banco, domínio, timezone)
4. Aponte o domínio para a pasta raiz
5. Configure o Task Scheduler ou cron job para executar
   `check_and_start_automation_t3.bat` a cada 5 minutos

## Links
- **Repositório oficial:** https://github.com/fernandomed93-byte/TravianZ-T3.6---Reworked
- **Original TravianZ:** https://github.com/shadowss/travianz
- **Android Bot:** https://github.com/fernandomed93-byte/travian_bot_windows

## Créditos
- **Shadowss** — criador do TravianZ
- **iopietro, AL-Kateb, martinambrus** — desenvolvedores alumni
- **Vladyslav, phaze1G** — testes e design
- **fer10fer (Fernando)** — melhorias, novas tribos, refatoração e ferramentas de produção
