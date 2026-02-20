
# Alcurad API

A lightweight PHP-based API for managing documents, users, and languages through structured POST requests.

---

## Overview

Alcurad API is designed to handle content management via a simple request system. It supports document handling, user management, and multilingual content through a unified endpoint.

* Document management (CRUD)
* User management
* Language support
* Flexible authentication system

---

## Endpoint

```
https://tor.ntigskovde.se/php/alcuradapi.php
```

Only POST requests are accepted.
GET requests will return:

```json
{"error":"Only POST requests are allowed."}
```

---

## Authentication

Each request requires identification. You can authenticate using either:

### Option 1: userId + password

```json
{
  "userId": "YourUserId",
  "password": "YourPassword"
}
```

### Option 2: API key

```json
{
  "key": "YourKey"
}
```

### Dev Access (for testing)

| Field    | Value    |
| -------- | -------- |
| userId   | devGuest |
| password | Dev      |
| key      | devG     |

---

## Request Structure

Every request must include:

```json
{
  "request": "YourAction"
}
```

---

## Available Requests

### Documents

* getDocument
* listDocuments
* addDocument
* updateDocument
* removeDocument

### Languages

* listLanguages
* addLanguage
* removeLanguage

### Users

* listUsers
* addUser
* removeUser

---

## Example: Get Document

```json
{
  "userId": "username",
  "key": "devG",
  "request": "getDocument",
  "requestedPage": "homepage",
  "lang": "en"
}
```

### Parameters

| Field         | Description           |
| ------------- | --------------------- |
| userId        | Owner of the document |
| key/password  | Authentication        |
| request       | Action to perform     |
| requestedPage | Target document       |
| lang          | Language version      |

---

## Project Structure

```
AlcuradApi/
│
├── index.php
├── php/
│   ├── alcuradapi.php
│   └── apidocumentation.php
│
├── css/
├── oldcontent/
├── content.ini
└── Documentation.md
```

---

## Requirements

* PHP 7 or higher
* Web server (Apache or Nginx)
* Support for POST requests

---

## Notes

* All actions are controlled via the `request` field
* Authentication is required for most operations
* The API is designed to be simple and flexible rather than strictly REST-based

---

## Testing

You can test the API using tools like Postman, cURL, or a custom frontend.

Example using cURL:

```bash
curl -X POST https://tor.ntigskovde.se/php/alcuradapi.php \
-H "Content-Type: application/json" \
-d '{"key":"devG","request":"listDocuments"}'
```

---

## Future Improvements

* Token-based authentication (JWT)
* Rate limiting
* Improved error handling
* RESTful structure
* Database integration instead of `.ini`


