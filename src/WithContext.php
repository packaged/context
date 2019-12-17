<?php
namespace Packaged\Context;

interface WithContext
{
  public static function withContext(ContextAware $context);
}
