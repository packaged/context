<?php
namespace Packaged\Context;

interface Condition
{
  public function isSatisfied(Context $ctx);
}
