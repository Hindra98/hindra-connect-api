<?php

namespace App\Core\Utils;

use Psr\Http\Message\ServerRequestInterface as Request;

class LogFormatter
{
  public static function format(Request $request, $user = null)
  {
    $serverParams = $request->getServerParams();
    $val = mb_split("/",$request->getServerParams()['REQUEST_URI']);
    $context = [
      'host' => $serverParams['HTTP_HOST'],
      'uri' => $serverParams['REQUEST_URI'],
      'platform' => $serverParams['HTTP_SEC_CH_UA_PLATFORM'] ?? "null",
      'user-agent' => $serverParams['HTTP_USER_AGENT'],
      'request-method' => $serverParams['REQUEST_METHOD'],
      'ip' => $serverParams['REMOTE_ADDR'],
      'user' => $user,
      'args' => $val[count($val)-1],
      'payload' => count($request->getQueryParams()) > 0 ? $request->getQueryParams() : $request->getParsedBody(),
    ];
    return $context;
  }
  public function getName(Request $request) {
    $method = ['GET'=>"Recuperation des donnees de ", 'POST'=>"Creation de ", 'PUT'=>"Modification de ", 'DELETE'=>"Suppression de "];
    $val = mb_split("/",$request->getServerParams()['REQUEST_URI']);
    $name = $method[$request->getMethod()].$val[3];
    return $name;
  }
}
