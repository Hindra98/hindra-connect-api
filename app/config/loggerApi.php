<?php

namespace App\Config;

use App\Core\Utils\LogFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoggerApi
{
  private $log;
  private $logFormatter;

  public function __construct()
  {
    $this->log = new Logger('api-hindra-connect');
    $this->logFormatter = new LogFormatter();
  }
  public function getDebug(Request $request, array $options = [])
  {
    return $this->getResult($request, $options);
  }
  public function getInfo(Request $request, array $options = [])
  {
    return $this->getResult($request, $options, Level::Info);
  }
  public function getError(Request $request, array $options = [])
  {
    return $this->getResult($request, $options, Level::Error);
  }
  private function getResult(Request $request, array $options = [], Level $level = Level::Debug)
  {
    $message = (count($options) > 0 && $options[0] != "") ? $options[0] : $this->logFormatter->getName($request);
    $context = $this->logFormatter::format($request, $options[1] ?? null);
    $this->log->pushHandler(new StreamHandler(__DIR__ . '/log/api_log-' . strtolower($level->getName()) . '.log', $level));
    if ($level == Level::Error) {
      $this->getInfo($request, $options);
      return $this->log->error($message, $context);
    }
    if ($level == Level::Info) {
      $this->getDebug($request, $options);
      return $this->log->error($message, $context);
    }
    return $this->log->debug($message, $context);
  }
}
