# Symfony API Base
This is a base application for a REST API in the Symfony framework that I created to speed up development by eliminating the need to repeatedly build the base and user system from scratch.

# Features
The base contains essential utilities and functions for validation, logging, error handling, and the user system.
## API-Documentation
The application is set up with the Nelmio API Doc Bundle for testing endpoints in the browser at ```/api/doc``` and in JSON format at ```/api/doc.json```.

## Error handling
Error handling is managed by the ```handleError``` function in the ```ErrorManager class```, which triggers an exception that is listened to by the ```ExceptionEventSubscriber```. The subscriber logs the exception into the exception log and displays an error response for the user.

## Logging system
For logging, there is the ```LogManager class```, which contains functions for saving and reading logs from the database through the Log entity.

## User system
The user system is managed by the ```UserManager class```, and login works using a JWT token in the authorization header, thanks to Symfony Security and Lexik JWT.

## CLI commands
The application has CLI commands for the LogManager and UserManager, and overall system management through the CLI.

# Request examples
All requests accept input data in JSON format and return JSON data back to the client.
## register request
```
curl -X POST http://localhost/api/auth/register \
    -H "Content-Type: application/json" \
    -d '{
        "email": "test@example.com",
        "first-name": "John",
        "last-name": "Doe",
        "password": "securePassword123"
    }'
```

## login request
```
curl -X POST http://localhost/api/auth/login \
    -H "Content-Type: application/json" \
    -d '{
        "email": "test@test.test",
        "password": "test"
    }'
```

## logout request
```
curl -X POST http://localhost/api/auth/logout \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>"
```

## user info request
```
curl -X POST http://localhost/api/user/info \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>"
```

## update user password request
```
curl -X PATCH http://localhost/api/user/data/update/password\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>" \
    -d '{
        "new-password": "asdfghjkoiuzrewq"
    }'
```

## update user role request
```
curl -X PATCH http://localhost/api/user/data/update/role\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>" \
    -d '{
        "user-id": 1,
        "task": "add",
        "role": "ROLE_TEST"
    }'
```

## update user status request
```
curl -X PATCH http://localhost/api/user/data/update/status\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>" \
    -d '{
        "user-id": 2,
        "status": "idk"
    }'
```

## delete user request
```
curl -X PATCH http://localhost/api/user/delete\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>" \
    -d '{
        "user-id": 3
    }'
```

## get users list request
```
curl -X GET http://localhost/api/user/list\
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer <token>"
```

# License
This software is licensed under the [MIT license](https://github.com/lukasbecvar/api-base/blob/main/LICENSE).
