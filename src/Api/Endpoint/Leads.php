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
    private function sendLeadWithOneLinkedContact(
        ?ContactModel $contact = null,
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
        ?int $price = null,
        ?string $leadName = null,
        array $tags = [],
    ): void {
        try {
            $leadsService = $this->apiClient->leads();
        } catch (AmoCRMMissedTokenException $e) {
            throw new ApiException(previous: $e);
        }

        $lead = new LeadModel();
        $lead->setPrice($price);

        if (!empty($leadName)) {
            $lead->setName($leadName);
        }

        if (null !== $contactsCollection) {
            $lead->setContacts(
                $contactsCollection
            );
        }

        if (!empty($tags)) {
            $tagsCollection = new TagsCollection();
            foreach ($tags as $tagName) {
                $tagsCollection->add((new TagModel())->setName($tagName));
            }
            $lead->setTags($tagsCollection);
        }

        $leadsCollection = new LeadsCollection();
        $leadsCollection->add($lead);

        try {
            $leadsService->add($leadsCollection);
        } catch (AmoCRMApiException|AmoCRMoAuthApiException $e) {
            throw new ApiException(previous: $e);
        }
    }
}
