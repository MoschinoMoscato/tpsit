<?php

 use OTPHP\TOTP;
 use Endroid\QrCode\QrCode;
 use Endroid\QrCode\Writer\PngWriter;

 // Funzione per generare un secret casuale per il 2FA
 function generate_twofa_secret() 
 {
  $totp = TOTP::create();
  return $totp->getSecret();
 }

 // Funzione per costruire l'URI da usare per Google Authenticator
 function build_otpauth_uri($email, $secret) 
 {
  $totp = TOTP::create($secret);
  $totp->setLabel($email);
  $totp->setIssuer("TPSIT Mosca");

  return $totp->getProvisioningUri();
 }

 // Funzione per verificare se il codice inserito è corretto
 function verify_totp_code($secret, $code) 
 {
  $totp = TOTP::create($secret);
  return $totp->verify($code);
 }

 // Funzione per generare il QR code in formato immagine base64
 function generate_qrcode_data_uri($data) 
 {
  $qr_code = new QrCode($data);
  $writer = new PngWriter();

  $result = $writer->write($qr_code);

  return $result->getDataUri();
 }

?>