<?php
namespace Packaged\Context;

use Packaged\Config\ConfigProviderInterface;
use Packaged\Config\Provider\ConfigProvider;
use Packaged\Event\Channel\Channel;
use Packaged\Helpers\System;
use Packaged\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use function dirname;
use function getenv;
use function php_sapi_name;
use function uniqid;

class Context
{
  const _ENV_VAR = 'CONTEXT_ENV';

  const ENV_PHPUNIT = 'phpunit';
  const ENV_LOCAL = 'local';
  const ENV_DEV = 'dev';
  const ENV_QA = 'qa';
  const ENV_UAT = 'uat';
  const ENV_STAGE = 'stage';
  const ENV_PROD = 'prod';

  protected $_projectRoot;
  protected $_env;
  protected $_cfg;
  protected $_meta;
  protected $_routeData;

  private $_id;
  private $_events;
  private $_request;

  public final function __construct(Request $request = null)
  {
    // Give this context an ID
    $this->_id = $this->_generateId();
    $this->_request = $request;
    $this->_construct();
  }

  public static function create(string $projectRoot = null, string $environment = null, Request $request = null)
  {
    $ctx = new static($request);
    if($projectRoot !== null)
    {
      $ctx->setProjectRoot($projectRoot);
    }
    if($environment !== null)
    {
      $ctx->setEnvironment($environment);
    }
    return $ctx;
  }

  protected function _generateId()
  {
    return uniqid('ctx-', true);
  }

  protected function _construct()
  {
    //This method will be called after the context has been constructed
  }

  public function setEnvironment(string $env)
  {
    $this->_env = $env;
    return $this;
  }

  public function getProjectRoot(): string
  {
    if($this->_projectRoot === null)
    {
      $this->_projectRoot = dirname(dirname(dirname(dirname((__DIR__)))));
    }
    return $this->_projectRoot;
  }

  public function setProjectRoot(string $root)
  {
    $this->_projectRoot = $root;
    return $this;
  }

  public function isEnv(string $env)
  {
    return $this->getEnvironment() === $env;
  }

  public function getEnvironment(): string
  {
    if($this->_env === null)
    {
      $this->_env = $this->getSystemEnvironment();
    }
    return $this->_env;
  }

  public function isCli()
  {
    return !System::isFunctionDisabled('php_sapi_name') && php_sapi_name() === 'cli';
  }

  /**
   * @param ConfigProviderInterface $config
   *
   * @return $this
   */
  public function setConfig(ConfigProviderInterface $config)
  {
    $this->_cfg = $config;
    return $this;
  }

  /**
   * @return ConfigProviderInterface
   */
  public function getConfig()
  {
    if($this->_cfg === null)
    {
      $this->_cfg = new ConfigProvider();
    }
    return $this->_cfg;
  }

  /**
   * Unique ID for this context
   *
   * @return string
   */
  public function id()
  {
    return $this->_id;
  }

  /**
   * @return Request
   */
  public function request()
  {
    if($this->_request === null)
    {
      $this->_request = Request::createFromGlobals();
    }
    return $this->_request;
  }

  /**
   * @return ParameterBag
   */
  public function meta()
  {
    if($this->_meta === null)
    {
      $this->_meta = new ParameterBag();
    }
    return $this->_meta;
  }

  /**
   * @return ParameterBag
   */
  public function routeData()
  {
    if($this->_routeData === null)
    {
      $this->_routeData = new ParameterBag();
    }
    return $this->_routeData;
  }

  /**
   * @return ConfigProviderInterface
   */
  public function config()
  {
    return $this->getConfig();
  }

  /**
   * Events channel
   *
   * @return Channel
   */
  public function events(): Channel
  {
    if($this->_events === null)
    {
      $this->_events = new Channel('context');
    }
    return $this->_events;
  }

  public function getSystemEnvironment()
  {
    //Calculate the environment
    $env = getenv(static::_ENV_VAR);
    if(($env === null || !$env) && isset($_ENV[static::_ENV_VAR]))
    {
      $env = (string)$_ENV[static::_ENV_VAR];
    }
    if($env === null || !$env)//If there is no environment available, assume local
    {
      $env = self::ENV_LOCAL;
    }
    return $env;
  }
}
