<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Api\Endpoint;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\Leads\Pipelines\PipelineModel;
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

            $result = [];
            /** @var PipelineModel $pipeline */
            foreach ($pipelines as $pipeline) {
                $result[] = $pipeline->toArray();
            }

            return $result;
        } catch (AmoCRMApiException|AmoCRMMissedTokenException|AmoCRMoAuthApiException $e) {
            throw new ApiException(message: 'Ошибка получения воронок', previous: $e);
        }
    }
}


