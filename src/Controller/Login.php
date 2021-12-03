<?php

namespace PSX\Github\Controller;

use Firebase\JWT\JWT;
use PSX\Framework\Controller\ViewAbstract;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class Login extends ViewAbstract
{
    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        $context = [
            'agent' => $request->getHeader('User-Agent'),
            'ip' => $_SERVER['REMOTE_ADDR'],
        ];

        $clientId = $this->config->get('github_client_id');
        $state = JWT::encode($context, $this->config->get('app_secret'));

        $url = 'https://github.com/login/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'state' => $state,
        ]);

        $this->responseWriter->setBody($response, [
            'url' => $url,
        ]);
    }
}
