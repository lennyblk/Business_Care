@echo off
mkdir lib 2>nul
cd lib

echo Téléchargement des dépendances...
curl -LO https://repo1.maven.org/maven2/com/itextpdf/itext7-core/7.2.3/itext7-core-7.2.3.jar
curl -LO https://repo1.maven.org/maven2/org/jfree/jfreechart/1.5.3/jfreechart-1.5.3.jar
curl -LO https://repo1.maven.org/maven2/mysql/mysql-connector-java/8.0.27/mysql-connector-java-8.0.27.jar

echo Dépendances téléchargées avec succès!
pause
