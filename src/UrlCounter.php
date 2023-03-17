<?php
declare (strict_types = 1);

namespace _3c\Phptest;

final class UrlCounter
{
	/**
	 * This function counts how many unique
	 * normalized valid URLs were passed to the function.
	 *
	 * @param string[] $urls
	 * @throws UrlMalformedException
	 */
	public function countUniqueUrls(array $urls): int
	{
		return array_sum($this->countUniqueUrlsPerTopLevelDomain($urls));
	}

	/**
	 * This function counts how many unique
	 * normalized valid URLs were passed to the function per top level
	 * domain. A top level domain is a domain in the form of example.com;
	 * subdomain.example.com is not a top level domain.
	 *
	 * @param string[] $urls
	 * @return array<string, int>
	 * @throws UrlMalformedException
	 */
	public function countUniqueUrlsPerTopLevelDomain(array $urls): array
	{
		$result = [];

		foreach ($urls as $url) {
			$url = (new Url($url))->normalize();
			$result[$url->getRootDomain() ?? ''][] = $url->toString();
		}

		$result = array_map('array_unique', $result);
		$result = array_map('count', $result);

		return $result;
	}
}
