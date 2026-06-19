<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Configuration;

class ConfigurationTest extends TestCase
{
    public function testConstructorSetsApiToken()
    {
        $config = new Configuration('my-token');
        $this->assertEquals('my-token', $config->getApiToken());
    }

    public function testConstructorSetsDefaultBaseUrl()
    {
        $config = new Configuration('my-token');
        $this->assertEquals('https://api.webpag.com.br', $config->getBaseUrl());
    }

    public function testConstructorSetsCustomBaseUrl()
    {
        $config = new Configuration('my-token', 'https://sandbox.api.webpag.com.br');
        $this->assertEquals('https://sandbox.api.webpag.com.br', $config->getBaseUrl());
    }

    public function testConstructorRemovesTrailingSlashFromBaseUrl()
    {
        $config = new Configuration('my-token', 'https://api.webpag.com.br/');
        $this->assertEquals('https://api.webpag.com.br', $config->getBaseUrl());
    }

    public function testConstructorSetsDefaultTimeout()
    {
        $config = new Configuration('my-token');
        $this->assertEquals(30, $config->getTimeout());
    }

    public function testConstructorSetsCustomTimeout()
    {
        $config = new Configuration('my-token', null, 60);
        $this->assertEquals(60, $config->getTimeout());
    }
}
