<?php

namespace PSX\Github\Controller;

use Firebase\JWT\JWT;
use PSX\Framework\Controller\ViewAbstract;
use PSX\Http\Client\Client;
use PSX\Http\Client\GetRequest;
use PSX\Http\Client\PostRequest;
use PSX\Http\Exception as StatusCode;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class Callback extends ViewAbstract
{
    private const USER_AGENT = 'PSX (https://phpsx.org)';
    public const JWT_ALG = 'HS256';

    /**
     * @Inject
     */
    protected Client $httpClient;

    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $body = $this->requestReader->getBody($request);
        $code = $body->code ?? null;
        $state = $body->state ?? null;

        $this->assertState($request, $state);

        $data = $this->requestAccessToken($code);
        $accessToken = $data->access_token ?? null;
        if (empty($accessToken)) {
            throw new StatusCode\BadRequestException('Could not obtain access token');
        }

        $data = $this->requestUserInfo($accessToken);
        $userId = $data->login ?? null;
        if (empty($userId)) {
            throw new StatusCode\BadRequestException('Could not obtain user id');
        }

        $token = JWT::encode(['user' => $userId], $this->config->get('app_secret'), self::JWT_ALG);

        $this->responseWriter->setBody($response, [
            'user' => $userId,
            'token' => $token,
        ]);
    }

    private function requestAccessToken(string $code)
    {
        $body = json_encode([
            'client_id' => $this->config->get('github_client_id'),
            'client_secret' => $this->config->get('github_client_secret'),
            'code' => $code,
            'redirect_uri' => 'https://apigen.app/callback'
        ]);
        $headers = [
            'User-Agent' => self::USER_AGENT,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $request = new PostRequest('https://github.com/login/oauth/access_token', $headers, $body);
        $response = $this->httpClient->request($request);

        if ($response->getStatusCode() !== 200) {
            throw new StatusCode\BadRequestException('Could not obtain access token');
        }

        return \json_decode((string) $response->getBody());
    }

    private function requestUserInfo(string $accessToken)
    {
        $headers = ['User-Agent' => self::USER_AGENT, 'Accept' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken];
        $request = new GetRequest('https://api.github.com/user', $headers);
        $response = $this->httpClient->request($request);

        if ($response->getStatusCode() !== 200) {
            throw new StatusCode\BadRequestException('Could not obtain user information');
        }

        return \json_decode((string) $response->getBody());
    }

    private function assertState(RequestInterface $request, string $state)
    {
        try {
            $data = JWT::decode($state, $this->config->get('app_secret'), [self::JWT_ALG]);
        } catch (\Exception $e) {
            throw new StatusCode\BadRequestException('Provided state is invalid');
        }

        if ($data->agent !== $request->getHeader('User-Agent')) {
            throw new StatusCode\BadRequestException('Provided state is invalid');
        }

        if ($data->ip !== $_SERVER['REMOTE_ADDR']) {
            throw new StatusCode\BadRequestException('Provided state is invalid');
        }
    }
}
