<?php

namespace App\Tests\Functional;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Finder\Finder;

abstract class AbstractApiTest extends WebTestCase
{
    use PHPMatcherAssertions;

    /**
     * @return \Generator<\SplFileInfo>
     */
    protected function yieldFilesFromDirectory(string $directory): \Generator
    {
        $finder = new Finder();
        $finder->in($directory)
            ->ignoreVCS(true)
            ->files()
            ->name('*.md');

        foreach ($finder as $file) {
            if ($file instanceof \SplFileInfo) {
                yield \str_replace(\getcwd() . '/', '', $file->getPathname()) => [$file];
            }
        }
    }

    protected function doTestFileInfo(\SplFileInfo $fileInfo): void
    {
        // arrange
        $content = \file_get_contents($fileInfo->getPathname());

        [$input, $output] = \explode("\n---\n", $content);

        [$method, $uri, $headers, $content] = $this->parseRequest($input);
        [$expectedServerProtocol, $expectedStatusCode, $expectedHeaders, $expectedContent] = $this->parseResponse($output);

        /** @var array<string, string> $server */
        $server = [];
        foreach ($headers as $key => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        // act
        $client = $this->createClient();
        $client->request($method, $uri, [], [], $server, $content);

        // assert
        $response = $client->getResponse();

        $this->assertSame($expectedServerProtocol, $response->getProtocolVersion());
        $this->assertSame($expectedStatusCode, $response->getStatusCode());
        foreach ($expectedHeaders as $headerName => $expectedHeaderValue) {
            $this->assertSame($expectedHeaderValue, $response->headers->get($headerName));
        }

        $this->assertMatchesPattern($expectedContent, $response->getContent());
    }

    /**
     * @return array{
     *     0: string,
     *     1: string,
     *     2: array<string, string>,
     *     3: string,
     * }
     */
    private function parseRequest(string $input): array
    {
        preg_match('/```http request\n(\w+) (.+)\n([^```]*)```([^```]*```\w+\n([^```]*)```)?/', $input, $matches);
        $method = $matches[1];
        $uri = $matches[2];
        $headerString = $matches[3];
        $content = $matches[5] ?? '';

        $headers = [];
        $headerParts = array_filter(explode("\n", $headerString));
        foreach ($headerParts as $headerPart) {
            [$headerName, $headerValue] = \explode(':', $headerPart, 2);
            $headers[trim($headerName)] = trim($headerValue);
        }

        return [$method, $uri, $headers, $content];
    }

    /**
     * @return array{
     *     0: string,
     *     1: int,
     *     2: array<string, string>,
     *     3: string,
     * }
     */
    private function parseResponse(string $output): array
    {
        preg_match('/```http request\n\w+\/(\d+.\d+) (\d+) \w+\n([^```]*)```([^```]*```\w+\n([^```]*)```)?/', $output, $matches);
        $serverProtocol = $matches[1];
        $statusCode = (int) $matches[2];
        $headerString = $matches[3];
        $content = $matches[5] ?? '';

        $headers = [];
        $headerParts = array_filter(explode("\n", $headerString));
        foreach ($headerParts as $headerPart) {
            [$headerName, $headerValue] = \explode(':', $headerPart, 2);
            $headers[trim($headerName)] = trim($headerValue);
        }

        return [$serverProtocol, $statusCode, $headers, $content];
    }
}
