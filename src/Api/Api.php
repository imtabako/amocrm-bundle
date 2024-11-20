<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Api;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Exceptions\InvalidArgumentException;
use Ectool\AmoCrmBundle\Api\Endpoint\Contacts;
use Ectool\AmoCrmBundle\Api\Endpoint\Leads;
use Ectool\AmoCrmBundle\Api\Exception\ApiException;
use Ectool\AmoCrmBundle\Model\Contact;

class Api
{
    use Contacts;
    use Leads;

    private AmoCRMApiClient $apiClient;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $longLivedAccessToken,
        private string $accountUrl,
        private ?string $alias = null
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

    /**
     * @throws ApiException
     */
    public function sendLeadWithOneContact(
        Contact $contact,
        ?int $price = null,
        ?string $leadName = null,
        array $tags = [],
    ): void {
        $this->sendLeadWithOneLinkedContact(
            contact: $this->sendContact($contact),
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
            price: $price,
            leadName: $leadName,
            tags: $tags,
        );
    }
}
