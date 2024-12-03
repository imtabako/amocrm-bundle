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
use Ectool\AmoCrmBundle\Api\Exception\ApiException;

trait Unsorted
{
    /**
     * @throws ApiException
     */
    private function sendToUnsorted(
        string $sourceName,
        string $sourceUid,
        array $metadata,
        int $pipelineId,
        LeadModel $lead,
        ?ContactsCollection $contacts = null,
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
            ->setFormId($metadata['form_id'])
            ->setFormName($metadata['form_name'])
            ->setFormPage($metadata['form_page'])
            ->setFormSentAt($metadata['form_sent_at'])
            ->setReferer($metadata['referer'])
            ->setIp($metadata['ip'])
        ;

        try {
            $formUnsorted->setSourceName($sourceName)
                ->setSourceUid($sourceUid)
                ->setCreatedAt($now)
                ->setMetadata($formMetadata)
                ->setLead($lead)
                ->setPipelineId($pipelineId)
                ->setContacts($contacts)
            ;
        } catch (BadTypeException $e) {
            throw new ApiException(previous: $e);
        }

        $formsUnsortedCollection->add($formUnsorted);

        try {
            $formsUnsortedCollection = $unsortedService->add($formsUnsortedCollection);
        } catch (AmoCRMApiException|AmoCRMoAuthApiException $e) {
            throw new ApiException(message: 'Ошибка отправки лида в Неразобранное', previous: $e);
        }
    }
}
