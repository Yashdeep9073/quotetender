Yes — but **don’t put the real `TOKEN` or `SECRET_KEY` inside documentation you send around**. Give the developer placeholders in the document and send the actual credentials separately through a secure channel.

Use this credentials section:

```text
QuoteTender API Credentials

Base URL:
https://YOUR-DOMAIN.com

TOKEN:
<PROVIDED_SEPARATELY>

SECRET_KEY:
<PROVIDED_SEPARATELY>

Authentication:
HMAC-SHA256

Signature generation:

timestamp = current UNIX timestamp

signature = HMAC_SHA256(
    TOKEN + timestamp,
    SECRET_KEY
)

Every API request must include:

token=<TOKEN>
ts=<TIMESTAMP>
sig=<SIGNATURE>
```

Developer PHP example:

```php
<?php

$serverUrl = "https://YOUR-DOMAIN.com";

$token = getenv("QUOTETENDER_API_TOKEN");
$secretKey = getenv("QUOTETENDER_SECRET_KEY");

$timestamp = time();

$signature = hash_hmac(
    'sha256',
    $token . $timestamp,
    $secretKey
);

$query = http_build_query([
    'token' => $token,
    'ts'    => $timestamp,
    'sig'   => $signature,
]);

$staffApi = $serverUrl . "/login/api/staff.php?" . $query;
$customersApi = $serverUrl . "/login/api/customers.php?" . $query;
$rolesApi = $serverUrl . "/login/api/roles.php?" . $query;
$permissionsApi = $serverUrl . "/login/api/permissions.php?" . $query;
$awardTendersApi = $serverUrl . "/login/api/awardTenders.php?" . $query;
```

And give them these API names:

| API | Endpoint |
|---|---|
| Staff/Admin | `/login/api/staff.php` |
| Customers/Members | `/login/api/customers.php` |
| Roles | `/login/api/roles.php` |
| Permissions | `/login/api/permissions.php` |
| Award (Orders) Tenders | `/login/api/awardTenders.php` |

**Important:** the developer needs the **secret key**, because they must generate a fresh `ts` and `sig` for each request. Don't send them one generated URL and expect it to work permanently—the timestamp/signature expires.

If you want the documentation to contain your **actual base URL and credential variable names**, send me the base URL plus the names you want to use. **Don't paste the real secret here**; I can create the final `.docx` with placeholders like `<TOKEN PROVIDED PRIVATELY>` and `<SECRET PROVIDED PRIVATELY>`.