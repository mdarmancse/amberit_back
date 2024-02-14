<?php

//$serverADDR = $_SERVER['SERVER_ADDR'];
//
//if ($serverADDR == '::1') {
////Local DB
//    $conn = new mysqli("localhost", "root", '', "tsports_db");
//} else {
//
////Production DB (GCP)
////$conn = new mysqli("34.142.181.225", "root", '97q8P2xjWLxtPY9nd!VEjmw2#Dj5^29E', "tsports_db");
//
//
////Production DB (Local)
//    $conn = new mysqli("34.124.207.130", "root", 'QPqg0&.HT>[eEu[J', "tsports_db");
////    $conn = new mysqli("10.187.96.8", "root", 'M3JkEzYanDxkRY2', "tsports_db");
//}
//Production
$conn = new mysqli("10.187.96.8", "root", 'M3JkEzYanDxkRY2', "tsports_db");
//$conn = new mysqli("35.240.218.135", "root", 'M3JkEzYanDxkRY2', "tsports_db");
//Staging
//$conn = new mysqli("34.124.207.130", "root", 'QPqg0&.HT>[eEu[J', "tsports_db");

mysqli_set_charset($conn, 'utf8');

// Check connection
if ($conn->connect_errno) {
    echo "Failed to connect to MySQL: " . $conn->connect_error;
    exit();
}

// Google bigquery configurations----------------
use Google\Cloud\BigQuery\BigQueryClient;

$cloud_bucketName = 'bucket-bigquery-toffee';

$privateKeyFileContent = '{
    "type": "service_account",
    "project_id": "toffee-261507",
    "private_key_id": "7afcbc7ef7fad53f969f6b65aba6d63903046a63",
    "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDWUwq/zqc1J1/T\n++a1n56ZMjmuV11yf91lIWwnfRQr9Zv4Hqw0fzwTjGYi2iiC+eMcIODwb1xT0cLG\n5GdYMUOr2UPW/5xejcIttqb5VZRuaR4LVA7OPBjIFkYo7DXv9wGQizIXJpifZDbe\nLpm8yiJ6mNuwR/dLK3QQjCZkW96CmYdfBcT5sMhvnqREwrzHsEiQnj+XiH1v88Cw\nepj6lsQ3ob6scbEm1AS9fxOesmT8YuNQxgAjN5K10XgPXyMpfb9X2Wmm9FFPdO68\ntnKU5KF39BJ7mElXnWH9RLggM396b+gqnQZcobKN610lei0+ToDuS+q+OJI8Ab65\nhxWmznrRAgMBAAECggEASRjpgaGpxac4N8SiWy+ll/pZUezaIkMZ73QGvzEZwCR0\nnZtYgE8k3kX0T864InO30dAk59wTUUMpe0xLMvkaa3IegSWM33LZ112EdWWKyl1v\nsc1pf01f5l4yb6Kggsdr8TCIVP0E8NftHromFQ2b1NRtmSHyZeDcJQinz3LF4SR7\n9tF6K6np28lBRoGSaBHdlP8hbX7t1oNP/h49DBgYxnWsGU/pTDj8vBHxFJgt8Qxe\nxnFKGGcsZYxBjMGQ8X1wPqW8LuAvnDr50i5wNg4MdKcD+D9lSbUU96e0StjKPf8r\ntBjz6wbNdL4Osdb2UuUErssGdopCiXBfIkFI/NzsQQKBgQDygid59d/lpFNS8gFu\nu4Sa8G6Lkx1SRbDG5bJSniEqJZ1qsYHdXVD/S6t0F3Mp0uu1dN7EqOVxi57hI39L\niDTkKlvMCiUTCodgdI2qK3UGohZZ7JwEqC9tPvcT7nN/KvqTDgNeYV/Yzk+ybG5h\noUc5m8mD+N3lEZwUbAU2LjPtmwKBgQDiP3xtIdJ4z3kp7WKlWOdC5U8mCYuoQyug\n5kpBHKEo1qloJjq/H8vaI8cC9UYqYWwAv1DHL7k+hbkyZbMn28KDoQ2Gp0ve1k6b\n3/qTlZ5870xOGUztK7zExPA6+s74K279qgQIU0cRUv4IXVs7d1USr5qHvOqbWRev\nlkCww3w2AwKBgAZDwHtRE7id9x0UbV8L7xAFmAV5Bm/ipv9sXZ/uv9KT6C4iacVr\nLLV7ofE8zdfNwBMO8tZHuq2lOrR8M0SjPyKJyZdKx8xnIDooqKQ1vS8vrr4h86HX\nKmp7Duzv4wHs/U4hNYsRnuU95ycnz+4ruWhOkvUaz7ikLNEGPVg591NzAoGBAMt7\n6+2/UKwFdeUGswUhi4V39hKw3SGtoAyrcrdTB4NSZbTdRVLmRxLfwLrhXIT7cAbQ\nCtfLDWG1JZa1L0e2+CAo9qBX0P4PY04AST75fzzAgLFxQeXBw5p5wdJaB1Hexolw\nowXV2II1UXK2fDpknmga9fIFMEAeBhaPHYmdmBZ/AoGBANJsHGe4hx/bpAlNa/z7\n15P+/7XwjiL06KUmXsyAPeK+VKK5JUUPSSfY6M7bBidhOdxmFMI20kcZTd6DRk0v\nC1BTBrmalldEflRmCtWhc+NJ45tbKsjm4F+SOpACSxKbu3ctOrBGeGM8MFZTC+sM\neXW+9Aygo5PJ2FkCqXGKJUHx\n-----END PRIVATE KEY-----\n",
    "client_email": "bucket-bigquery-toffee@toffee-261507.iam.gserviceaccount.com",
    "client_id": "108782197810318523325",
    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
    "token_uri": "https://oauth2.googleapis.com/token",
    "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
    "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/bucket-bigquery-toffee%40toffee-261507.iam.gserviceaccount.com"
  }';

try {
    $bigQuery = new BigQueryClient([
        'keyFile' => json_decode($privateKeyFileContent, true)
    ]);
} catch (Exception $e) {
    // maybe invalid private key ?
    print $e;
    return;
}
