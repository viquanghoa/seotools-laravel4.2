<?php namespace Grouphub\Seotools\Traits;

use Grouphub\Seotools\Contracts\SEOFriendly;

trait SEOTools
{
    /**
     * @return \Grouphub\Seotools\Contracts\SEOTools
     */
    protected function seo()
    {
        return app('seotools');
    }

    /**
     * @param SEOFriendly $friendly
     *
     * @return \Grouphub\Seotools\Contracts\SEOTools
     */
    protected function loadSEO(SEOFriendly $friendly)
    {
        $SEO = $this->seo();

        $friendly->loadSEO($SEO);

        return $SEO;
    }
}
