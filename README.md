# markdown

```bash 
composer install nanoblocktech/markdown
```

```php
$markdown = new Markdown();

$markdown->text('### Hello');
$markdown->enableTableOfContents(true);
$markdown->setHeadings(['h1', 'h2']);
$arry = $markdown->getTableOfContents();
```
