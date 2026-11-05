@echo off
"C:\xampp\mysql\bin\mysql.exe" -u root -pcarol11071107 --default-character-set=utf8mb4 apipragmatic < "C:\xampp\htdocs\games\pragmatic\database e install\apipragmatic_fixed.sql"
echo Finished with code %ERRORLEVEL%
