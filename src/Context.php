<?php
namespace Packaged\Context;

use Packaged\Config\ConfigProviderInterface;
use Packaged\Config\Provider\ConfigProvider;
use Packaged\Context\Conditions\ExpectEnvironment;
use Packaged\Event\Channel\Channel;
use Packaged\Helpers\System;
use Packaged\Http\Cookies\CookieJar;
use Packaged\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use function dirname;
use function getenv;
use function php_sapi_name;
use function uniqid;

class Context implements ContextAware
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
  private $_cookieJar;
  /**
   * @var Context
   */
  private $_parent;

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

  public static function extends(Context $ctx)
  {
    $c = new static();
    $c->_projectRoot = $ctx->_projectRoot;
    $c->_env = $ctx->_env;
    $c->_cfg = $ctx->_cfg;
    $c->_meta = $ctx->_meta;
    $c->_routeData = $ctx->_routeData;

    $c->_id = $ctx->_id;
    $c->_events = $ctx->_events;
    $c->_request = $ctx->_request;
    $c->_cookieJar = $ctx->_cookieJar;
    return $c;
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

  /** @deprecated user matches(new ExpectEnvironment($env)) */
  public function isEnv(string $env)
  {
    return $this->matches(new ExpectEnvironment($env));
  }

  /** @deprecated user matches(ExpectEnvironment::local()) */
  public function isLocal()
  {
    return $this->matches(ExpectEnvironment::local());
  }

  /** @deprecated user matches(ExpectEnvironment::prod()) */
  public function isProd()
  {
    return $this->matches(ExpectEnvironment::prod());
  }

  /** @deprecated user matches(ExpectEnvironment::phpunit()) */
  public function isUnitTest()
  {
    return $this->matches(ExpectEnvironment::phpunit());
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

  /**
   * Cookies for the request
   *
   * @return CookieJar
   */
  public function cookies(): CookieJar
  {
    if($this->_cookieJar === null)
    {
      $this->_cookieJar = $this->_configureCookieJar(new CookieJar());
      $this->_cookieJar->hydrate($this->request());
    }
    return $this->_cookieJar;
  }

  protected function _configureCookieJar(CookieJar $jar): CookieJar
  {
    return $jar;
  }

  /**
   * Retrieve the CONTEXT_ENV from the environment
   *
   * @return array|false|string
   */
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

  public function setContext(Context $context)
  {
    if($context !== $this)
    {
      throw new \Exception("You cannot set context on context");
    }
    return $this;
  }

  public function getContext(): Context
  {
    return $this;
  }

  public function hasContext(): bool
  {
    return true;
  }

  final public static function withParent(Context $parentCtx)
  {
    $ctx = new static();
    $ctx->_parent = $parentCtx;
    return $ctx;
  }

  public function hasParent()
  {
    return $this->_parent !== null;
  }

  public function parent($root = false): ?Context
  {
    if($root)
    {
      $ctx = $this->_parent;
      while($ctx && $ctx->_parent)
      {
        $ctx = $ctx->_parent;
      }
      return $ctx;
    }
    return $this->_parent;
  }

  public function matches(Condition ...$condition): bool
  {
    foreach($condition as $cond)
    {
      if(!$cond->isSatisfied($this))
      {
        return false;
      }
    }
    return true;
  }

  public function matchAny(Condition ...$condition): bool
  {
    foreach($condition as $cond)
    {
      if($cond->isSatisfied($this))
      {
        return true;
      }
    }
    return false;
  }

  public function __debugInfo()
  {
    return [
      'id'          => $this->_id,
      'env'         => $this->_env,
      'projectRoot' => $this->_projectRoot,
      'meta'        => $this->_meta,
      'routeData'   => $this->_routeData,
      'request'     => $this->_request,
    ];
  }
}
