# Markdown

PHP Markdown is an extension of `Parsedown`, to embed video and audio in markdown formatting.
Additionally, it supports creating a table of contents and automatically inserts `target="_blank"` anchor if links don't match your hostname.

---

```bash 
composer install nanoblocktech/markdown
```

Initialize markdown.

```php
$markdown = new Markdown();
```

### Audio Embedding 

```markdown
{Description}(audio)(/path/to/audio.opus)
```

### Video Embedding 

```markdown
{Description}(video)(/path/to/video.mp4)
```

Configure markdown 

```php
// Enable table of contents
$markdown->tableOfContents(true);

// Enable responsive HTML table
$markdown->responsiveTable(true);

// Set heading to allow in the table of contents
$markdown->setHeadings(['h1', 'h2']);

// Set id prefix for table of contents 
$markdown->setIdPrefix('my-contents-');

// Add a base link to markdown
$markdown->setLink('https://example.com/assets/');

// Set media type
$markdown->setMediaType('audio', 'audio/ogg; codecs=opus');
$markdown->setMediaType('video', 'video/mp4');

// Get table of contents
$arry = $markdown->getTableOfContents();
```

Display your markdown text 

```php
$markdown->text('### Hello');
```
