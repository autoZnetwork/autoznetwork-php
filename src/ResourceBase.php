<?php

/*
 * This file is part of the AutozNetwork.com PHP Client.
 *
 * (c) 2020 AutozNetwork.com, https://www.autoznetwork.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AutozNetwork;

use AutozNetwork\Exception\AuthorizationException;
use AutozNetwork\Exception\ForbiddenException;
use AutozNetwork\Exception\InternalErrorException;
use AutozNetwork\Exception\NetworkErrorException;
use AutozNetwork\Exception\RateLimitException;
use AutozNetwork\Exception\ResourceNotFoundException;
use AutozNetwork\Exception\UnexpectedErrorException;
use AutozNetwork\Exception\ValidationException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;

abstract class ResourceBase implements ResourceInterface
{
    protected AutozNetwork $autozNetwork;

    protected Client $client;

    private static $file_parameters = ['front', 'back', 'file', 'check_bottom', 'attachment'];

    public function __construct(AutozNetwork $autozNetwork)
    {
        $this->autozNetwork = $autozNetwork;
        $this->client = new Client(['base_uri' => 'https://deep-moss-2t1sgialfkgv.vapor-farm-a1.com']);
    }

    public function all(array $query = [])
    {
        return $this->sendRequest(
            'GET',
            $this->resourceName(),
            $query
        );
    }

    public function create(array $data, array $headers = null)
    {
        if (array_key_exists('merge_variables', $data)) {
            $data['merge_variables'] = json_encode($data['merge_variables']);
        }

        return $this->sendRequest(
            'POST',
            $this->resourceName(),
            [],
            $data,
            $headers
        );
    }

    public function get($id)
    {
        return $this->sendRequest(
            'GET',
            $this->resourceName().'/'.strval($id)
        );
    }

    public function delete($id)
    {
        return $this->sendRequest(
            'DELETE',
            $this->resourceName().'/'.strval($id)
        );
    }

    /**
     *  Adds a filter to the resource request
     *
     *  @param array|Filter $filter
     *
     *  @return $this
     */
    public function filter($filter)
    {
        if (is_array($filter)) {
            $filter = new Filter($filter);
        }
        $this->filter = $filter;

        return $this;
    }

    /**
     *  Get the current filter
     *
     *  @return Moltin\Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     *  Set the included resources to request
     *
     *  @param array $includes the included resource type(s) eg ['products'], ['products', 'categories']
     *  @return $this
     */
    public function with($includes = [])
    {
        foreach ($includes as $include) {
            $this->includes[] = strtolower(trim($include));
        }

        return $this;
    }

    /**
     *  Adds a sort parameter to the request (eg `-name` or `name,-slug`)
     *
     *  @return $this
     */
    public function sort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     *  Get the resource offset
     *
     *  @return false|string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     *  Set a limit on the number of resources
     *
     *  @param int $limit
     *  @return $this
     */
    public function limit($limit = false)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     *  Get the resource limit
     *
     *  @return false|int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     *  Set an offset on the resources
     *
     *  @param false|int $offset
     *  @return $this
     */
    public function offset($offset = false)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     *  Get the resource offset
     *
     *  @return false|int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     *  Build the query string parameters based on the resource settings
     *
     *  @return array
     */
    public function buildQueryStringParams()
    {
        $params = [];
        if ($this->limit > 0) {
            $params['page']['limit'] = $this->limit;
        }
        if ($this->offset > 0) {
            $params['page']['offset'] = $this->offset;
        }
        if ($this->sort) {
            $params['sort'] = $this->sort;
        }
        if ($this->filter) {
            $params['filter'] = (string) $this->filter;
        }
        if (! empty($this->includes)) {
            $params['include'] = implode(',', $this->includes);
        }

        return $params;
    }

    protected function resourceName()
    {
        $class = explode('\\', strtolower(get_called_class()));

        return array_pop($class);
    }

    protected function sendRequest($method, $path, array $query = [], array $body = null, array $headers = null)
    {
        $path = $this->getPath($path, $query);
        $options = $this->getOptions($body, $headers);

        try {
            $response = $this->client->request($method, $path, $options);
        } catch (ConnectException $e) {
            // @codeCoverageIgnoreStart
            throw new NetworkErrorException($e->getMessage());
            // @codeCoverageIgnoreEnd
        } catch (GuzzleException $e) {
            if (! $e->hasResponse()) {
                throw new UnexpectedErrorException('An Unexpected Error has occurred: ' . $e->getMessage());
            }

            $responseErrorBody = strval($e->getResponse()->getBody());
            $errorMessage = $this->errorMessageFromJsonBody($responseErrorBody);
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 401) {
                throw new AuthorizationException($errorMessage, 401);
            }

            if ($statusCode === 403) {
                throw new ForbiddenException($errorMessage, 403);
            }

            if ($statusCode === 404) {
                throw new ResourceNotFoundException($errorMessage, 404);
            }

            if ($statusCode === 422) {
                throw new ValidationException($errorMessage, 422);
            }

            // @codeCoverageIgnoreStart
            if ($statusCode === 429) {
                throw new RateLimitException($errorMessage, 429);
            }

            if ($statusCode >= 500) {
                throw new InternalErrorException($errorMessage, $statusCode);
            }

            throw new UnexpectedErrorException('An Unexpected Error has occurred: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new UnexpectedErrorException('An Unexpected Error has occurred: ' . $e->getMessage());
            // @codeCoverageIgnoreEnd
        }

        return json_decode($response->getBody(), true);
    }

    protected function getPath($path, array $query = [])
    {
        $path = '/api/v1/'.$path;
        $queryString = '';
        if (! empty($query)) {
            $queryString = '?'.http_build_query($query);
        }

        return $path.$queryString;
    }

    protected function getOptions(array $body = null, array $headers = null)
    {
        $options = [
            'headers' => [
                'Accept' => 'application/json; charset=utf-8',
                'User-Agent' => 'AutozNetwork/v1 PhpBindings/' . $this->autozNetwork->getClientVersion(),
                'Authorization' => 'Bearer ' . $this->autozNetwork->getApiKey(),
            ],
            // 'auth' => array($this->autozNetwork->getApiKey(), '')
        ];

        if ($headers) {
            $options['headers'] = array_merge($options['headers'], $headers);
        }

        if ($version = $this->autozNetwork->getVersion()) {
            $options['headers']['AutozNetwork-Version'] = $version;
        }

        if (! $body) {
            return $options;
        }

        $body = $this->stringifyBooleans($body);
        $files = array_filter($body, function ($element) {
            return (is_string($element) && strpos($element, '@') === 0);
        });

        if (! $files) {
            $options['form_params'] = $body;

            return $options;
        }

        $body = $this->flattenArray($body);
        $options['multipart'] = [];
        foreach ($body as $key => $value) {
            $element = [
                'name' => $key,
                'contents' => $value,
            ];

            if (in_array($key, self::$file_parameters) && (is_string($value) && strpos($value, '@') === 0)) {
                $element['contents'] = fopen(substr($value, 1), 'r');
            }

            $options['multipart'][] = $element;
        }

        return $options;
    }

    /*
     * Because guzzle uses http_build_query it will turn all booleans into '' and '1' for
     * false and true respectively. This function will turn all booleans into the string
     * literal 'false' and 'true'
     */
    protected function stringifyBooleans($body)
    {
        return array_map(function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                return $this->stringifyBooleans($value);
            }

            return $value;
        }, $body);
    }

    /*
     * This method is needed because multipart guzzle requests cannot have nested data
     * This function will take:
     * array(
     *     'foo' => array(
     *         'bar' => 'baz'
     *     )
     * )
     * And convert it to:
     * array(
     *     'foo[bar]' => 'baz'
     * )
     */
    protected function flattenArray(array $body, $prefix = '')
    {
        $newBody = [];
        foreach ($body as $k => $v) {
            $key = (! strlen($prefix)) ? $k : "{$prefix}[{$k}]";
            if (is_array($v)) {
                $newBody += $this->flattenArray($v, $key);
            } else {
                $newBody[$key] = $v;
            }
        }

        return $newBody;
    }

    protected function errorMessageFromJsonBody($body)
    {
        $response = json_decode($body, true);
        if (is_array($response) && array_key_exists('error', $response)) {
            $error = $response['error'];

            return $error['message'];
        }
        // @codeCoverageIgnoreStart
        // Pokemon handling is tough to test... "Gotta catch em all!"
        return 'An Internal Error has occurred';
        // @codeCoverageIgnoreEnd
    }
}
