<?php

require '../../vendor/autoload.php';

// Google bigquery configurations----------------
use Google\Cloud\BigQuery\BigQueryClient;


$privateKeyFileContent = '{
  "type": "service_account",
  "project_id": "t-sports-361206",
  "private_key_id": "0da4678a534b11000d2094e832875c486374c604",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCvFIs2bpEMQpB1\nQ8QubnfdA4XK8VGpoPg0joDekJES1FfPNZCHIaPqOnYVrduya7ugtWngBYiOFMGD\nGKKlVp7JamopJmg4Sy+r9Bt/PPkOMVsemPEUGQ0Dx2C75+WKuRGT2zsR8DGyddo8\nEpyZJsok61eKTtta7l7AzGIzEiYHxSYK9v0csMEYxzcvZq9j2Bg2Azpr6m46ZJMa\nXUW1lwE4Vrpsv0hPDzgnetE8tT3GAxMW3/3MAlnwiRfd6wI81cxhEU7r2kVuNDhq\nbRyTrm7qdv7E3Q9NzdQdHZuLXrd/xO7SxBuRwgXPJFG4mb7ND/q2JOAaQ+iitMgc\nAGe9L1rVAgMBAAECggEAB59UGURSjc9XFTfkUmYuU0G8QTPk64btz3H109rE6iYz\nBXsuRefVyFSPwA1f74Jnuo0zZRSwl4j5TNEVga/F/Xxjz1NT/cFuO6UGznET4cWN\n00Ty1/5oNeYoBL5JnNLGXGLSzUrhCwU9AVkJFtTcv6M8XjHlNG4E042CZ/lODQ/6\nkN3Ns6I0L4ZlVx54cub1cRveV+ziuzCqoCYSMfTVtqhhU41TsHqAfmUNT6Sil6GK\nahrJ5xsfsbyQcSI8WG29h5/Vtdqeg82OdTFFf4ILyn2mB3NNtfF0BljvGI3BCteX\nEfLBzIQZppwJu5+kCRRWhxWh1e+8sVCuKCYdkmm10QKBgQDaShEZtkea5GLjmamR\naYTpsyS8HWUJq51qQxnCoPRotKf/EH7h4A9dWlUmpvmQg4xpWMv0Rl+CY/MZqQRO\nMoZ5+d2KherFLJA+v+wUMYXXsjmzBlXgxQ3a/lruSAbQFJ+PzmNFpgeRCfXnOYgo\njWrjeaw5kU3e5Ch6SROnjVZOBQKBgQDNU4nOAcX175zL2zNjKkRvtfXkipHOJ21X\nd9lRPMh8+GcczgcuN7afE/qPC4sSMhmG/AkioZrI79zhRqC4x+KginmnOLVZrs29\ncKhQYgUfo5j3wV6Lqom9KKIz0zn5Fc7own4nEpCQNNicXg/vaasuqSINpGMbNopg\notTo35aikQKBgALzeWO1mSY47DVTgH1cxdU6/MYmR5Vn4orrOU/uSR+ZQaQrHuwC\n0XJbpEcPftQ7UwyhjLBSuzvSChlQtaQw/qxrellDEjd0MMcIZTKosLyHxkvrTAHr\n6BIL2kLaam0pujaBfcoVQojtb4uetX6G2ukUXgWxNzJKN1nf3wom2QHZAoGAKCPI\nsOlP1gB92qyDo0NEFcKwy3j7gB3dFlhrt6H2X9f/8HWmmbZykS7KsOREz83Th7wF\n3StSoy2hNLkl+nm5KEFp/vLrIRk3R22aufwvnDvTT7wRy6QPQVeMPi1xC+zjgkVj\nfCf48vgh0I/i9Z7mxDx0V85nQY6sBrBOhygOvsECgYEAg4SkUb8zugBpnZtVDrHv\n94Kh7c2a9TkIGqIGhM8xhcgUuw91b9YP9pYiJjuajLF4GIVWU2ThhpuS4wTF6yRO\nurOouTkVmQhGQhdWtSBjajuSW+EQPO9aqN2XHSKBVZGp9uNDkIsZ3ZKRpQ3+OM7n\nZ4D5MP53Zkk6LHCLAk5kXfM=\n-----END PRIVATE KEY-----\n",
  "client_email": "bigquery-admin@t-sports-361206.iam.gserviceaccount.com",
  "client_id": "117367093674952241206",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/bigquery-admin%40t-sports-361206.iam.gserviceaccount.com"
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
