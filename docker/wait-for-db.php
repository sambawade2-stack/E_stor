<?php

/*
 * Attend que la base accepte les connexions.
 *
 * On teste avec PDO, et non avec le client mysqladmin : le paquet
 * « mysql-client » d'Alpine fournit en réalité le client MariaDB, qui
 * refuse le certificat auto-signé que MySQL 8.4 présente par défaut. La
 * sonde échouait donc en boucle alors que la base était parfaitement
 * saine. PDO est de toute façon la bonne référence — c'est exactement ce
 * dont l'application se sert.
 */

$host = getenv('DB_HOST') ?: 'mysql';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: '';
$username = getenv('DB_USERNAME') ?: '';
$password = getenv('DB_PASSWORD') ?: '';

// Large par défaut : sur une petite machine, la toute première
// initialisation de MySQL (création du compte et de la base) peut demander
// plusieurs minutes. Trop court, le conteneur repart en boucle sans que
// rien ne soit réellement cassé.
$attempts = (int) (getenv('DB_WAIT_ATTEMPTS') ?: 90);
$delay = 2;

for ($i = 1; $i <= $attempts; $i++) {
    try {
        new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $database),
            $username,
            $password,
            [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        exit(0);
    } catch (Throwable $e) {
        if ($i === $attempts) {
            fwrite(STDERR, sprintf(
                "ERREUR : base %s:%s injoignable après %d tentatives.\n  %s\n",
                $host,
                $port,
                $attempts,
                $e->getMessage(),
            ));

            exit(1);
        }

        sleep($delay);
    }
}
