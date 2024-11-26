<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Api\Endpoint;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use Ectool\AmoCrmBundle\Api\Exception\ApiException;

trait Pipelines
{
    /**
     * @throws ApiException
     */
    public function getPipelines(): array
    {
        try {
            $pipelinesService = $this->apiClient->pipelines();
            $pipelines = $pipelinesService->get();

            return array_map(fn ($pipeline) => $pipeline->toArray(), iterator_to_array($pipelines));
        } catch (AmoCRMApiException|AmoCRMMissedTokenException|AmoCRMoAuthApiException $e) {
            throw new ApiException(message: 'Ошибка получения воронок', previous: $e);
        }
    }
}


