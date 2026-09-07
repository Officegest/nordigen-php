<?php

namespace UnitTests\CoreTests;

use Nordigen\NordigenPHP\Exceptions\InstitutionExceptions\RateLimitError;
use Nordigen\NordigenPHP\Exceptions\ExceptionHandler;
use Nordigen\NordigenPHP\Exceptions\NordigenExceptions\NordigenException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class ExceptionHandlerTest extends TestCase
{
    /**
     * @covers \Nordigen\NordigenPHP\Exceptions\ExceptionHandler
     */
    public function testCorrectExceptionIsThrown()
    {
        $jsonBody = json_encode([
            'detail' => 'Rate limit exceeded',
            'type' => 'RateLimitError'
        ]);
        $response = new Response(429, [], $jsonBody);

        $this->expectException(RateLimitError::class);
        ExceptionHandler::handleException($response);
    }

    /**
     * @covers \Nordigen\NordigenPHP\Exceptions\ExceptionHandler
     */
    public function testNordigenExceptionIsThrownWhenNoMatch()
    {
        $jsonBody = json_encode([
            'detail' => 'Rate limit exceeded',
            'type' => 'SomeNewError'
        ]);
        $response = new Response(401, [], $jsonBody);

        $this->expectException(NordigenException::class);
        ExceptionHandler::handleException($response);
    }

    /**
     * @covers \Nordigen\NordigenPHP\Exceptions\ExceptionHandler
     */
    public function testNordigenExceptionIsThrownForNonJsonResponse(): void
    {
        $response = new Response(502, ['Content-Type' => 'text/html'], '<html>Bad Gateway</html>');

        try {
            ExceptionHandler::handleException($response);
            $this->fail('Expected a NordigenException to be thrown.');
        } catch (NordigenException $exception) {
            $this->assertSame(502, $exception->getCode());
            $this->assertSame($response, $exception->getResponse());
        }
    }
}
