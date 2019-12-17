<?php
namespace Packaged\Context;

trait WithContextTrait
{
  public static function withContext(ContextAware $aware, ...$args)
  {
    $obj = new static(...$args);
    if($obj instanceof ContextAware)
    {
      $obj->setContext($aware->getContext());
    }
    return $obj;
  }
}
