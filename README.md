# amoCRM Bundle

## Установка

Необходимо в composer.json проекта добавить ссылку на репозиторий

```json
{
    "repositories": [
        { "type": "vcs", "url": "git@easycommerce.gitlab.yandexcloud.net:ectool/env/amocrm-bundle.git" }
    ]
}
```

После чего установить пакет
```shell
composer require ectool/amocrm-bundle:dev-master
```

## Настройка

### Добавление переменных окружения
В файле .env вашего проекта добавьте необходимые переменные окружения:
- субдомен вашего аккаунта amoCRM
- долгосрочный токен интеграции
```dotenv
AMOCRM_LONG_LIVED_ACCESS_TOKEN=ваш_долгосрочный_токен_доступа
AMOCRM_ACCOUNT_URL=your_account_base_domain.amocrm.ru
```

### Добавляем сервис клиента amoCRM:

```yaml
# config/services.yaml
parameters:
    amocrm.long_lived_access_token: '%env(AMOCRM_LONG_LIVED_ACCESS_TOKEN)'
    amocrm.account_url: '%env(AMOCRM_ACCOUNT_URL)'
...

services:
    amocrm.api.tg_bot:
        class: Ectool\AmoCrmBundle\Api\Api
        arguments:
            $longLivedAccessToken: '%amocrm.long_lived_access_token%'
            $accountUrl: '%amocrm.account_url%'
        tags:
            - { name: amocrm.api, alias: amocrm_tg }
```