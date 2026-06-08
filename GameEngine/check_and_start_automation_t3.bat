@echo off
REM --- CONFIGURAÇÕES ---
SET RUNNER_SCRIPT_NAME=run_automation_continuously_t3.bat
SET RUNNER_SCRIPT_PATH="C:\laragon\www\t3\GameEngine\run_automation_continuously_t3.bat"
SET PROCESS_TITLE="Automation Fast Runner - Servidor T3"

SET LOG_DIR="C:\laragon\tmp"
SET LOG_PREFIX=t3
SET TODAY_DATE=%DATE:~6,4%-%DATE:~3,2%-%DATE:~0,2%
SET LOG_FILE=%LOG_DIR%\%LOG_PREFIX%_%TODAY_DATE%.log
REM --- FIM DAS CONFIGURAÇÕES ---

REM --- LIMPEZA DE LOGS ANTIGOS ---
REM Apaga arquivos de log com o mesmo prefixo e com mais de 7 dias.
echo [ %DATE% %TIME% ] Verificando e limpando logs com mais de 7 dias... >> %LOG_FILE%
forfiles /P %LOG_DIR% /M %LOG_PREFIX%_*.log /D -7 /C "cmd /c del @path"
echo [ %DATE% %TIME% ] Limpeza de logs antigos concluida. >> %LOG_FILE%

echo. >> %LOG_FILE%
echo [ %DATE% %TIME% ] Verificando se Automation_T3 esta executando... >> %LOG_FILE%

REM Verifica se um processo cmd.exe está executando o script runner.
wmic process where "name='cmd.exe'" get commandline | findstr /I /C:"%RUNNER_SCRIPT_NAME%" > nul

REM Se %ERRORLEVEL% for diferente de 0, o processo não foi encontrado.
IF %ERRORLEVEL% NEQ 0 (
    echo [ %DATE% %TIME% ] Script Runner nao encontrado. Iniciando %RUNNER_SCRIPT_PATH%... >> %LOG_FILE%
    REM Inicia o Script Runner em uma nova janela.
    start %PROCESS_TITLE% %RUNNER_SCRIPT_PATH%
    echo [ %DATE% %TIME% ] Script Runner iniciado. >> %LOG_FILE%
) ELSE (
    echo [ %DATE% %TIME% ] Script Runner ja esta em execucao. Nada a fazer. >> %LOG_FILE%
)