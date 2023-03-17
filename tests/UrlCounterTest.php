<?php
declare (strict_types = 1);

namespace bileslav\Three1911s\Phptest;

use PHPUnit\Framework\TestCase;

final class UrlCounterTest extends TestCase
{
	private const URLS = [
		'https://www.example.com',
		'http://example.net:80',
		'HTTP://EXAmPle.Net/',
		'https://mail.example.net',
		'example',
		'ftp://127.0.0.1',
	];

	public function testCountUniqueUrls(): void
	{
		$counter = new UrlCounter();
		$count = $counter->countUniqueUrls(self::URLS);

		$this->assertSame(5, $count);
	}

	public function testCountUniqueUrlsPerTopLevelDomain(): void
	{
		$counter = new UrlCounter();
		$counts = $counter->countUniqueUrlsPerTopLevelDomain(self::URLS);

		$this->assertSame([
			'example.com' => 1,
			'example.net' => 2,
			UrlCounter::NOT_A_DOMAIN => 2,
		], $counts);
	}
}
