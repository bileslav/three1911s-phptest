<?php
declare (strict_types = 1);

namespace bileslav\Three1911s\Phptest;

final class Url
{
	private ?string $scheme = null;
	private ?string $user = null;
	private ?string $pass = null;
	private ?string $host = null;
	private ?int $port = null;
	private ?string $path = null;
	private ?string $query = null;
	private ?string $fragment = null;

	/**
	 * @throws MalformedUrlException
	 */
	public function __construct(string $url)
	{
		$components = parse_url($url);

		if ($components === false) {
			throw new MalformedUrlException($url);
		}

		foreach ($components as $component => $value) {
			$this->{$component} = $value;
		}
	}

	/**
	 * As per the intent, this method assumes none
	 * of the URLs have authentication information.
	 */
	public function normalize(): self
	{
		$that = clone $this;

		if ($that->scheme !== null) {
			$that->scheme = strtolower($that->scheme);
		}

		$that->user = null;
		$that->pass = null;

		if ($that->host !== null) {
			$that->host = strtolower($that->host);
		}

		if (
			$that->port === 80 && $that->scheme === 'http' ||
			$that->port === 443 && $that->scheme === 'https'
		) {
			$that->port = null;
		}

		if ($that->path !== null) {
			$path = explode('/', $that->path);
			$path = array_map('rawurldecode', $path);
			$path = array_map('rawurlencode', $path);
			$path = implode('/', $path);

			$that->path = self::realpath($path);
		} else if ($that->getAuthority() !== null) {
			$that->path = '/';
		}

		if ($that->query === '') {
			$that->query = null;
		} else if ($that->query !== null) {
			parse_str($that->query, $query);
			ksort($query);

			$that->query = http_build_query(
				$query,
				encoding_type: PHP_QUERY_RFC3986,
			);
		}

		$that->fragment = null;

		return $that;
	}

	private function getAuthority(): ?string
	{
		$userinfo = $this->user;

		if ($this->pass !== null) {
			$userinfo = "{$userinfo}:{$this->pass}";
		}

		$authority = implode([
			self::sprintf('%s@', $userinfo),
			self::sprintf('%s', $this->host),
			self::sprintf(':%d', $this->port),
		]);

		if ($authority === '') {
			return null;
		}

		return $authority;
	}

	public function getRootDomain(): ?string
	{
		if ($this->host === null || self::isIp($this->host)) {
			return null;
		}

		$domain = explode('.', $this->host);
		$domain = array_reverse($domain);
		$domain = array_slice($domain, 0, 2);
		$domain = array_reverse($domain);
		$domain = implode('.', $domain);

		return $domain;
	}

	public function toString(): string
	{
		return implode([
			self::sprintf('%s:', $this->scheme),
			self::sprintf('//%s', $this->getAuthority()),
			self::sprintf('%s', $this->path),
			self::sprintf('?%s', $this->query),
			self::sprintf('#%s', $this->fragment),
		]);
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	private static function isIp(string $data): bool
	{
		return filter_var($data, FILTER_VALIDATE_IP) !== false;
	}

	private static function sprintf(string $format, string|int|null $value): string
	{
		if ($value === null) {
			return '';
		}

		return sprintf($format, $value);
	}

	private static function realpath(string $path): string
	{
		$absolutes = [];

		foreach (explode('/', $path) as $part) {
			if ($part === '.') {
				continue;
			}

			if ($part !== '..') {
				$absolutes[] = $part;
			} else if (count($absolutes) > 1) {
				array_pop($absolutes);
			}
		}

		return implode('/', $absolutes);
	}
}
