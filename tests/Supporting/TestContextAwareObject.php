<?php
namespace Packaged\Tests\Context\Supporting;

use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;
use Packaged\Context\WithContext;
use Packaged\Context\WithContextTrait;

class TestContextAwareObject implements ContextAware, WithContext
{
  use ContextAwareTrait;
  use WithContextTrait;

  public function bind(ContextAware $to)
  {
    return $this->_bindContext($to);
  }

  public function apply(ContextAware $to)
  {
    return $this->_applyContext($to);
  }
}
