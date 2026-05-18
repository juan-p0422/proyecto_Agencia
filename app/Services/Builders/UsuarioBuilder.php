<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  2.0.17  |
    |              on 2026-04-14 01:48:36              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
 namespace App\Services\Builders; use App\Models\Usuario; class UsuarioBuilder 
 { private $data = array(); public function setNombre($pliL1) { $this->data["\116\x6f\155\x62\x72\145"] = $pliL1; return $this; }
  public function setCorreo($KmxR6) { $this->data["\x43\157\162\x72\145\157"] = $KmxR6; return $this; } 
  public function setPassword($g9ipE) { $this->data["\x50\x61\x73\x73\x77\x6f\x72\x64"] = $g9ipE; return $this; } 
  public function build() { return Usuario::create($this->data); } }
