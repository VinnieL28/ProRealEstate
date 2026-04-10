# Public Lead Capture API

## Overview

This API allows external landing pages, contact forms, and third-party integrations to submit leads directly into the CRM.

**Base URL:** `https://your-domain.com/api`

---

## Endpoints

### POST /leads

Capture a new lead from an external source. The lead is automatically assigned to an available agent via round-robin based on current open lead count.

#### Rate Limiting

- **10 requests per minute per IP address**
- Exceeding the limit returns `HTTP 429 Too Many Requests`
- The `Retry-After` header indicates how many seconds to wait

#### Request Headers

| Header | Value | Required |
|---|---|---|
| `Content-Type` | `application/json` | Yes |
| `Accept` | `application/json` | Yes |

#### Request Body

| Field | Type | Required | Description |
|---|---|---|---|
| `name` | string (max 255) | Yes | Full name of the lead |
| `phone` | string (max 50) | Yes | Phone number (any format) |
| `email` | string (email, max 255) | No | Email address |
| `source` | string | No | Lead source (e.g. `website`, `facebook`, `referral`) |
| `property_interest` | string | No | Property type or address the lead is interested in |
| `notes` | string | No | Additional notes or message from the lead |
| `team_id` | integer | Yes | The CRM team ID to assign the lead to |

#### Success Response

**HTTP 201 Created**

```json
{
    "message": "Lead captured successfully.",
    "lead_id": 42,
    "assigned_to": "Jane Smith"
}
```

#### Error Responses

**HTTP 422 Unprocessable Entity** — Validation failed

```json
{
    "message": "The name field is required.",
    "errors": {
        "name": ["The name field is required."],
        "team_id": ["The team id field is required."]
    }
}
```

**HTTP 429 Too Many Requests** — Rate limit exceeded

```json
{
    "message": "Too Many Attempts."
}
```

---

## Example Requests

### cURL

```bash
curl -X POST https://your-domain.com/api/leads \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "phone": "555-123-4567",
    "email": "john@example.com",
    "source": "website",
    "property_interest": "3BR house in Austin",
    "notes": "Looking to buy within 3 months",
    "team_id": 1
  }'
```

### JavaScript (fetch)

```javascript
const response = await fetch('https://your-domain.com/api/leads', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    name: 'John Doe',
    phone: '555-123-4567',
    email: 'john@example.com',
    source: 'website',
    property_interest: '3BR house in Austin',
    notes: 'Looking to buy within 3 months',
    team_id: 1,
  }),
});

const data = await response.json();
console.log(data); // { message: "Lead captured successfully.", lead_id: 42, assigned_to: "Jane Smith" }
```

### PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->post('https://your-domain.com/api/leads', [
    'json' => [
        'name'              => 'John Doe',
        'phone'             => '555-123-4567',
        'email'             => 'john@example.com',
        'source'            => 'website',
        'property_interest' => '3BR house in Austin',
        'notes'             => 'Looking to buy within 3 months',
        'team_id'           => 1,
    ],
]);

$body = json_decode($response->getBody(), true);
```

---

## Lead Assignment Logic

When a lead is submitted:

1. All active agents on the specified team are ranked by their current open lead count (ascending).
2. The agent with the fewest open leads is assigned the new lead (round-robin by workload).
3. If no agents exist on the team, the lead is created unassigned.
4. A `NewLeadAssigned` in-app notification is sent to the assigned agent.

---

## Notes

- All leads created via this endpoint have their `source` field recorded as provided (or `null` if omitted).
- The `team_id` must correspond to an existing team in the CRM. Invalid team IDs will result in the lead being created with the provided ID, which may fail silently — validate this on your end.
- This endpoint does **not** require authentication. Protect it by keeping your `team_id` private or using an allowlist at the server/CDN layer if needed.
