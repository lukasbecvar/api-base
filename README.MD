# Symfony API Base
This is a base application for a REST API in the Symfony framework that I created to speed up development by eliminating the need to repeatedly build the base and user system from scratch.

# Request examples
All requests accept input data in JSON format and return JSON data back to the client.
## register request
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "first-name": "John",
    "last-name": "Doe",
    "password": "securePassword123"
  }'

## login request
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@test.test",
    "password": "test"
  }'

## logout request
curl -X POST http://localhost/api/auth/logout \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>"

## user info request
curl -X POST http://localhost/api/user/info \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>"

## update user password request
curl -X PATCH http://localhost/api/user/data/update/password\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>" \
    -d '{
        "new-password": "asdfghjkoiuzrewq"
    }'

## update user role request
curl -X PATCH http://localhost/api/user/data/update/role\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>" \
    -d '{
        "user-id": 1,
        "task": "add",
        "role": "ROLE_TEST"
    }'

## update user status request
curl -X PATCH http://localhost/api/user/data/update/status\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>" \
    -d '{
        "user-id": 2,
        "status": "idk"
    }'

## delete user request
curl -X PATCH http://localhost/api/user/delete\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>" \
    -d '{
        "user-id": 3
    }'

## get users list request
curl -X GET http://localhost/api/user/list\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>"

# License
This software is licensed under the [MIT license](https://github.com/lukasbecvar/api-base/blob/main/LICENSE).
