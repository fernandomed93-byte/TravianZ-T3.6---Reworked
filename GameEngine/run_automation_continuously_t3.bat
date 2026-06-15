@echo off
REM --- CONFIGURAÇÕES ---
SET PHP_EXE="C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe"
SET PHP_SCRIPT="C:\laragon\www\t3\GameEngine\Automation.php"
SET INTERVAL=2

SET LOG_DIR="C:\laragon\tmp"
SET LOG_PREFIX=t3

REM Gera o nome do arquivo de log uma vez para as mensagens iniciais
SET TODAY_DATE=%DATE:~6,4%-%DATE:~3,2%-%DATE:~0,2%
SET LOG_FILE=%LOG_DIR%\%LOG_PREFIX%_%TODAY_DATE%.log

echo [ %DATE% %TIME% ] ================================= >> %LOG_FILE%
echo [ %DATE% %TIME% ] Iniciando loop de execucao continua (Nao-Interativo). >> %LOG_FILE% 
echo [ %DATE% %TIME% ] ================================= >> %LOG_FILE%

:loop
REM --- ATUALIZA O ARQUIVO DE LOG PARA O DIA ATUAL DENTRO DO LOOP ---
REM Isso garante que se o script passar da meia-noite, ele criará um novo arquivo de log.
SET TODAY_DATE=%DATE:~6,4%-%DATE:~3,2%-%DATE:~0,2%
SET LOG_FILE=%LOG_DIR%\%LOG_PREFIX%_%TODAY_DATE%.log

echo [ %DATE% %TIME% ] Executando %PHP_SCRIPT%... >> %LOG_FILE%

REM Executa o PHP, redirecionando a saída e os erros para o log.
start "" /B /WAIT /BelowNormal %PHP_EXE% %PHP_SCRIPT% >> %LOG_FILE% 2>>&1

echo [ %DATE% %TIME% ] Execucao finalizada. Aguardando %INTERVAL% segundos... >> %LOG_FILE%
echo. >> %LOG_FILE%
echo. >> %LOG_FILE%

REM Espera pelo intervalo definido.
timeout /t %INTERVAL% /nobreak > nul

REM Volta para o início do loop
goto loop

REM (Esta parte normalmente não será alcançada)
echo [ %DATE% %TIME% ] Loop interrompido. >> %LOG_FILE%