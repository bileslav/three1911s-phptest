# 3c PHP hiring test

Apart from related things, this repository contains my implementation of the following class:

```php
<?php

namespace _3c\Phptest;

class UrlCounter
{
	/**
	 * This function counts how many unique normalized valid URLs were passed to the function
	 *
	 * Accepts a list of URLs
	 *
	 * Example:
	 *
	 * input: ['https://example.com']
	 * output: 1
	 *
	 * Notes:
	 *  - assume none of the URLs have authentication information (username, password).
	 *
	 * Normalized URL:
	 *  - process in which a URL is modified and standardized: https://en.wikipedia.org/wiki/URL_normalization
	 *
	 *    For example.
	 *    These 2 urls are the same:
	 *    input: ["https://example.com", "https://example.com/"]
	 *    output: 1
	 *
	 *    These 2 are not the same:
	 *    input: ["https://example.com", "http://example.com"]
	 *    output 2
	 *
	 *    These 2 are the same:
	 *    input: ["https://example.com?", "https://example.com"]
	 *    output: 1
	 *
	 *    These 2 are the same:
	 *    input: ["https://example.com?a=1&b=2", "https://example.com?b=2&a=1"]
	 *    output: 1
	 */

	/* @var $urls : string[] */
	public function countUniqueUrls( $urls)
	{
		// TODO your implementation
	}

	/**
	 * This function counts how many unique normalized valid URLs were passed to the function per top level domain
	 *
	 * A top level domain is a domain in the form of example.com. Assume all top level domains end in .com
	 * subdomain.example.com is not a top level domain.
	 *
	 * Accepts a list of URLs
	 *
	 * Example:
	 *
	 * input: ["https://example.com"]
	 * output: ["example.com" => 1]
	 *
	 * input: ["https://example.com", "https://subdomain.example.com"]
	 * output: ["example.com" => 2]
	 *
	 */
	/* @var $urls : string[] */
	public function countUniqueUrlsPerTopLevelDomain( $urls)
	{
		// TODO your implementation
	}
}
```
