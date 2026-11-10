
<?php

// Aiven login credentials
$config = parse_ini_file(__DIR__ . "/.env", false, INI_SCANNER_RAW);

if ($config === false) {
    error_log("Database configuration file could not be loaded.");
    die("Database configuration error.");
}

$host = $config["DB_HOST"] ?? "";
$port = (int) ($config["DB_PORT"] ?? 0);
$database = $config["DB_NAME"] ?? "";
$username = $config["DB_USER"] ?? "";
$password = $config["DB_PASSWORD"] ?? "";
$caPath = $config["DB_SSL_CA"] ?? "";

$conn = mysqli_init();

if (!$conn) {
    error_log("MySQLi initialization failed.");
    die("Database connection error.");
}

// configure SSL
if (!empty($caPath)) {
    mysqli_ssl_set($conn, null, null, $caPath, null, null);
}

$flags = MYSQLI_CLIENT_SSL;

if (!mysqli_real_connect(
    $conn,
    $host,
    $username,
    $password,
    $database,
    $port,
    null,
    $flags
)) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Unable to connect to the database.");
}

?>
