# Todo API (Laravel 12 + Sanctum)

## Run locally

```bash
php artisan serve
```

The API is available at `http://127.0.0.1:8000`.

## Postman setup

1) Import the collection: `postman/TodoAPI.postman_collection.json`
2) Import the environment: `postman/TodoAPI.postman_environment.json`
3) Select the environment in Postman (Todo API - Local)

### Auth flow (auto-store token)

- Run **Auth > Register** or **Auth > Login**
- The test script stores `data.token` into the `token` environment variable

### Auth endpoints

- POST `{{base_url}}/api/auth/register`
- POST `{{base_url}}/api/auth/login`
- POST `{{base_url}}/api/auth/logout` (Authorization: `Bearer {{token}}`)

## Todo endpoints

All Todo endpoints require `Authorization: Bearer {{token}}`.

- GET `{{base_url}}/api/todos`
- POST `{{base_url}}/api/todos`
- GET `{{base_url}}/api/todos/:id`
- PUT `{{base_url}}/api/todos/:id`
- PATCH `{{base_url}}/api/todos/:id`
- DELETE `{{base_url}}/api/todos/:id`

### Example JSON bodies

Create:

```json
{
  "title": "Finish report",
  "description": "Draft v1",
  "completed": false,
  "due_at": "2030-01-01 10:00:00"
}
```

Update (PUT):

```json
{
  "title": "Updated title",
  "description": "Updated description",
  "completed": true,
  "due_at": "2030-01-02 10:00:00"
}
```

Update (PATCH):

```json
{
  "completed": true
}
```
