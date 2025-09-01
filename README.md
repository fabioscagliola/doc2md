# doc2md

A Symfony web API that converts Word documents to Markdown using the [PHPWord](https://phpoffice.github.io/PHPWord) library

## Overview

This project will likely always be a work in progress.
It does not aim at being a complete solution.
This is what I use to convert my own documents, written using the predefined styles in Word, to Markdown.
It currently supports the following elements.
As new needs arise, I will add new features.

- Blockquotes – the name of the paragraph style must be "Quote"
- Bold text within paragraphs
- Headings – based on titles with different depth
- Italic text within paragraphs

## Give it a try

Clone this repo and change to the `doc2md` folder.

```
git clone https://github.com/fabioscagliola/doc2md.git
cd doc2md
```

Run a container based on the official Composer image, mounting the `webapi` folder, and "bash" into it.

```
docker run --name doc2md -it -v ./webapi:/webapi composer bash
```

Change to the `webapi` folder and install the dependencies.

```
cd /webapi
composer install
```

Install the Symfony CLI and add it to your path.

```
curl -sS https://get.symfony.com/cli/installer | bash
export PATH="$HOME/.symfony5/bin:$PATH"
```

Start the server.

```
symfony server:start
```

From another terminal window, "bash" into the container once again.

```
docker exec -it doc2md bash
```

And now you can convert Word documents to Markdown as if there were no tomorrow!

```
cd /webapi
bin/console doc2md /path/to/your/document.docx
```

