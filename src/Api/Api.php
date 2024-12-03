<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Api;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\TagModel;
use Ectool\AmoCrmBundle\Api\Endpoint\Contacts;
use Ectool\AmoCrmBundle\Api\Endpoint\Leads;
use Ectool\AmoCrmBundle\Api\Endpoint\Pipelines;
use Ectool\AmoCrmBundle\Api\Endpoint\Unsorted;
use Ectool\AmoCrmBundle\Api\Exception\ApiException;
use Ectool\AmoCrmBundle\Model\Contact;

class Api
{
    use Contacts;
    use Leads;
    use Pipelines;
    use Unsorted;

    private AmoCRMApiClient $apiClient;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $longLivedAccessToken,
        private string $accountUrl,
        private ?string $alias = null,
        private ?int $defaultPipelineId = null,
        private ?int $defaultStatusId = null,
    ) {
        $this->apiClient = new AmoCRMApiClient();

        $token = new LongLivedAccessToken($longLivedAccessToken);
        $this->apiClient
            ->setAccessToken($token)
            ->setAccountBaseDomain($accountUrl)
        ;
    }

    public function setAlias(string $alias): static
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getApiClient(): AmoCRMApiClient
    {
        return $this->apiClient;
    }

    public function setDefaultPipelineId(?int $defaultPipelineId): static
    {
        $this->defaultPipelineId = $defaultPipelineId;

        return $this;
    }

    public function getDefaultPipelineId(): ?int
    {
        return $this->defaultPipelineId;
    }

    public function setDefaultStatusId(?int $defaultStatusId): static
    {
        $this->defaultStatusId = $defaultStatusId;

        return $this;
    }

    public function getDefaultStatusId(): ?int
    {
        return $this->defaultStatusId;
    }

    /**
     * @throws ApiException
     */
    public function sendLeadWithOneContact(
        Contact $contact,
        ?int $pipelineId = null,
        ?int $statusId = null,
        ?int $price = null,
        ?string $leadName = null,
        array $tags = [],
    ): void {
        $this->sendLeadWithOneLinkedContact(
            contact: $this->sendContact($contact),
            pipelineId: $pipelineId ?? $this->defaultPipelineId,
            statusId: $statusId ?? $this->defaultStatusId,
            price: $price,
            leadName: $leadName,
            tags: $tags,
        );
    }

    /**
     * @param Contact[] $contacts
     *
     * @throws ApiException
     */
    public function sendLeadWithContacts(
        array $contacts = [],
        ?int $pipelineId = null,
        ?int $statusId = null,
        ?int $price = null,
        ?string $leadName = null,
        array $tags = [],
    ): void {
        if (!empty($contacts)) {
            $contactsCollection = new ContactsCollection();
        } else {
            $contactsCollection = null;
        }

        foreach ($contacts as $contact) {
            $contactModel = $this->sendContact($contact);
            $contactsCollection->add($contactModel);
        }

        $this->sendLeadWithLinkedContacts(
            contactsCollection: $contactsCollection,
            pipelineId: $pipelineId ?? $this->defaultPipelineId,
            statusId: $statusId ?? $this->defaultStatusId,
            price: $price,
            leadName: $leadName,
            tags: $tags,
        );
    }

    /**
     * @throws ApiException
     */
    public function sendUnsortedLeadWithOneContact(
        Contact $contact,
        array $metadata,
        ?int $pipelineId = null,
        ?int $statusId = null,
        ?int $price = null,
        ?string $leadName = null,
        array $tags = [],
    ) {
        $sourceName = 'jopa_source_name';
        $sourceUid = 'jopa_source_uid';
        $formName = 'jopa_form_name';

        $contactsCollection = new ContactsCollection();
        $contactModel = $this->sendContact($contact);
        $contactsCollection->add($contactModel);

//        try {
//            $leadsService = $this->apiClient->leads();
//        } catch (AmoCRMMissedTokenException $e) {
//            throw new ApiException(previous: $e);
//        }
//
//        $lead = new LeadModel();
//        $lead->setPrice($price);
//
//        if (!empty($leadName)) {
//            $lead->setName($leadName);
//        }
//
//        if (null !== $contactsCollection) {
//            $lead->setContacts(
//                $contactsCollection
//            );
//        }
//
//        if (null !== $pipelineId) {
//            $lead->setPipelineId($pipelineId);
//        }
//
////        if (null !== $statusId) {
////            $lead->setStatusId($statusId);
////        }
//
//        if (!empty($tags)) {
//            $tagsCollection = new TagsCollection();
//            foreach ($tags as $tagName) {
//                $tagsCollection->add((new TagModel())->setName($tagName));
//            }
//            $lead->setTags($tagsCollection);
//        }

        $lead = $this->createLead(
            contactsCollection: $contactsCollection,
            pipelineId: $pipelineId,
            statusId: $statusId,
            price: $price,
            leadName: $leadName,
            tags: $tags
        );

        $this->sendToUnsorted(
            sourceName: $sourceName,
            sourceUid: $sourceUid,
            metadata: $metadata,
            pipeline_id: $pipelineId,
            lead: $lead,
            contacts: $contactsCollection,
        );
    }
}
