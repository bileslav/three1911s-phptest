<?php
declare (strict_types = 1);

namespace _3c\Phptest;

use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
	private const LONG_URL = (
		'https://jschauma:hunter2@www.netmeister.org:443' .
		'/blog/urls.html?q=s&q2=a+b;q3=sp%0Ace#top'
	);

	public function testMalformed(): void
	{
		$this->expectException(UrlMalformedException::class);

		new Url('http://localhost:65536');
	}

	/**
	 * @dataProvider getNormalizationTestCases
	 */
	public function testNormalize(string $input, string $output): void
	{
		$this->assertSame($output, (new Url($input))->normalize()->toString());
	}

	public function testGetRootDomain(): void
	{
		$this->assertSame('netmeister.org', (new Url(self::LONG_URL))->getRootDomain());
	}

	public function testToString(): void
	{
		$this->assertSame(self::LONG_URL, (new Url(self::LONG_URL))->toString());
	}

	public static function getNormalizationTestCases(): iterable
	{
		foreach ([
			self::LONG_URL => 'https://www.netmeister.org/blog/urls.html',

			'http://www.example.com/%7Efoo' => 'http://www.example.com/~foo',
			'http://www.example.com/foo%2a' => 'http://www.example.com/foo%2A',

			'http://example.org/../a/.././p%61th' => 'http://example.org/path',
			'http://example.org/foo/./bar/BAZ/../QUX' => 'http://example.org/foo/bar/QUX',
			'http://example.org/path?k=v#f' => 'http://example.org/path',

			'https://Example.NET:443' => 'https://example.net/',
			'TCP://127.0.0.1:443/app?' => 'tcp://127.0.0.1:443/app',

			'http://Example.NET:80' => 'http://example.net/',
			'TCP://127.0.0.1:80/app?' => 'tcp://127.0.0.1:80/app',

			'urn:?#' => 'urn:',
			'file:///myfile' => 'file:/myfile',
			'udp://LOCALHOST' => 'udp://localhost/',
			'//example.com/' => '//example.com/',
			'example' => 'example',
		] as $input => $output) {
			yield [$input, $output];
		}
	}
}