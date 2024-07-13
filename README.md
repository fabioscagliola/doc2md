# doc2md

A Symfony web API that converts documents to Markdown using the [PHPWord](https://phpoffice.github.io/PHPWord) library

WORK IN PROGRESS

```
symfony server:start
```

```
curl --location 'http://127.0.0.1:8000/conversion' \
--header 'Authorization: Bearer BEsbGdBeSXtQEL3SnZ2GdU5MdyKaUTUv' \
--header 'Content-Type: application/json' \
--data '{
    "contents": null,
    "location": "https://fabioscagliola.com/Hair.docx"
}'
```

