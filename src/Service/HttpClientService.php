<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientService
{
    private HttpClientInterface $httpClient;

    function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function fetchData(string $url): array
    {
        $response = $this->httpClient->request('GET', $url);

        // Handle non-200 responses
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf('API request failed with status code %d', $response->getStatusCode()));
        }

        return $response->toArray();
    }


}
