@echo off
mkdir bin 2>nul

echo Compilation du projet...
javac -cp "lib/*" -d bin src/main/java/com/businesscare/*.java

if errorlevel 1 (
    echo Erreur de compilation!
    pause
    exit /b 1
)

echo Exécution du programme...
java -cp "bin;lib/*" main.java.com.businesscare.Main
pause
