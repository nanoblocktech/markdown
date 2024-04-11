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
 * "erusev/parsedown": "^1.7",
*/

class Markdown extends Parsedown
{
    /**
     * Enable table of contents
     * @var array $isTableOfContents
    */
    protected bool $isTableOfContents = false;

    /**
     * @var array $tableOfContents Table of contents
    */
    protected array $tableOfContents = [];

    /**
     * @var string $mediaType Media type
    */
    protected string $mediaType = "audio/ogg; codecs=opus";

    /**
     * @var string $hostLink Hostname link
    */
    private string $hostLink = '';

    /**
     * @var array $tableHeadings table headings
    */
    private array $tableHeadings = ["h2", "h3"];

    /**
     * Table of contents id prefix 
     * 
     * @var string $tablePrefix
    */
    private string $tablePrefix = '';

    /**
     * Initialize Markdown
    */
    public function __construct()
    {
        $this->setMarkupEscaped(true);
        $this->setUrlsLinked(false);
        $this->BlockTypes["{"] = ['Media'];
        $this->InlineTypes["{"] = ['Media'];
    }

    /**
     * Set host link 
     * 
     * @param string $link
     * 
     * @return self
    */
    public function setLink(string $link): self
    {
        $this->hostLink = $link;

        return $this;
    }

     /**
     * Set id prefix for table of contents 
     * 
     * @param string $prefix
     * 
     * @return self
    */
    public function setIdPrefix(string $prefix): self
    {
        $this->tablePrefix = $prefix;

        return $this;
    }

    /**
     * Set table of content heading 
     * 
     * @param array $headings
     * 
     * @return self
    */
    public function setHeadings(array $headings): self
    {
        $this->tableHeadings = $headings;

        return $this;
    }

    /**
     * Set if tables of contents should be enabled
     * 
     * @param bool $enable
     * 
     * @return self
    */
    public function tableOfContents(bool $enable): self 
    {
        $this->isTableOfContents = $enable;

        return $this;
    }

    /**
     * Set media type
     * 
     * @param string $type
     * 
     * @return self
    */
    public function setMediaType(string $type): self 
    {
        $this->mediaType = $type;
        
        return $this;
    }

    /**
     * Get table of contents
     * 
     * @return array<String, String> $this->tableOfContents
    */
    public function getTableOfContents(): array 
    {
        return $this->tableOfContents;
    }

    /**
     * Add attribute to table of contents
     * 
     * @param array $Element
     * @param string|array $text
     * 
     * @return string
    */
    protected function addAttribute(array $Element, string|array $text): string
    {
        $attr = '';
        if(is_array($text)){
            $text = $text['text'] ?? '';
        }

        if($this->isTableOfContents && in_array($Element['name'], $this->tableHeadings) && isset($Element['attr'])){
            $attrValue = self::toKebabCase($text);
            $this->tableOfContents[$this->tablePrefix . $attrValue] = $text;
            $attr =  ' ' . $Element['attr'] . '="' . $this->tablePrefix . $attrValue . '"';
        }

        return $attr;
    }

    /**
     * Add attribute to table of contents
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
            $id = self::toKebabCase(basename($link));
            $sanitizeLink =  htmlspecialchars($link);
            $isVideo = ($typeLower === 'video');

            $html = '<figure class="media-player">';
            $html .= ($isVideo ? '<video controls id="'.$id.'">' : '<audio controls id="'.$id.'">'); 
            $html .= '<source src="' . $sanitizeLink . '" type="' . $this->mediaType . '">';
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
     * Build element
     * 
     * @param array $Element
     * 
     * @return string
    */
    protected function element(array $Element)
    {
        if ($this->safeMode){
            $Element = $this->sanitiseElement($Element);
        }

        $markup = '<'.$Element['name'];

        if (isset($Element['attributes'])){
            foreach ($Element['attributes'] as $name => $value){
                if ($value === null){
                    continue;
                }

                if ($Element['name'] === 'a' && !filter_var($link = self::escape($value), FILTER_VALIDATE_URL)) {
                    $markup .= ' ' . $name . '="' . $this->hostLink . '/' . ltrim($link, '/') . '"';
                } else {
                    $markup .= ' ' . $name . '="' . self::escape($value) . '"';
                }
            }
        }

        $permitRawHtml = false;

        if (isset($Element['text'])){
            $text = $Element['text'];
        }
        // very strongly consider an alternative if you're writing an
        // extension
        elseif (isset($Element['rawHtml'])){
            $text = $Element['rawHtml'];
            $allowRawHtmlInSafeMode = isset($Element['allowRawHtmlInSafeMode']) && $Element['allowRawHtmlInSafeMode'];
            $permitRawHtml = !$this->safeMode || $allowRawHtmlInSafeMode;
        }

        if (isset($text)){
            $markup .= $this->addAttribute($Element, $text);
            $markup .= '>';

            if (!isset($Element['nonNestables'])){
                $Element['nonNestables'] = [];
            }

            if (isset($Element['handler'])){
                $markup .= $this->{$Element['handler']}($text, $Element['nonNestables']);
            }elseif (!$permitRawHtml){
                $markup .= self::escape($text, true);
            }else{
                $markup .= $text;
            }

            $markup .= '</'.$Element['name'].'>';
        } else{
            $markup .= ' />';
        }

        return $markup;
    }

    /**
	 * Create block header
	 *
	 * @param array $Line 
     * 
	 * @return array $block
	 */
    protected function blockHeader($Line)
    {
        $block = parent::blockHeader($Line);

        if (isset($block['element']))
        {
            $block['element']['attr'] = 'id';
        }

        return $block;
    }

    /**
	 * Convert a string to kebab case.
	 *
	 * @param string $string The input string to convert.
	 * @return string The kebab-cased string.
	 */
	public static function toKebabCase(string $string): string
	{
		$string = str_replace([' ', ':', '.', ',', '-'], '', $string);
		$kebabCase = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $string);

		return strtolower($kebabCase);
	}

}
