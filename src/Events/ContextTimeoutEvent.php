<?php
namespace Packaged\Context\Events;

use Packaged\Event\Events\AbstractEvent;

class ContextTimeoutEvent extends AbstractEvent
{
  const TYPE = 'context.timeout';
  private $_expiryTime;
  private $_duration;

  public function __construct($duration, $expiryTime)
  {
    parent::__construct();
    $this->_duration = $duration;
    $this->_expiryTime = $expiryTime;
  }

  public function getType()
  {
    return static::TYPE;
  }

  public function getDuration()
  {
    return $this->_duration;
  }

  public function getExpiryTime()
  {
    return $this->_expiryTime;
  }
}
