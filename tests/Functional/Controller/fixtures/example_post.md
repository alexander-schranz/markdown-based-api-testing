# Request

```http request
POST /api/examples
Accept: application/json
Content-Type: application/json
X-Auth-Token: e1f4ec0d-54df-465e-8cf3-78dad2ca8463
```

```json
{
    "title": "Test"
}
```

---

# Response

```http request
HTTP/1.1 201 Created
Content-Type: application/json
```

```json
{
    "id": "@integer@",
    "title": "Test"
}
```
