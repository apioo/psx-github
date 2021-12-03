<?php

namespace PSX\Github\Controller;

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use PSX\Http\Exception\BadRequestException;
use PSX\Http\RequestInterface;

trait AuthenticationTrait
{
    /**
     * @Inject
     */
    private Connection $connection;

    private function getUserId(RequestInterface $request): string
    {
        $auth = $request->getHeader('Authorization');
        $token = trim(substr($auth, 6));

        try {
            $data = JWT::decode($token, $this->config->get('app_secret'), [Callback::JWT_ALG]);
        } catch (\Exception $e) {
            throw new BadRequestException('User not authenticated');
        }

        $userId = $data->user ?? null;
        if (empty($userId)) {
            throw new BadRequestException('User not authenticated');
        }

        $id = $this->connection->fetchFirstColumn('SELECT li_id FROM apioo_license WHERE li_name = :name AND li_status = 1', ['name' => $userId]);
        if (empty($id)) {
            throw new BadRequestException('You are not an active Github supporter, please support our project at https://github.com/sponsors/chriskapp to obtain access to the generator.');
        }

        return $userId;
    }
}
