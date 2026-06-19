<?php

namespace WebPag\Tests;

use PHPUnit\Framework\TestCase;
use WebPag\Environment;

class EnvironmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Guarda variáveis originais
        $this->originalToken = getenv('WEBPAG_API_TOKEN');
        $this->originalBaseUrl = getenv('WEBPAG_BASE_URL');
        $this->originalTimeout = getenv('WEBPAG_TIMEOUT');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Restaura variáveis originais
        if ($this->originalToken !== false) {
            putenv('WEBPAG_API_TOKEN=' . $this->originalToken);
        } else {
            putenv('WEBPAG_API_TOKEN');
        }
        if ($this->originalBaseUrl !== false) {
            putenv('WEBPAG_BASE_URL=' . $this->originalBaseUrl);
        } else {
            putenv('WEBPAG_BASE_URL');
        }
        if ($this->originalTimeout !== false) {
            putenv('WEBPAG_TIMEOUT=' . $this->originalTimeout);
        } else {
            putenv('WEBPAG_TIMEOUT');
        }
    }

    public function testFromEnvReadsApiToken()
    {
        putenv('WEBPAG_API_TOKEN=env-token');
        putenv('WEBPAG_BASE_URL');
        putenv('WEBPAG_TIMEOUT');

        $env = Environment::fromEnv();
        $config = $env->toConfiguration();

        $this->assertEquals('env-token', $config->getApiToken());
        $this->assertEquals('https://api.webpag.com.br', $config->getBaseUrl());
    }

    public function testFromEnvReadsAllVariables()
    {
        putenv('WEBPAG_API_TOKEN=full-token');
        putenv('WEBPAG_BASE_URL=https://sandbox.api.webpag.com.br');
        putenv('WEBPAG_TIMEOUT=15');

        $env = Environment::fromEnv();
        $config = $env->toConfiguration();

        $this->assertEquals('full-token', $config->getApiToken());
        $this->assertEquals('https://sandbox.api.webpag.com.br', $config->getBaseUrl());
        $this->assertEquals(15, $config->getTimeout());
    }

    public function testFromEnvReturnsEmptyTokenWhenNotSet()
    {
        putenv('WEBPAG_API_TOKEN');
        putenv('WEBPAG_BASE_URL');
        putenv('WEBPAG_TIMEOUT');

        $env = Environment::fromEnv();
        $config = $env->toConfiguration();

        $this->assertEquals('', $config->getApiToken());
    }

    public function testFromArrayWithAllKeys()
    {
        $env = Environment::fromArray([
            'api_token' => 'array-token',
            'base_url' => 'https://custom.api.com',
            'timeout' => 45,
        ]);

        $config = $env->toConfiguration();

        $this->assertEquals('array-token', $config->getApiToken());
        $this->assertEquals('https://custom.api.com', $config->getBaseUrl());
        $this->assertEquals(45, $config->getTimeout());
    }

    public function testFromArrayIgnoresMissingKeys()
    {
        $env = Environment::fromArray([]);
        $config = $env->toConfiguration();

        $this->assertEquals('', $config->getApiToken());
        $this->assertEquals('https://api.webpag.com.br', $config->getBaseUrl());
        $this->assertEquals(30, $config->getTimeout());
    }

    public function testSettersAreFluent()
    {
        $env = new Environment();

        $return = $env->setApiToken('tok')
            ->setBaseUrl('https://ex.com')
            ->setTimeout(10);

        $this->assertSame($env, $return);
    }

    public function testToConfigurationUsesDefaultsForEmptyBaseUrl()
    {
        $env = new Environment();
        $env->setApiToken('tok');

        $config = $env->toConfiguration();

        $this->assertEquals('https://api.webpag.com.br', $config->getBaseUrl());
        $this->assertEquals(30, $config->getTimeout());
    }
}
