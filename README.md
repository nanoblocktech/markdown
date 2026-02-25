# PHP Markdown Extension

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![Composer](https://img.shields.io/badge/Composer-Required-brightgreen)](https://getcomposer.org/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

A lightweight PHP extension of [`Parsedown`](https://github.com/erusev/parsedown) that adds:

* Audio and video embedding using markdown syntax
* Automatic Table of Contents generation
* Responsive HTML tables
* External links automatically open in a new tab when the hostname differs


---

## Installation

Installation via composer.

```bash
composer require nanoblocktech/markdown
```

---

## Quick Start

```php
use Luminova\ExtraUtils\HtmlDocuments\Markdown;

$markdown = new Markdown();

echo $markdown->text('### Hello World');
```

---

## Media Embedding

Embedding audio and videos in markdown. 

### Audio

```markdown
{Description}(audio)(/path/to/audio.opus)
```

### Video

```markdown
{Description}(video)(/path/to/video.mp4)
```

> Supports both local and remote URLs.

---

## Configuration

### Enable Features

```php
// Generate table of contents
$markdown->tableOfContents(true);

// Make HTML tables responsive
$markdown->responsiveTable(true);
```

---

### Table of Contents Options

```php
// Include specific heading levels
$markdown->setHeadings(['h1', 'h2']);

// Prefix for generated heading IDs
$markdown->setIdPrefix('my-contents-');

// Retrieve generated table of contents
$tableOfContents = $markdown->getTableOfContents();
```

---

### Link Handling

```php
// Base URL for relative links
$markdown->setLink('https://example.com/assets/');
```

---

### Media Types

```php
$markdown->setMediaType('audio', 'audio/ogg; codecs=opus');
$markdown->setMediaType('video', 'video/mp4');
```

---

## Rendering Markdown

```php
echo $markdown->text($content);
```

---

## Full Example

```php
$markdown = new Markdown();
$markdown->tableOfContents(true);
$markdown->responsiveTable(true);
$markdown->setHeadings(['h1', 'h2']);
$markdown->setIdPrefix('toc-');
$markdown->setLink('https://example.com/assets/');
$markdown->setMediaType('audio', 'audio/ogg; codecs=opus');
$markdown->setMediaType('video', 'video/mp4');

$mdText = <<<MD
# Welcome
Some intro text.

## Audio Example
{Cool song}(audio)(/media/song.opus)

## Video Example
{Demo video}(video)(/media/demo.mp4)
MD;

echo $markdown->text($mdText);
```

---

## Features vs Parsedown

| Feature                            | Parsedown | PHP Markdown Extension |
| ---------------------------------- | --------- | ---------------------- |
| Markdown rendering                 | ✅         | ✅                      |
| Audio embedding                    | ❌         | ✅                      |
| Video embedding                    | ❌         | ✅                      |
| Table of Contents generation       | ❌         | ✅                      |
| Responsive HTML tables             | ❌         | ✅                      |
| Auto external link target="_blank" | ❌         | ✅                      |


---

> **Notes:**
>
> * External links automatically receive `target="_blank"` when the host differs from your application domain.
> * Media embedding works with local paths or remote URLs.
> * Table of contents is generated from configured heading levels only.
