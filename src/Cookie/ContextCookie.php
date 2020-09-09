<?php
namespace Packaged\Context\Cookie;

use Packaged\Context\ContextAware;
use Packaged\Context\ContextAwareTrait;
use Packaged\Context\WithContext;
use Packaged\Context\WithContextTrait;

abstract class ContextCookie implements ContextAware, WithContext
{
  use ContextAwareTrait;
  use WithContextTrait;

  /**
   * @param ContextAware $ctx
   * @param bool         $checkQueued
   *
   * @return static
   */
  public static function loaded(ContextAware $ctx, bool $checkQueued = true)
  {
    return static::withContext($ctx)->read($checkQueued);
  }

  /**
   * @var string|null
   */
  private $_rawValue;

  abstract public function name(): string;

  abstract public function ttl(): int;

  /**
   * @param bool $checkQueued
   *
   * @return bool
   */
  public function exists(bool $checkQueued = true)
  {
    return $this->getContext()->cookies()->has($this->name(), $checkQueued);
  }

  /**
   * @param bool $checkQueued
   *
   * @return $this
   */
  public function read(bool $checkQueued = true)
  {
    return $this->_setRawValue($this->getContext()->cookies()->read($this->name(), $checkQueued));
  }

  protected function _getRawValue(): ?string
  {
    return $this->_rawValue;
  }

  /**
   * @param string $value
   *
   * @return $this
   */
  protected function _setRawValue(string $value)
  {
    $this->_rawValue = $value;
    return $this;
  }

  /**
   * @return $this
   */
  public function store()
  {
    $this->getContext()->cookies()->store($this->name(), $this->_getRawValue(), $this->ttl());
    return $this;
  }

  /**
   * @return $this
   */
  public function delete()
  {
    $this->getContext()->cookies()->delete($this->name());
    return $this;
  }
}
