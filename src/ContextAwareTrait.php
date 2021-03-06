<?php
namespace Packaged\Context;

trait ContextAwareTrait
{
  private $_context;

  /**
   * @return $this
   */
  public function clearContext()
  {
    $this->_context = null;
    return $this;
  }

  /**
   * @return bool
   */
  public function hasContext(): bool
  {
    return $this->_context !== null;
  }

  /**
   * Bind the current context to another context aware object
   *
   * @param ContextAware $to Object to apply the context to
   *
   * @return $this
   */
  protected function _bindContext(ContextAware $to)
  {
    $to->setContext($this->getContext());
    return $this;
  }

  /**
   * Bind context to a context aware object, and return it
   *
   * @param ContextAware $to
   *
   * @return ContextAware
   */
  protected function _applyContext(ContextAware $to)
  {
    $to->setContext($this->getContext());
    return $to;
  }

  /**
   * @return Context
   */
  public function getContext(): Context
  {
    return $this->_context;
  }

  /**
   * @param Context $context
   *
   * @return $this
   */
  public function setContext(Context $context)
  {
    $this->_context = $context;
    return $this;
  }

}
