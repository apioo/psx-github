<?php

namespace PSX\Github\Controller;

use PSX\Framework\Controller\ControllerAbstract;
use PSX\Github\Order;
use PSX\Github\OrderManager;
use PSX\Http\Exception\BadRequestException;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;

class Webhook extends ControllerAbstract
{
    /**
     * @Inject
     */
    protected OrderManager $orderManager;

    public function onPost(RequestInterface $request, ResponseInterface $response)
    {
        if ($request->getUri()->getParameter('token') !== $this->config->get('github_webhook_token')) {
            throw new BadRequestException('Invalid request');
        }

        $payload = $this->requestReader->getBody($request);
        $action = $payload->action ?? null;

        switch ($action) {
            case 'created':
                $this->onCreated($payload);
                break;

            case 'cancelled':
                $this->onCancelled($payload);
                break;

            case 'edited':
                $this->onEdited($payload);
                break;

            case 'tier_changed':
                $this->onTierChanged($payload);
                break;

            case 'pending_cancellation':
                $this->onPendingCancellation($payload);
                break;

            case 'pending_tier_change':
                $this->onPendingTierChange($payload);
                break;
        }

        $this->responseWriter->setBody($response, [
            'success' => true
        ]);
    }

    private function onCreated(\stdClass $payload)
    {
        $order = $this->newOrder($payload);

        try {
            $this->orderManager->create($order);
        } catch (\Doctrine\DBAL\Exception $e) {
            // name probably already exists
        }
    }

    private function onCancelled(\stdClass $payload)
    {
        $order = $this->newOrder($payload);

        $this->orderManager->cancel($order);
    }

    private function onEdited(\stdClass $payload)
    {
        $order = $this->newOrder($payload);

        $this->orderManager->update($order);
    }

    private function onTierChanged(\stdClass $payload)
    {
    }

    private function onPendingCancellation(\stdClass $payload)
    {
    }

    private function onPendingTierChange(\stdClass $payload)
    {
    }

    private function newOrder(\stdClass $payload): Order
    {
        return new Order(
            $payload->sender->login,
            $payload->sender->avatar_url,
            $payload->sender->html_url,
            $payload->sponsorship->tier->monthly_price_in_cents
        );
    }
}
