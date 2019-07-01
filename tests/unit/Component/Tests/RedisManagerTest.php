<?php
namespace Imi\Test\Component\Tests;

use Imi\Test\BaseTest;
use Imi\App;
use Imi\Redis\RedisManager;
use PHPUnit\Framework\Assert;
use Imi\Redis\RedisHandler;
use Imi\Pool\PoolManager;

/**
 * @testdox RedisManager
 */
class RedisManagerTest extends BaseTest
{
    public function testDefaultPoolName()
    {
        Assert::assertEquals('redis_test', RedisManager::getDefaultPoolName());
    }

    public function testNewInstance()
    {
        $pool = PoolManager::getInstance(RedisManager::getDefaultPoolName());
        try {
            Assert::assertEquals(1, $pool->getCount());
            Assert::assertEquals(1, $pool->getFree());

            $instance = RedisManager::getNewInstance();

            Assert::assertEquals(1, $pool->getCount());
            Assert::assertEquals(0, $pool->getFree());
            $this->assertRedisHandler($instance);
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            if($instance)
            {
                RedisManager::release($instance);
                Assert::assertEquals(1, $pool->getCount());
                Assert::assertEquals(1, $pool->getFree());
            }
        }
    }

    public function testInstance()
    {
        $pool = PoolManager::getInstance(RedisManager::getDefaultPoolName());
        $this->go(function() use($pool){
            Assert::assertEquals(1, $pool->getCount());
            Assert::assertEquals(1, $pool->getFree());

            $instance = RedisManager::getInstance();

            Assert::assertEquals(1, $pool->getCount());
            Assert::assertEquals(0, $pool->getFree());

            $this->assertRedisHandler($instance);
        });
        Assert::assertEquals(1, $pool->getCount());
        Assert::assertEquals(1, $pool->getFree());
    }

    /**
     * @param \Imi\Redis\RedisHandler $redisHandler
     * @return void
     */
    private function assertRedisHandler($redisHandler)
    {
        Assert::assertInstanceOf(RedisHandler::class, $redisHandler);
        $time = time();
        $redisHandler->set('imi:test:a', $time);
        Assert::assertEquals($time, $redisHandler->get('imi:test:a'));
    }

}