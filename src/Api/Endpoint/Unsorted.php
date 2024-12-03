<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Api\Endpoint;

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\Leads\Unsorted\FormsUnsortedCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\BadTypeException;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\Unsorted\FormsMetadata;
use AmoCRM\Models\Unsorted\FormUnsortedModel;
use DateTime;
use Ectool\AmoCrmBundle\Api\Exception\ApiException;

trait Unsorted
{
    /**
     * @throws ApiException
     */
    private function sendToUnsorted(
        string $sourceName,
        string $sourceUid,
        string $formName,
        int $pipeline_id,
        LeadModel $lead,
        ?ContactsCollection $contacts = null,
        array $metadata = []
    ): void {
        try {
            $unsortedService = $this->apiClient->unsorted();
        } catch (AmoCRMMissedTokenException $e) {
            throw new ApiException(previous: $e);
        }

        $formsUnsortedCollection = new FormsUnsortedCollection();
        $formUnsorted = new FormUnsortedModel();
        $formMetadata = new FormsMetadata();
        $now = time();
        $formMetadata
            ->setFormId('my_best_form')
            ->setFormName('Обратная связь')
            ->setFormPage('https://example.com/form')
            ->setFormSentAt($now)
            ->setReferer('https://google.com/search')
            ->setIp('192.168.0.1');

        try {
            $formUnsorted->setSourceName($sourceName)
                ->setSourceUid($sourceUid)
                ->setCreatedAt($now)
                ->setMetadata($formMetadata)
                ->setLead($lead)
                ->setPipelineId($pipeline_id)
                ->setContacts($contacts);

        } catch (BadTypeException $e) {
            throw new ApiException(previous: $e);
        }

        $formsUnsortedCollection->add($formUnsorted);

        try {
            $formsUnsortedCollection = $unsortedService->add($formsUnsortedCollection);
        } catch (AmoCRMoAuthApiException|AmoCRMApiException $e) {
            throw new ApiException(message: 'Ошибка отправки лида в Неразобранное', previous: $e);
        }
    }
}