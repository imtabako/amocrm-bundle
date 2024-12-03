<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Api\Endpoint;

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\TagModel;
use Ectool\AmoCrmBundle\Api\Exception\ApiException;

trait Leads
{
    /**
     * @throws ApiException
     */
    public function getLeads(): array
    {
        try {
            $leadsService = $this->apiClient->leads();
            $leads = $leadsService->get();

            return array_map(fn ($lead) => $lead->toArray(), iterator_to_array($leads));
        } catch (AmoCRMApiException|AmoCRMMissedTokenException|AmoCRMoAuthApiException $e) {
            throw new ApiException(previous: $e);
        }
    }

    private function createLead(
        ?ContactsCollection $contactsCollection = null,
        ?int $pipelineId = null,
        ?int $statusId = null,
        ?int $price = null,
        ?string $leadName = null,
        array $tags = []
    ): LeadModel {
        $lead = new LeadModel();
        $lead->setPrice($price);

        if (!empty($leadName)) {
            $lead->setName($leadName);
        }

        if (null !== $contactsCollection) {
            $lead->setContacts($contactsCollection);
        }

        if (null !== $pipelineId) {
            $lead->setPipelineId($pipelineId);
        }

        if (null !== $statusId) {
            $lead->setStatusId($statusId);
        }

        if (!empty($tags)) {
            $tagsCollection = new TagsCollection();
            foreach ($tags as $tagName) {
                $tagsCollection->add((new TagModel())->setName($tagName));
            }
            $lead->setTags($tagsCollection);
        }

        return $lead;
    }

    /**
     * @throws ApiException
     */
    private function sendLeadWithOneLinkedContact(
        ?ContactModel $contact = null,
        ?int $pipelineId = null,
        ?int $statusId = null,
        ?int $price = null,
        ?string $leadName = null,
        array $tags = [],
    ): void {
        if (null !== $contact) {
            $contactsCollection = (new ContactsCollection())
                ->add($contact)
            ;
        } else {
            $contactsCollection = null;
        }

        $this->sendLeadWithLinkedContacts(
            contactsCollection: $contactsCollection,
            pipelineId: $pipelineId,
            statusId: $statusId,
            price: $price,
            leadName: $leadName,
            tags: $tags
        );
    }

    /**
     * @throws ApiException
     */
    private function sendLeadWithLinkedContacts(
        ?ContactsCollection $contactsCollection = null,
        ?int $pipelineId = null,
        ?int $statusId = null,
        ?int $price = null,
        ?string $leadName = null,
        array $tags = [],
    ): void {
        try {
            $leadsService = $this->apiClient->leads();
        } catch (AmoCRMMissedTokenException $e) {
            throw new ApiException(previous: $e);
        }

        $lead = $this->createLead(
            contactsCollection: $contactsCollection,
            pipelineId: $pipelineId,
            statusId: $statusId,
            price: $price,
            leadName: $leadName,
            tags: $tags
        );

        $leadsCollection = new LeadsCollection();
        $leadsCollection->add($lead);

        try {
            $leadsService->add($leadsCollection);
        } catch (AmoCRMApiException|AmoCRMoAuthApiException $e) {
            throw new ApiException(previous: $e);
        }
    }
}
