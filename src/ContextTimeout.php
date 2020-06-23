<?php
namespace Packaged\Context;

use Packaged\Context\Events\ContextDoneEvent;
use Packaged\Context\Events\ContextTimeoutEvent;

class ContextTimeout
{
  private $_ctx;
  private $_duration;
  private $_expiry;

  public function __construct(Context $context, $duration = 30)
  {
    $this->_ctx = $context;
    $this->_ctx->events()->listen(ContextDoneEvent::TYPE, [$this, 'onComplete']);
    $this->_duration = $duration;
    $this->_expiry = time() + $duration;
    register_tick_function([$this, 'onTick']);
  }

  protected function _tick()
  {
    if($this->_expiry <= time())
    {
      $this->_ctx->events()->trigger(new ContextTimeoutEvent($this->_duration, time()));
      $this->_ctx->done(ContextDoneEvent::RSN_TIMEOUT);
      $this->_shutdown();
    }
  }

  public function onTick()
  {
    $this->_tick();
  }

  public function onComplete()
  {
    $this->_shutdown();;
  }

  protected function _shutdown()
  {
    unregister_tick_function([$this, 'tick']);
  }

  public function __destruct()
  {
    $this->_shutdown();
  }

}
