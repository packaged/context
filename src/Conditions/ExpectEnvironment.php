<?php
namespace Packaged\Context\Conditions;

use Packaged\Context\Condition;
use Packaged\Context\Context;

class ExpectEnvironment implements Condition
{
  protected $_environment;

  public function __construct($environment)
  {
    $this->_environment = $environment;
  }

  public static function phpunit()
  {
    return new static(Context::ENV_PHPUNIT);
  }

  public static function local()
  {
    return new static(Context::ENV_LOCAL);
  }

  public static function dev()
  {
    return new static(Context::ENV_DEV);
  }

  public static function qa()
  {
    return new static(Context::ENV_QA);
  }

  public static function uat()
  {
    return new static(Context::ENV_UAT);
  }

  public static function stage()
  {
    return new static(Context::ENV_STAGE);
  }

  public static function prod()
  {
    return new static(Context::ENV_PROD);
  }

  public function isSatisfied(Context $ctx)
  {
    return $ctx->getEnvironment() == $this->_environment;
  }
}
