<?php
namespace App\Libraries;

use Github\Api\Repo;
use Github\Client;
use Github\HttpClient\CachedHttpClient;
use RuntimeException;

/**
 * GitHub API Library
 *
 * Wraps the KnpLabs library, providing methods for each
 * of the specific endpoints needed by the website.
 * The core version requires PSR's CacheItemPoolInterface
 * to handle caching natively, which CodeIgniter does not
 * yet support so caching is handled here instead.
 * The core version requires PSR-17 and PSR-18 implementations
 * which CodeIgniter does not yet support so the following
 * packages are included with Composer (but can be removed
 * if the framework ever implements these directly):
 * - guzzlehttp/guzzle
 * - http-interop/http-factory-guzzle
 */
class GithubAPI
{

	/**
	 * Github API Client 
	 *
	 * @var object
	 */
	private $client = null;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->client = new Client();
	}

	/**
	 * Shorthand for API repo calls.
	 *
	 * @return Repo
	 */
	private function api(): Repo
	{
		return $this->client->api('repo');
	}

	/**
	 * Retrieves extended information about a repository.
	 *
	 * @param string $username
	 * @param string $repository
	 *
	 * @return array Repository information
	 */
	public function getRepoInfo($username, $repository): array
	{
		return $this->api()->show($username, $repository);
	}

	/**
	 * Retrieves extended information about releases.
	 * 
	 * Use this for CodeIgniter4.
	 * 
	 * @param string $username
	 * @param string $repository
	 *
	 * @return array Releases information
	 */
	public function getRepoReleases($username, $repository): array
	{
		return $this->api()->releases()->all($username, $repository);
	}

	/**
	 * Retrieves name & download link for latest release.
	 * 
	 * Use this for CodeIgniter4.
	 * 
	 * @param string $username   the username
	 * @param string $repository the repository name
	 *
	 * @return array|null Releases information
	 */
	public function getLatestRelease($username, $repository): array
	{
		if ($releases = $this->getRepoReleases($username, $repository))
		{
			return reset($releases);
		}

		throw new RuntimeException("No releases located for {$username}/{$repository}");
	}

	/**
	 * Retrieves extended information about the tags (releases).
	 * 
	 * Use this for CodeIgniter3.
	 * 
	 * @param string $username   the username
	 * @param string $repository the repository name
	 *
	 * @return array Releases information
	 */
	public function getRepoTags($username, $repository): array
	{
		$results = [];
		foreach ($this->api()->tags($username, $repository) as $result)
		{
			if (isset($result['name']) && substr($result['name'], 0, 1) !== 'v')
			{
				$results[] = $result;
			}
		}

		return $results;
	}
//WIP
	/**
	 * Retrieves name & download link for latest tag.
	 * 
	 * Use this for CodeIgniter3.
	 * 
	 * @param string $username   the username
	 * @param string $repository the repository name
	 *
	 * @return array|null Releases information
	 */
	public function getLatestTag($username, $repository): ?array
	{
		try
		{
			$info = $this->client->api('repo')->tags($username, $repository);
			$results = [];
			foreach ($info as $key => $value)
			{
				if (substr($value['name'], 0, 1) !== 'v')
				{
					$results[] = $value;
				}
			}
			return ( ! empty($results)) ? $results[0] : FALSE;
		}
		catch (Throwable $e)
		{
			return null;
		}
	}

	/**
	 * Retrieves top 12contributors information for a repository given its username and repository name
	 *
	 * @param string $username   the username
	 * @param string $repository the repository name
	 *
	 * @return array|null Contributor information
	 */
	public function getContributors($username, $repository): ?array
	{
		try
		{
			$info = array_slice($this->client->api('repo')->contributors($username, $repository), 0, 12);
			return ( ! empty($info)) ? $info : FALSE;
		}
		catch (Throwable $e)
		{
			return null;
		}
	}

}
