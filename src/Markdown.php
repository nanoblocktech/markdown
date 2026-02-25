<?php 
/**
 * Luminova Framework
 *
 * @package Luminova
 * @author Ujah Chigozie Peter
 * @copyright (c) Nanoblock Technology Ltd
 * @license See LICENSE file
*/
namespace Luminova\ExtraUtils\HtmlDocuments;

use \Parsedown;

/**
 * @version "erusev/parsedown": "^1.8.0",
 */
class Markdown extends Parsedown
{
	/**
	 * Enable table of contents
	 * 
	 * @var array $isTableOfContents
	 */
	protected bool $isTableOfContents = false;
	
	/**
	 * Table of contents.
	 * 
	 * @var array $tableOfContents
	 */
	protected array $tableOfContents = [];
	
	/**
	 * Media type.
	 * 
	 * @var array<string,string> $mediaType
	 */
	protected array $mediaType = [
		'audio' => 'audio/ogg; codecs=opus',
		'video' => 'video/mp4'
	];
	
	/**
	 * Hostname link.
	 * 
	 * @var string $hostLink
	 */
	private string $hostLink = '';
	
	/**
	 * Table headings.
	 * 
	 * @var array $tableHeadings
	 */
	private array $tableHeadings = ['h2', 'h3'];
	
	/**
	 * Table of contents id prefix.
	 * 
	 * @var string $tablePrefix
	 */
	private string $tablePrefix = '';
	
	/**
	 * Heading anchor links.
	 * 
	 * @var bool $anchor
	 */
	private bool $anchor = true;
	
	/**
	 * Heading anchor links.
	 * 
	 * @var bool $anchor
	 */
	private bool $responsiveTable = false;
	
	/**
	 * Extended markdown formatting.
	 * 
	 * @var array $extended
	 */
	private static array $extended = [
		'table',
		'media',
		'a',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6'
	];

	/**
	 * Optional attributes for anchor links.
	 * 
	 * @var array $linkAttributes
	 */
	private array $linkAttributes = [];

	/**
	 * Initialize Markdown.
	 */
	public function __construct()
	{
		$this->setMarkupEscaped(true);
		$this->setUrlsLinked(false);
		$this->BlockTypes["{"] = ['Media'];
		$this->InlineTypes["{"] = ['Media'];
	}

	/**
	 * Set host link.
	 * 
	 * @param string $link The based host URL.
	 * 
	 * @return self
	 */
	public function setLink(string $link): self
	{
		$this->hostLink = $link;
		
		return $this;
	}

	/**
	 * Set a default attribute for anchor links.
	 * 
	 * @param array<string,string> $attributes The attributes for anchor links.
	 * 
	 * @return self
	 */
	public function setLinkAttributes(array $attributes): self
	{
		$this->linkAttributes = $attributes;
		
		return $this;
	}

	/**
	 * Set id prefix for table of contents.
	 * 
	 * @param string $prefix The id prefix for table of contents.
	 * 
	 * @return self
	 */
	public function setIdPrefix(string $prefix): self
	{
		$this->tablePrefix = $prefix;
		
		return $this;
	}

	/**
	 * Set table of contents heading.
	 * 
	 * @param array $headings The supported headings for table of contents.
	 * 
	 * @return self
	 */
	public function setHeadings(array $headings): self
	{
		$this->tableHeadings = $headings;
		
		return $this;
	}
	
	/**
	 * Enable heading anchor link.
	 * 
	 * @param bool $anchors The enable/disable of heading anchor link.
	 * 
	 * @return self
	 */
	public function headingAnchor(bool $anchors = true): self
	{
		$this->anchor = $anchors;
		
		return $this;
	}

	/**
	 * Enable responsive table.
	 * 
	 * @param bool $responsive The enable/disable of responsive table.
	 * 
	 * @return self
	 */
	public function responsiveTable(bool $responsive = true): self
	{
		$this->responsiveTable = $responsive;
		
		return $this;
	}

	/**
	 * Set if tables of contents should be enabled.
	 * 
	 * @param bool $enable The enable/disable of table of contents.
	 * 
	 * @return self
	 */
	public function tableOfContents(bool $enable = true): self 
	{
		$this->isTableOfContents = $enable;
		
		return $this;
	}

	/**
	 * Set media type
	 * 
	 * @param string $context The media context type (e.g, `audio`, `video`).
     * @param string $type The attribute of media type (e.g, `audio/ogg; codecs=opus`).
	 * 
	 * @return self
	 */
	public function setMediaType(string $context, string $type): self 
	{
		$this->mediaType[$context] = $type;
		
		return $this;
	}

	/**
	 * Get table of contents.
	 * 
	 * @return array<string,string> Return table of content.
	 */
	public function getTableOfContents(): array 
	{
		return $this->tableOfContents;
	}
	
	/**
	 * Add attribute to table of contents.
	 * 
	 * @param array $Element
	 * @param string|array $text
	 * 
	 * @return array
	 */
	protected function addAttribute(array $Element, ?string $text): array
	{
		if(!$text || !$this->isTableOfContents){
			return ['', null, false, ''];
		}

		$eleName = $Element['name'] ?? null;

		if(!$eleName || !in_array($eleName, $this->tableHeadings)){
			return ['', null, false, ''];
		}

		$attr = '';
		$id = null;
		$headings = false;
		$text = htmlspecialchars(strip_tags($text), ENT_QUOTES, 'UTF-8');

		if(isset($Element['attr'])){
		    $id = $this->tablePrefix . $this->toKebabCase($text);
		    $this->tableOfContents[$id] = self::toName($text);

		    $attr =  ' ' . $Element['attr'] . '="' . $id . '" tabindex="0"';
		    $headings = true;
		}
		
		return [$attr, $id, $headings, $text];
	}

	/**
	 * Parse a Markdown text node into its final HTML string.
	 *
	 * Resolution order:
	 * 1. If the element contains a nested `elements` array, they are rendered
	 *    recursively via $this->elements() and returned.
	 * 2. If the element contains a single `element`, it is rendered via
	 *    $this->element() and returned.
	 * 3. If no child elements exist and raw HTML is NOT permitted, the provided
	 *    text is escaped to prevent HTML injection.
	 * 4. Otherwise, the raw text is returned as-is.
	 *
	 * This method acts as the final stage for text node rendering, ensuring
	 * consistent handling of nested structures and safe output when raw HTML
	 * is disallowed.
	 *
	 * @param array       $Element        Parsed Markdown element definition.
	 * @param string|null $text           Raw text content for the element.
	 * @param bool        $permitRawHtml  Whether raw HTML output is allowed.
	 *
	 * @return string Rendered HTML-safe string output.
	 */
	protected function parseText(array $Element, ?string $text, bool $permitRawHtml): string
	{
		if (isset($Element['elements']))
		{
			return $this->elements($Element['elements']);
		}

		if (isset($Element['element']))
		{
			return $this->element($Element['element']);
		}

		if (!$permitRawHtml)
		{
			return self::escape($text, true);
		}
	
		return (string) $text;
	}

	/**
	 * Add attribute to table of contents.
	 * 
	 * @param array $Line
	 * @param ?array $Block
	 * 
	 * @return array
	 */
	protected function blockMedia(array $Line, ?array $Block = null): array 
	{
		$result = [];
		if (isset($Block) && !isset($Block['type']) && !isset($Block['interrupted']))
		{
		    return $result;
		}
		
		if (preg_match('/^(\s*)\{(.+?)\}\((.+?)\)\((\S+?)\)$/', $Line['body'], $matches)){
		    
		    $description = $matches[2];
		    $type = $matches[3];
		    $link = $this->hostLink . $matches[4];
		    $typeLower = strtolower($type);
		    $id = $this->toKebabCase(basename($link));
		    $sanitizeLink =  htmlspecialchars($link);
		    $isVideo = ($typeLower === 'video');
		
		    $html = '<figure class="media-player">';
		    $html .= ($isVideo ? '<video controls id="'.$id.'">' : '<audio controls id="'.$id.'">'); 
		    $html .= '<source src="' . $sanitizeLink . '" type="' . $this->mediaType[$typeLower] . '">';
		    $html .= 'Your browser does not support the '.$typeLower.' element.';
		    $html .= ' <a href="' . $sanitizeLink . '">Download '.$typeLower.'</a>';
		    $html .= ($isVideo ? '</video>' : '</audio>'); 
		    $html .= '<figcaption>' . htmlspecialchars($description) . ' (' . htmlspecialchars($type) . ')</figcaption>';
		    $html .= '</figure>';
		
		    $result = ['markup' => $html];
		}
		
		return $result;
	}

	/**
	 * Build element.
	 * 
	 * @param array $Element
	 * 
	 * @return string
	 */
	protected function element(array $Element)
    {
		$eleName = $Element['name'] ?? null;

		if(
			!$eleName ||
			($eleName === 'table' && !$this->responsiveTable)
			|| !in_array($eleName, self::$extended)
		){
		    return parent::element($Element);
		}

        if ($this->safeMode)
        {
            $Element = $this->sanitiseElement($Element);
        }

        $Element = $this->handle($Element);
        $hasName = isset($Element['name']);

        $markup = '';

        if ($hasName)
        {
			$eleName = $Element['name'];
			if($eleName === 'table'){
				$class = $Element['attributes']['class'] ?? '';
				$Element['attributes']['class'] = 'table' . ($class ? ' ' : '') . $class;

				$markup .= '<div class="table-responsive"><table';
			}elseif($eleName === 'a' && $this->linkAttributes){
				$markup .= '<' . $eleName;
				$Element['attributes'] = array_merge(
					$Element['attributes'], 
					$this->linkAttributes
				);
			}else{
				$markup .= '<' . $eleName;
			}

            if (isset($Element['attributes']))
            {
                foreach ($Element['attributes'] as $name => $value)
                {
                    if ($value === null)
                    {
                        continue;
                    }

					$value = self::escape($value);

					if($eleName === 'a'){
						if ($name === 'href' && !filter_var($value , FILTER_VALIDATE_URL)) {
							$markup .= ' ' . $name . '="' . $this->hostLink . '/' . ltrim($value, '/') . '"';
							continue;
						}
						
						$target = str_starts_with($value, $this->hostLink) 
							?:' target="_blank" rel="noopener noreferrer"';
						
						$markup .= ' ' . $name . '="' . $value . '"' . $target;
						continue;
					}

					$markup .= " $name=\"".$value.'"';
                }
            }
        }

        $permitRawHtml = false;

        if (isset($Element['text']))
        {
            $text = $Element['text'];
        }
        // very strongly consider an alternative if you're writing an
        // extension
        elseif (isset($Element['rawHtml']))
        {
            $text = $Element['rawHtml'];

            $allowRawHtmlInSafeMode = isset($Element['allowRawHtmlInSafeMode']) 
				&& $Element['allowRawHtmlInSafeMode'];

            $permitRawHtml = !$this->safeMode || $allowRawHtmlInSafeMode;
        }

        $hasContent = isset($text) || isset($Element['element']) || isset($Element['elements']);

        if ($hasContent)
        {
			$text = $this->parseText(
				$Element,
				$text ?? null,
				$permitRawHtml
			);

		    [$attr, $id, $headings, $description] = $this->addAttribute($Element, $text);

            $markup .= $hasName ? $attr . '>' : '';
			$markup .= $text;

			if($this->anchor && $headings){
				$markup .= '<a 
					class="anchor-link" href="#' . $id . '" 
					title="Permalink to ' . $description . ' section" 
					aria-label="Permalink to ' . $description . ' section"
				></a>'; 
		    }

            $markup .= $hasName ? '</' . $Element['name'] . '>' : '';
        }
        elseif ($hasName)
        {
            $markup .= ' />';
        }

		$markup .= ($hasName && $Element['name'] === 'table') ? '</div>' : '';

        return $markup;
    }

	/**
	 * Create a block header.
	 *
	 * @param array $Line 
	 * 
	 * @return array $block
	 */
	protected function blockHeader($Line)
	{
		$block = parent::blockHeader($Line);
		
		if (isset($block['element'])){
		    $block['element']['attr'] = 'id';
		}
		
		return $block;
	}

	/**
	 * Convert a string to kebab-case.
	 *
	 * @param string $string The input string to convert.
	 * @return string The kebab-cased string.
	 */
	public function toKebabCase(string $string): string
	{
		$string = preg_replace(
			'/[^\p{L}\p{N}]+/u', '-', 
			str_replace(['(', ')', '`', '::', '\\', $this->tablePrefix], ['', '', '', ' ', ' ', ''], $string)
		);
		return mb_strtolower(trim($string, '-'));
	}

	/**
	 * Extract a valid name from text, stripping optional backticks inside brackets.
	 *
	 * @param string $text The input string to process.
	 * @return string The normalized name string.
	 */
	private static function toName(string $text): string
	{
		$text = str_replace(['\\', '::'], ' ', $text);
		return preg_match('/\[(?:`)?([^`\]]+)(?:`)?\]/', $text, $matches)
			? $matches[1]
			: $text;
	}
}
