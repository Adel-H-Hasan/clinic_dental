@echo off
cd /d "%~dp0"
echo The isolated Demo database launcher has been retired to avoid creating new demo databases.
echo Use Start-TAQWEEM.cmd; it reads your existing .env and targets the configured taqweem database.
call Start-TAQWEEM.cmd
