<?php

namespace Packaged\Tests\Context;

use Packaged\Config\Provider\ConfigProvider;
use Packaged\Context\Context;
use Packaged\Event\Channel\Channel;
use Packaged\Helpers\Arrays;
use Packaged\Http\Request;
use Packaged\Tests\Context\Supporting\TestContextAwareObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use function dirname;

class ContextTest extends TestCase
{
  public function testDefaults()
  {
    $ctx = new Context();
    $this->assertInstanceOf(ParameterBag::class, $ctx->meta());
    $this->assertInstanceOf(ParameterBag::class, $ctx->routeData());
    $this->assertInstanceOf(ConfigProvider::class, $ctx->config());
    $this->assertInstanceOf(ConfigProvider::class, $ctx->getConfig());
    $this->assertInstanceOf(Request::class, $ctx->request());
    $this->assertInstanceOf(Channel::class, $ctx->events());
    $this->assertEquals(Context::ENV_LOCAL, $ctx->getEnvironment());
    $this->assertTrue($ctx->isCli());
    $this->assertStringStartsWith('ctx-', $ctx->id());
  }

  public function testEnvironment()
  {
    $pre = Arrays::value($_ENV, Context::_ENV_VAR);
    $_ENV[Context::_ENV_VAR] = Context::ENV_PHPUNIT;
    $ctx = new Context();
    $this->assertEquals(Context::ENV_PHPUNIT, $ctx->getEnvironment());
    $this->assertTrue($ctx->isEnv(Context::ENV_PHPUNIT));
    $this->assertFalse($ctx->isEnv(Context::ENV_LOCAL));
    if($pre == null)
    {
      unset($_ENV[Context::_ENV_VAR]);
    }
    else
    {
      $_ENV[Context::_ENV_VAR] = $pre;
    }
    $this->assertFalse($ctx->isEnv(Context::ENV_QA));
    $ctx->setEnvironment(Context::ENV_QA);
    $this->assertTrue($ctx->isEnv(Context::ENV_QA));
  }

  public function testContextAware()
  {
    $obj = new TestContextAwareObject();
    $obj2 = new TestContextAwareObject();
    $ctx = new Context();
    $this->assertFalse($obj->hasContext());
    $obj->setContext($ctx);
    $this->assertTrue($obj->hasContext());
    $this->assertSame($ctx, $obj->getContext());
    $obj->clearContext();
    $this->assertFalse($obj->hasContext());

    $obj2->setContext($ctx);
    $obj2->bind($obj);
    $this->assertTrue($obj2->hasContext());
    $this->assertSame($ctx, $obj->getContext());

    $obj3 = new TestContextAwareObject();
    $obj4 = $obj2->with($obj3);
    $this->assertTrue($obj3->hasContext());
    $this->assertSame($ctx, $obj3->getContext());
    $this->assertSame($obj3, $obj4);
    $this->assertSame($ctx, $obj4->getContext());
  }

  public function testConfig()
  {
    $cnf = new ConfigProvider();
    $ctx = new Context();
    $ctx->setConfig($cnf);
    $this->assertSame($cnf, $ctx->getConfig());
  }

  public function testProjectRoot()
  {
    $ctx = new Context();
    $this->assertEquals(dirname(dirname(dirname(dirname((__DIR__))))), $ctx->getProjectRoot());

    $ctx->setProjectRoot('abc/def');
    $this->assertEquals('abc/def', $ctx->getProjectRoot());
  }

  public function testStaticCreate()
  {
    $req = new Request();
    $ctx = Context::create('/abc', Context::ENV_QA, $req);
    $this->assertSame($req, $ctx->request());
    $this->assertEquals('/abc', $ctx->getProjectRoot());
    $this->assertEquals(Context::ENV_QA, $ctx->getEnvironment());
  }
}
