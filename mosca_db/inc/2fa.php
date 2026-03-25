<?php

 use OTPHP\TOTP;

 // Funzione per generare un secret casuale per il 2FA
 function generate_twofa_secret() 
 {
  $totp = TOTP::create();
  return $totp->getSecret();
 }

 // Funzione per costruire l'URI da usare per il QR code
 function build_otpauth_uri($email, $secret) 
 {
  $totp = TOTP::create($secret);
  $totp->setLabel($email);
  $totp->setIssuer("TPSIT");

  return $totp->getProvisioningUri();
 }

 // Funzione per verificare se il codice inserito è corretto
 function verify_totp_code($secret, $code) 
 {
  $totp = TOTP::create($secret);
  return $totp->verify($code);
 }

?>