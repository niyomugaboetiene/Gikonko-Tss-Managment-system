
<?php

$host = getenv("DB_HOST") ?: "";
$port = (int) (getenv("DB_PORT") ?: 0);
$database = getenv("DB_NAME") ?: "";
$username = getenv("DB_USER") ?: "";
$password = getenv("DB_PASSWORD");

if (
    $host === "" ||
    $port <= 0 ||
    $database === "" ||
    $username === "" ||
    $password === false
) {
    error_log("Database environment variables are missing.");
    die("Database configuration error.");
}

$conn = mysqli_init();

if (!$conn) {
    error_log("MySQLi initialization failed.");
    die("Database connection error.");
}

$caPath = __DIR__ . "/ca.pem";

if (!file_exists($caPath)) {
    error_log("Aiven CA certificate is missing.");
    die("Database SSL configuration error.");
}

mysqli_ssl_set($conn, null, null, $caPath, null, null);

if (!mysqli_real_connect(
    $conn,
    $host,
    $username,
    $password,
    $database,
    $port,
    null,
    MYSQLI_CLIENT_SSL
)) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Unable to connect to the database.");
}

mysqli_set_charset($conn, "utf8mb4");

?>
