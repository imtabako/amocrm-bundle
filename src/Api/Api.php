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
use Psr\Log\LoggerInterface;

class Api
{
    use Contacts;
    use Leads;

    private const AMOCRM_ACCOUNT_URL = "yurieasycomm.amocrm.ru";
    private const AMOCRM_LONG_LIVED_ACCESS_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImI0NmRhNjZjY2RmYTU3ZWI0YzI3OGU4NzE3Zjc0ODEwMDNhNmE1MDc5YTI0NjI2ZjZmMDQ2YTAzYjY1YjYwODM4NTYwM2UyM2FjYTk3YjZhIn0.eyJhdWQiOiI2NDQyN2M5NS0yZGY1LTRkNzEtYjc1Ny01MGQxY2YxZjczZjUiLCJqdGkiOiJiNDZkYTY2Y2NkZmE1N2ViNGMyNzhlODcxN2Y3NDgxMDAzYTZhNTA3OWEyNDYyNmY2ZjA0NmEwM2I2NWI2MDgzODU2MDNlMjNhY2E5N2I2YSIsImlhdCI6MTczMjQ5NzI5NywibmJmIjoxNzMyNDk3Mjk3LCJleHAiOjE3NTA4MDk2MDAsInN1YiI6IjExODA1ODk0IiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMyMDgwNDA2LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiOTczZTMwNGEtYWU3Ny00ZTViLWI3ZmItM2JmOWJlNzQ5ZmQ0IiwiYXBpX2RvbWFpbiI6ImFwaS1iLmFtb2NybS5ydSJ9.j6VUfqXMWm4WJ-j-pxNfdpsREoc3hfy6d7oTmhD_plS7oZaYllOFSjzIkTl4XnaljuC-BWKC8fwtlN6-BjHxpp57irUfYz1EgyN1l8xj9OrSkcNXdu6gxR4wSIEjwWxmGfGHk_pnbQ0uDKls-mKylc933RnddwU1R7k4hZhNobFJAwZt3a_NzfXK5mJvHJJRRWcY4lSrEmt1wP_5LmgD47d4JQzVgnbK_R71eRd4_LdEVL1oKm4kjLshje2Dj4zC7kVMv3yqFTPtHWDMCDvVwAyqYXdINxelcEhCE7kZNzwHSmxTNPb6p0Zv-fw9LqzOiRrUwsVklHkVWUSyGDfA3w";

    public AmoCRMApiClient $apiClient;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $longLivedAccessToken,
        private string $accountUrl,
        private ?string $alias = null,
    ) {
//        if (!str_contains($longLivedAccessToken, '.')) {
//        }
//        file_put_contents('/tmp/debug_token.log', $longLivedAccessToken.PHP_EOL, FILE_APPEND);
//        error_log($this->longLivedAccessToken);
//        dump($this->longLivedAccessToken);

//        throw new \InvalidArgumentException('Invalid long-lived access token: "'.$longLivedAccessToken.'"');
        $this->apiClient = new AmoCRMApiClient();

        $this->logger->info(self::AMOCRM_ACCOUNT_URL);
        $this->logger->info(self::AMOCRM_LONG_LIVED_ACCESS_TOKEN);
        $token = new LongLivedAccessToken(self::AMOCRM_LONG_LIVED_ACCESS_TOKEN);
        $this->apiClient
            ->setAccessToken($token)
            ->setAccountBaseDomain(self::AMOCRM_ACCOUNT_URL)
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

    public function getLongLivedAccessToken(): string
    {
        return $this->longLivedAccessToken;
    }

    public function setLongLivedAccessToken(string $longLivedAccessToken): static
    {
        $this->longLivedAccessToken = $longLivedAccessToken;

        return $this;
    }

    public function getAccountUrl(): string
    {
        return $this->accountUrl;
    }

    public function setAccountUrl(string $accountUrl): static
    {
        $this->accountUrl = $accountUrl;

        return $this;
    }
}
