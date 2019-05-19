<?php
namespace Packaged\Context;

interface ContextAware
{
  public function setContext(Context $context);

  public function getContext(): Context;

  public function hasContext(): bool;
}
