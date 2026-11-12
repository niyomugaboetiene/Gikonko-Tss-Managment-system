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
    $password === false ||
    $password === ""
) {
    error_log("Database environment variables are missing or incomplete.");
    http_response_code(500);
    exit("Database configuration error.");
}

$conn = mysqli_init();

if (!$conn) {
    error_log("MySQLi initialization failed.");
    http_response_code(500);
    exit("Database connection error.");
}

$caPath = __DIR__ . "/ca.pem";

if (!is_file($caPath)) {
    error_log("Aiven CA certificate not found: " . $caPath);
    http_response_code(500);
    exit("Database SSL configuration error.");
}

mysqli_ssl_set($conn, null, null, $caPath, null, null);

// Connect to Aiven MySQL
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
    http_response_code(500);
    exit("Unable to connect to the database.");
}

if (!mysqli_set_charset($conn, "utf8mb4")) {
    error_log("Unable to set database character encoding: " . mysqli_error($conn));
    http_response_code(500);
    exit("Database configuration error.");
}
