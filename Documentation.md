# Alcurad API

## Endpoints
[http://localhost:8080/AlcuradApi/php/alcuradapi.php](http://localhost:8080/AlcuradApi/php/alcuradapi.php)

Change `localhost:8080` to the adress

Do Only accept `POST` and will only give back `{"error":"Only POST requests are allowed."}` if given a `GET` request. This is done so you can send multiple diffrent `values`. They are as follow;


### UserId

```json
{
    "userId": "YourUserId" 
}
```
`userId` is used to know which user is doing the action and is needed in every `POST` if you want to do an action that do not require a `userId` you can use the `userId` `devGuest`.

### Password

```json
{
    "password": "YourPassword" 
}
```
`Password` similarly to `userId` is used to know who you are and if you have access to some of the features. It is also needed in every `POST` but if you want an action that doesnt require a `userId` or `password` you use the `password` `Dev`.

### Key

```json
{
    "key": "YourKey"
}
```
`key` is the last thing in the identification progress instead of using a `userId` and a `password` you can use your `key`. `key` is given when creating an account if you do something that doesnt require a account use `key` `devG` and you willbe let through.

# Request
```json
{
    "request":"YourAction"
}
```
When you want to do something you call a request there is multiple diffrent request who i will list below;

- `getDocument`
- `listDocuments`
- `updateDocument`
- `addDocument`
- `removeDocument`
- `listLanguages`
- `addLanguage`
- `removeLanguage`
- `removeUser`
- `listUsers`
- `addUser`

All do have their own documentation below.

## Documents / `requestedPage`

```json
{
    "requestedPage":"PageAreYouAfter"
}
```
`requestedPage` is used to know which specific document you are after.

### Get a specific document

```json
{
    "userId":"WhoseDocument",
    "key":"DevG", 
    "request":"getDocument", 
    "requestedPage":"WhichPageAreYouAfter",
    "lang":"WhatLanguageVersion"
}
```
Here are a few `values` to keep track of first is `userId` who in this case is whose document are you after. Then we have `key` which is not for the moment not neasesary. Then we have the `request` which is in this case `getDocument` who will give you back the document. Then we have `requestedPage` which is which dokument from the `userId` you are after then we have `lang` that if you keep empty will give you all back else will give only chosen language.

### List documents
```json
{
    "userId":"WhoseDocuments",
    "key":"DevG", 
    "request":"listDocuments", 
    "lang":"WhatLanguageVersions"
}
```
To keep it simple, if you leave `userId` empty you will get all documents saved else you will get the `userId`s documents. Then we have `key` which is not for the moment not neasesary. The `request` is `listDocuments` which will list all documents depending on the other values. `lang` is language if you leave it empty it will give all othervise only the chosen language.

### Update a Document
```json
{
    "userId":"UsersUserId",
    "password":"usersPassword",
    "key":"usersKey", 
    "request":"updateDocument",
    "requestedPage":"WhichUserDocument", 
    "lang":"WhatLanguageVersion"
}
```
To update a document you need premission by using the `userId` and `password` or only their `key` othervise you will not be able to open the document/change the document. You also need to specifiy which by its `requestedPage` id/name and you can give the entire with all `lang`s or you can also chose a specific `lang` to update. And the `request` need to be `updateDocument`.


### Add a document
```json
{
    "userId":"UsersUserId",
    "password":"usersPassword",
    "key":"usersKey", 
    "request":"addDocument",
    "requestedPage":"documentName"
}
```
To add a document you need to do the `request` `addDocument`. When making a new document you need to have a `userId` and a `password` to know which user it belonges to or just use the `key`. `requestedPage` will be the document name and id. THIS WILL ONLY ADD AN EMPTY DOCUMENT WITHOUT ANY CONTENT AND WITH THE ORIGINAL eng AND sve.

### removeDocument
```json
{
    "userId":"UsersUserId",
    "password":"usersPassword",
    "key":"usersKey", 
    "request":"removeDocument",
    "requestedPage":"WhichUserDocument", 
    "lang":"WhatLanguageVersion"
}
```
When removing a document there is two types removing all of it or just a specific language version. You have to use `request` `removeDocument` either way. Then you chose which page using the `requestedPage` `value` and then if you want all keep `lang` empty else if you write a language it will only remove that one if it exists.

## Language
```json
{
    "lang": "language"
}
```
Language is here shortned to `lang` and the language value shall be a three letter code as sve for sweden and eng for english and so on.

### List of Languages
```json
{
    "userId":"WhoseLanguages",
    "key":"DevG", 
    "request":"listLanguages", 
    "lang":"who has this language"
}
```
When listing languages (`lang`) you have multiple diffrent results. If you want to get who has the language you write only the specific `lang` else if you want which languages some has then you only write `userId` else if you want to se if `userId` has specific `lang` you write both and get a `bool` value back else if you leave both empty you get just all languages anyone has. Here you do not need `key` but it is preffered. And you use the `request` `listLanguages`.

### Add a Language to a userId
```json
{
    "userId":"usersUserId",
    "password":"userPassword",
    "key":"DevG", 
    "request":"addLanguage", 
    "lang":"WhatLanguagecode"
}
```
When adding a language (`lang`) you have to use a `userId` and `password` or only users `key`. Why is becuase you can only add a `lang` to a person and it will fall into the existing languages. But on the users profile they will only have the languages they have chosen. You need to do the `request` `addLanguage` to add a language to a profile and you can only add one at the time. And `lang` will be the language code you have to write a 3 letter long language code such as sve or eng.

### Remove a Language from a userId
```json
{
    "userId":"usersUserId",
    "password":"userPassword",
    "key":"DevG", 
    "request":"removeLanguage", 
    "lang":"WhatLanguagecode"
}
```
When removing a language (`lang`) you have to use a `userId` and `password` or only users `key`. Why is becuase you can only remove a `lang` from a person and if it was alone it will get removed from all the existing languages. You need to do the `request` `removeLanguage` to remove a language from a profile and you can only remove one at the time.

## User / `userId`

```json
{
    "userId": "YourUserId" 
}
```
`userId` is used to know which user is doing the action and is needed in every `POST` if you want to do an action that do not require a `userId` you can use the `userId` `devGuest`.

### List all Users
```json
{
    "userId":"DoesUserExist",
    "key":"DevG", 
    "request":"listUsers"
}
```
When listing users you can chose to either to check if a user exists by using a `userId` and you will get back a `bool` else if you do not include or leave it empty it will list every `userId` by name.

### Add a new User
```json
{
    "userId":"usersUserId",
    "password":"usersPassword",
    "request":"addUser"
}
```
You want a new profile/user, if that is the case you'll need a `request` with `addUser`. Then it will need a `password` and `userId`. In return you'll get a success message and a `key`.

### Remove a User
```json
{
    "userId":"usersUserId",
    "password":"usersPassword",
    "key":"DevG", 
    "request":"removeUser"
}
```
To remove a profile/user by the `API` you'll need to include the profiles `userId`, `password` and `key`. And to do this you'll need to do a `removeUser` `request`.