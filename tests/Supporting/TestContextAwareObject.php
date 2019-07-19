<?php
namespace Packaged\Tests\Context\Supporting;

use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;

class TestContextAwareObject implements ContextAware
{
  use ContextAwareTrait;

  public function bind(ContextAware $to)
  {
    return $this->_bindContext($to);
  }

  public function with(ContextAware $to)
  {
    return $this->_withContext($to);
  }
}
