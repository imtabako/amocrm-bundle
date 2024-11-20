<?php

declare(strict_types=1);

namespace Ectool\AmoCrmBundle\Api\Endpoint;

use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use Ectool\AmoCrmBundle\Api\Exception\ApiException;
use Ectool\AmoCrmBundle\Enum\FieldCodeEnum;
use Ectool\AmoCrmBundle\Enum\PhoneValueEnum;
use Ectool\AmoCrmBundle\Model\Contact;

trait Contacts
{
    /**
     * @throws ApiException
     */
    public function sendContactRaw(
        string $phoneNumber,
        PhoneValueEnum $phoneType = PhoneValueEnum::WORK,
        ?string $name = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
    ): ContactModel {
        try {
            $contactsService = $this->apiClient->contacts();
        } catch (AmoCRMMissedTokenException $e) {
            throw new ApiException(previous: $e);
        }

        $contact = $this->buildContactModel(
            phoneNumber: $phoneNumber,
            phoneType: $phoneType,
            name: $name,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
        );

        try {
            $contact = $contactsService->addOne($contact);
        } catch (AmoCRMApiException|AmoCRMoAuthApiException $e) {
            throw new ApiException(previous: $e);
        }

        return $contact;
    }

    /**
     * @throws ApiException
     */
    public function sendContact(
        Contact $contact
    ): ContactModel {
        return $this->sendContactRaw(
            $contact->phoneNumber,
            $contact->phoneType,
            $contact->name,
            $contact->firstName,
            $contact->lastName,
            $contact->email
        );
    }

    private function buildContactModel(
        string $phoneNumber,
        PhoneValueEnum $phoneType = PhoneValueEnum::WORK,
        ?string $name = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
    ): ContactModel {
        $contact = new ContactModel();

        $contact->setName($name)
            ->setFirstName($firstName)
            ->setLastName($lastName)
        ;

        $customFields = new CustomFieldsValuesCollection();

        $phoneField = new MultitextCustomFieldValuesModel();
        $phoneField->setFieldCode(FieldCodeEnum::PHONE->value);

        $phoneValues = new MultitextCustomFieldValueCollection();
        $phoneValues->add(
            (new MultitextCustomFieldValueModel())
                ->setValue($phoneNumber)
                ->setEnum($phoneType->value)
        );

        $phoneField->setValues($phoneValues);
        $customFields->add($phoneField);

        if (!empty($email)) {
            $emailField = new MultitextCustomFieldValuesModel();
            $emailField->setFieldCode(FieldCodeEnum::EMAIL->value);

            $emailValues = new MultitextCustomFieldValueCollection();
            $emailValues->add(
                (new MultitextCustomFieldValueModel())
                    ->setValue($email)
            );

            $emailField->setValues($emailValues);
            $customFields->add($emailField);
        }

        $contact->setCustomFieldsValues($customFields);

        return $contact;
    }
}
