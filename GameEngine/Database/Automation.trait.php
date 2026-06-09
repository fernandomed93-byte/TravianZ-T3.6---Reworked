<?php

trait DBAutomation {

    function updateAutomationTime($time, $hasMovements) {
        list($time) = $this->escape_input($time);
        $time = (int)$time;

        $q1 = "SELECT lastautomationtime, time_offset FROM ".TB_PREFIX."config";
        $result1 = mysqli_query($this->dblink, $q1);

        if($result1) {
            $row = mysqli_fetch_assoc($result1);
            $lastTime = (int)$row['lastautomationtime'];
            $currentOffset = (int)$row['time_offset'];

            // 1. Primeira execução no servidor
            if($lastTime == 0) {
                $q = "UPDATE " . TB_PREFIX . "config SET lastautomationtime = " . $time;
                mysqli_query($this->dblink, $q);
            } 
            // 2. Execuções subsequentes
            else {
                $elapsed = $time - $lastTime;
                $newOffset = $currentOffset;
                $nextStarvation = 0; // Variável auxiliar

                //Grande parada no servidor > 6h
                if($elapsed > 21600) {
                    // Adicionamos todo o tempo perdido ao offset global
                    $newOffset += $elapsed;
                    $nextStarvation = time() + 3600;
                } 
                // Regra A: Se passou mais de 30min (falha no servidor/script travado), impede starvation por 1h
                elseif($elapsed > 1800) {
                    $nextStarvation = time() + 3600;
                } 
                // Regra B: Se não há movimentos (prevenção para queda de energia/bot parado)
                elseif(!$hasMovements) {
                    $nextStarvation = time() + 1800;
                }

                // Monta a query de atualização
                if($nextStarvation > 0) {

                    // IMPORTANTE: Atualiza o ponto de partida de todas as aldeias para "agora"
                    // Isso evita que elas tentem "recuperar" a fome dos dias que o servidor ficou off
                    $qReset = "UPDATE " . TB_PREFIX . "vdata 
                        SET starvupdate = " . $time . " 
                        WHERE starvupdate > 0";   
                    mysqli_query($this->dblink, $qReset);


                    // Atualiza tanto o último tempo quanto o próximo ponto de starvation
                    $q = "UPDATE " . TB_PREFIX . "config SET 
                        lastautomationtime = " . $time . ", 
                        time_offset = " . (int)$newOffset . ",
                        nextStarvationUpdate = " . (int)$nextStarvation;

                if (!is_array($this->automationConfigCache)) {
                    $this->automationConfigCache = [];
                }
                $this->automationConfigCache['nextStarvationUpdate'] = (int)$nextStarvation;
                $this->automationConfigCache['time_offset'] = (int)$newOffset;

                } else {
                    // Apenas atualiza o tempo da última execução normal
                    $q = "UPDATE " . TB_PREFIX . "config SET lastautomationtime = " . $time;
                }

                mysqli_query($this->dblink, $q);

            }
        }
    }

    public function needRunStarvation() {
        $time = time();

        // Implementação de Cache Simples: se já buscamos neste ciclo, não busca de novo
        if ($this->automationConfigCache === null) {
            $q1 = "SELECT nextStarvationUpdate FROM ".TB_PREFIX."config";
            $result1 = mysqli_query($this->dblink, $q1);
            if($result1) {
                $this->automationConfigCache = mysqli_fetch_assoc($result1);
            }
        }

        $nextTime = (int)($this->automationConfigCache['nextStarvationUpdate'] ?? 0);

        // 1. Se for 0 (desativado) ou se o tempo de proteção já passou
        if($nextTime == 0 || $nextTime < $time) {
            return true;
        } 
        
        // 2. Proteção ativa: o tempo atual ainda não alcançou o próximo ponto de liberação
        return false;
    }

    public function getGameTime() {
        // Tenta usar o cache que criamos no needRunStarvation para evitar query extra
        if ($this->automationConfigCache === null) {
            $q = "SELECT * FROM ".TB_PREFIX."config LIMIT 1";
            $result = mysqli_query($this->dblink, $q);
            $this->automationConfigCache = mysqli_fetch_assoc($result);
        }
        
        $offset = (int)($this->automationConfigCache['time_offset'] ?? 0);
        return time() - $offset;
    }

    public function getConfigs() {
        // Se o cache já estiver preenchido, retorna ele sem consultar o banco
        if ($this->configCache !== null) {
            return $this->configCache;
        }

        $q = "SELECT * FROM " . TB_PREFIX . "config";
        $result = mysqli_query($this->dblink, $q);
        
        if ($result) {
            $this->configCache = mysqli_fetch_assoc($result);
            return $this->configCache;
        }

        return []; // Retorna array vazio se falhar
    }

}
