<?php
namespace ClawCorpLib\Enums;

enum JwtStates : string
{
  case new = 'new';
  case expired = 'expired';
  case issued = 'issued';
  case revoked = 'revoked';
  case confirm = 'confirm';
  case init = 'init';
  case error = 'error';
}
