<?php
namespace Packaged\Context\Events;

use Packaged\Event\Events\AbstractEvent;

class ContextDoneEvent extends AbstractEvent
{
  const RSN_DESTRUCT = 1;
  const RSN_TRIGGER = 2;
  const RSN_TIMEOUT = 3;

  const TYPE = 'context.done';

  private $_reason;

  public function __construct($reason)
  {
    parent::__construct();
    $this->_reason = $reason;
  }

  public function getType()
  {
    return static::TYPE;
  }

  public function reason()
  {
    return $this->_reason;
  }
}
