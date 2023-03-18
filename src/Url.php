<?php
declare (strict_types = 1);

namespace bileslav\Three1911s\Phptest;

final class Url
{
	private const DEFAULT_PORTS = [
		'http' => 80,
		'https' => 443,
	];

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
	 *
	 * @return self new normalized `Url` instance
	 */
	public function normalize(): self
	{
		$url = clone $this;

		$url->lowercaseScheme();
		$url->removeUserinfo();
		$url->lowercaseHost();
		$url->removePortIfDefault();
		$url->normalizePath();
		$url->sortQueryAndUseRfc3986();
		$url->removeQueryIfEmpty();
		$url->removeFragment();

		return $url;
	}

	private function lowercaseScheme(): void
	{
		if ($this->scheme === null) {
			return;
		}

		$this->scheme = strtolower($this->scheme);
	}

	private function removeUserinfo(): void
	{
		$this->user = null;
		$this->pass = null;
	}

	private function lowercaseHost(): void
	{
		if ($this->host === null) {
			return;
		}

		$this->host = strtolower($this->host);
	}

	private function removePortIfDefault(): void
	{
		if (
			array_key_exists($this->scheme, self::DEFAULT_PORTS) &&
			$this->port === self::DEFAULT_PORTS[$this->scheme]
		) {
			$this->port = null;
		}
	}

	private function normalizePath(): void
	{
		if ($this->path !== null) {
			$this->reencodePath();
			$this->removeDotSegments();
		} else if ($this->getAuthority() !== null) {
			$this->path = '/';
		}
	}

	private function reencodePath(): void
	{
		$path = explode('/', $this->path);

		$path = array_map('rawurldecode', $path);
		$path = array_map('rawurlencode', $path);

		$this->path = implode('/', $path);
	}

	private function removeDotSegments(): void
	{
		$path = [];

		foreach (explode('/', $this->path) as $segment) {
			if ($segment === '.') {
				continue;
			}

			if ($segment !== '..') {
				$path[] = $segment;
			} else if ($path !== ['']) {
				array_pop($path);
			}
		}

		$this->path = implode('/', $path);
	}

	private function removeQueryIfEmpty(): void
	{
		if ($this->query === '') {
			$this->query = null;
		}
	}

	private function sortQueryAndUseRfc3986(): void
	{
		if ($this->query === null) {
			return;
		}

		parse_str($this->query, $query);
		ksort($query);

		$this->query = http_build_query(
			$query,
			encoding_type: PHP_QUERY_RFC3986,
		);
	}

	private function removeFragment(): void
	{
		$this->fragment = null;
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
		$host = $this->host;

		if ($host === null || self::isIp($host)) {
			return null;
		}

		$host = explode('.', $host);
		$host = array_slice($host, -2, 2);
		$host = implode('.', $host);

		return $host;
	}

	private static function isIp(string $data): bool
	{
		return filter_var($data, FILTER_VALIDATE_IP) !== false;
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

	private static function sprintf(string $format,
		string|int|null $value): string
	{
		if ($value === null) {
			return '';
		}

		return sprintf($format, $value);
	}
}
