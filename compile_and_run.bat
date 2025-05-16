@echo off
mkdir bin 2>nul
echo Compilation...
javac -cp "lib/*" -d bin src/main/java/com/businesscare/*.java
if %errorlevel% neq 0 (
    echo Erreur de compilation
    pause
    exit /b %errorlevel%
)
echo Execution...
java -cp "bin;lib/*" main.java.com.businesscare.Main
pause
