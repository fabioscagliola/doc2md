# doc2md

A Symfony web API that converts Word documents to Markdown using the [PHPWord](https://phpoffice.github.io/PHPWord) library

## Overview

This project will likely always be a work in progress.
It does not aim at being a complete solution.
This is what I use to convert my own documents, written using the predefined styles in Word, to Markdown.
It currently supports the following elements.
As new needs arise, I will add new features.

- Headings – based on titles with different depth
- Blockquotes – the name of the paragraph style must be "Quote"
- Bold text within paragraphs
- Italic text within paragraphs

I plan to containerize it when I have time. For now, I am running it locally.

If you wish to try it, assuming you have already set up Symfony as explained [here](https://symfony.com/doc/current/setup.html), you can follow these steps:

## Local setup

Clone this repo,

```
git clone https://github.com/fabioscagliola/doc2md.git
```

change to the **webapi** folder and install the dependencies,

```
cd webapi
composer install
```

start the server,

```
symfony server:start
```

and, in another terminal, run the following command.

```
bin/console doc2md /path/to/your/document.docx
```

## Docker setup

Coming soon. Hopefully.

