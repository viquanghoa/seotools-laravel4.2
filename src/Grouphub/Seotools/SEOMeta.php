<?php namespace Grouphub\Seotools;

use Grouphub\Seotools\Contracts\MetaTags as MetaTagsContract;
use Illuminate\Config\Repository as Config;

class SEOMeta implements MetaTagsContract
{
    /**
     * The meta title.
     *
     * @var string
     */
    protected $title;

    /**
     * The meta title session.
     *
     * @var string
     */
    protected $title_session;

    /**
     * The meta title session.
     *
     * @var string
     */
    protected $title_default;

    /**
     * The title tag separator.
     *
     * @var array
     */
    protected $title_separator;

    /**
     * The meta description.
     *
     * @var string
     */
    protected $description;

    /**
     * The meta keywords.
     *
     * @var array
     */
    protected $keywords = [];

    /**
     * extra metatags.
     *
     * @var array
     */
    protected $metatags = [];

    /**
     * The canonical URL.
     *
     * @var string
     */
    protected $canonical;

    protected $amphtml;

    /**
     * The prev URL in pagination.
     *
     * @var string
     */
    protected $prev;

    /**
     * The next URL in pagination.
     *
     * @var string
     */
    protected $next;

    /**
     * The alternate languages.
     *
     * @var array
     */
    protected $alternateLanguages = [];

    /**
     * @var Config
     */
    protected $config;

    /**
     * The webmaster tags.
     *
     * @var array
     */
    protected $webmasterTags = [
        'google'   => 'google-site-verification',
        'bing'     => 'msvalidate.01',
        'alexa'    => 'alexaVerifyID',
        'pintrest' => 'p:domain_verify',
        'yandex'   => 'yandex-verification',
    ];

    /**
     * @param array $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Generates meta tags.
     *
     * @return string
     */
    public function generate()
    {
        $this->loadWebMasterTags();

        $title = $this->getTitle();
        $description = $this->getDescription();
        $keywords = $this->getKeywords();
        $metatags = $this->getMetatags();
        $canonical = $this->getCanonical();
        $amphtml = $this->getAmpHtml();
        $prev = $this->getPrev();
        $next = $this->getNext();
        $languages = $this->getAlternateLanguages();

        $html = [];

        if ($title):
            $html[] = "<title>$title</title>";
        endif;

        if ($description):
            $html[] = "<meta name=\"description\" content=\"{$description}\">";
        endif;

        if (!empty($keywords)):
            $keywords = implode(', ', $keywords);
        $html[] = "<meta name=\"keywords\" content=\"{$keywords}\">";
        endif;

        foreach ($metatags as $key => $value):
            $name = $value[0];
        $content = $value[1];

            // if $content is empty jump to nest
            if (empty($content)) {
                continue;
            }

        $html[] = "<meta {$name}=\"{$key}\" content=\"{$content}\">";
        endforeach;

        if ($canonical):
            $html[] = "<link rel=\"canonical\" href=\"{$canonical}\"/>";
        endif;

        if ($amphtml):
            $html[] = "<link rel=\"amphtml\" href=\"{$amphtml}\"/>";
        endif;

        if ($prev):
            $html[] = "<link rel=\"prev\" href=\"{$prev}\"/>";
        endif;

        if ($next):
            $html[] = "<link rel=\"next\" href=\"{$next}\"/>";
        endif;

        foreach ($languages as $lang):
            $html[] = "<link rel=\"alternate\" hreflang=\"{$lang['lang']}\" href=\"{$lang['url']}\"/>";
        endforeach;

        return implode(PHP_EOL, $html);
    }

    /**
     * Sets the title.
     *
     * @param string $title
     * @param bool   $appendDefault
     *
     * @return MetaTagsContract
     */
    public function setTitle($title, $appendDefault = true)
    {
        // clean title
        $title = strip_tags($title);

        // store title session
        $this->title_session = $title;

        // store title
        if (true === $appendDefault) {
            $this->title = $this->parseTitle($title);
        } else {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * Sets the default title tag.
     *
     * @param string $default
     *
     * @return MetaTagsContract
     */
    public function setTitleDefault($default)
    {
        $this->title_default = $default;

        return $this;
    }

    /**
     * Sets the separator for the title tag.
     *
     * @param string $separator
     *
     * @return MetaTagsContract
     */
    public function setTitleSeparator($separator)
    {
        $this->title_separator = $separator;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return MetaTagsContract
     */
    public function setDescription($description)
    {
        // clean and store description
        // if is false, set false
        $this->description = (false == $description) ? $description : strip_tags($description);

        return $this;
    }

    /**
     * Sets the list of keywords, you can send an array or string separated with commas
     * also clears the previously set keywords.
     *
     * @param string|array $keywords
     *
     * @return MetaTagsContract
     */
    public function setKeywords($keywords)
    {
        if (!is_array($keywords)):
            $keywords = explode(', ', $keywords);
        endif;

        // clean keywords
        $keywords = array_map('strip_tags', $keywords);

        // store keywords
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Add a keyword.
     *
     * @param string|array $keyword
     *
     * @return MetaTagsContract
     */
    public function addKeyword($keyword)
    {
        if (is_array($keyword)):
            $this->keywords = array_merge($keyword, $this->keywords); else:
            $this->keywords[] = strip_tags($keyword);
        endif;

        return $this;
    }

    /**
     * Remove a metatag.
     *
     * @param string $key
     *
     * @return MetaTagsContract
     */
    public function removeMeta($key)
    {
        array_forget($this->metatags, $key);

        return $this;
    }

    /**
     * Add a custom meta tag.
     *
     * @param string|array $meta
     * @param string       $value
     * @param string       $name
     *
     * @return MetaTagsContract
     */
    public function addMeta($meta, $value = null, $name = 'name')
    {
        // multiple metas
        if (is_array($meta)):
            foreach ($meta as $key => $value):
                $this->metatags[$key] = [$name, $value];
        endforeach; else:
            $this->metatags[$meta] = [$name, $value];
        endif;

        return $this;
    }

    /**
     * Sets the canonical URL.
     *
     * @param string $url
     *
     * @return MetaTagsContract
     */
    public function setCanonical($url)
    {
        $this->canonical = $url;

        return $this;
    }

    /**
     * Sets the AMP html URL.
     *
     * @param string $url
     *
     * @return MetaTagsContract
     */
    public function setAmpHtml($url)
    {
        $this->amphtml = $url;

        return $this;
    }

    /**
     * Sets the prev URL.
     *
     * @param string $url
     *
     * @return MetaTagsContract
     */
    public function setPrev($url)
    {
        $this->prev = $url;

        return $this;
    }

    /**
     * Sets the next URL.
     *
     * @param string $url
     *
     * @return MetaTagsContract
     */
    public function setNext($url)
    {
        $this->next = $url;

        return $this;
    }

    /**
     * Add an alternate language.
     *
     * @param string $lang language code in ISO 639-1 format
     * @param string $url
     *
     * @return MetaTagsContract
     */
    public function addAlternateLanguage($lang, $url)
    {
        $this->alternateLanguages[] = ['lang' => $lang, 'url' => $url];

        return $this;
    }

    /**
     * Add alternate languages.
     *
     * @param array $langs
     *
     * @return MetaTagsContract
     */
    public function addAlternateLanguages(array $languages)
    {
        $this->alternateLanguages = array_merge($this->alternateLanguages, $languages);

        return $this;
    }

    /**
     * Takes the title formatted for display.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title ?: $this->getDefaultTitle();
    }

    /**
     * Takes the default title.
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        if (empty($this->title_default)) {
            return $this->config->get('meta.defaults.title', null);
        }

        return $this->title_default;
    }

    /**
     * takes the title that was set.
     *
     * @return string
     */
    public function getTitleSession()
    {
        return $this->title_session ?: $this->getTitle();
    }

    /**
     * takes the title that was set.
     *
     * @return string
     */
    public function getTitleSeparator()
    {
        return $this->title_separator ?: $this->config->get('meta.defaults.separator', ' - ');
    }

    /**
     * Get the Meta keywords.
     *
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords ?: $this->config->get('meta.defaults.keywords', []);
    }

    /**
     * Get all metatags.
     *
     * @return array
     */
    public function getMetatags()
    {
        return $this->metatags;
    }

    /**
     * Get the Meta description.
     *
     * @return string
     */
    public function getDescription()
    {
        if (false === $this->description) {
            return;
        }

        return $this->description ?: $this->config->get('meta.defaults.description', null);
    }

    /**
     * Get the canonical URL.
     *
     * @return string
     */
    public function getCanonical()
    {
        return $this->canonical;
    }

    /**
     * Sets the AMP html URL.
     *
     * @param string $url
     *
     * @return MetaTagsContract
     */
    public function getAmpHtml()
    {
        return $this->amphtml;
    }

    /**
     * Get the prev URL.
     *
     * @return string
     */
    public function getPrev()
    {
        return $this->prev;
    }

    /**
     * Get the next URL.
     *
     * @return string
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Get alternate languages.
     *
     * @return array
     */
    public function getAlternateLanguages()
    {
        return $this->alternateLanguages;
    }

    /**
     * Reset all data.
     *
     * @return void
     */
    public function reset()
    {
        $this->description = null;
        $this->title_session = null;
        $this->metatags = [];
        $this->keywords = [];
    }

    /**
     * Get parsed title.
     *
     * @param string $title
     *
     * @return string
     */
    protected function parseTitle($title)
    {
        $default = $this->getDefaultTitle();

        return (empty($default)) ? $title : $title.$this->getTitleSeparator().$default;
    }

    /**
     * Load webmaster tags from configuration.
     */
    protected function loadWebMasterTags()
    {
        foreach ($this->config->get('meta.webmaster_tags', []) as $name => $value) {
            if (!empty($value)) {
                $meta = array_get($this->webmasterTags, $name, $name);
                $this->addMeta($meta, $value);
            }
        }
    }
}
