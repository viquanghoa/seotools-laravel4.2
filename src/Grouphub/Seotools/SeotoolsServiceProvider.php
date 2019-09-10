<?php namespace Grouphub\Seotools;  

use Grouphub\Seotools\OpenGraph;
use Grouphub\Seotools\SEOMeta;
use Grouphub\Seotools\SEOTools;
use Grouphub\Seotools\TwitterCards;
use Illuminate\Support\ServiceProvider;

class SEOToolsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * @return void
     */
    public function boot()
    {    
        $this->package('grouphub/seotools');
        // $this->app['config']->package('grouphub/seotools', __DIR__ . '/../../../config');   
        // $this->mergeConfigFrom(
        //     __DIR__.'/../../config/seotools.php', 'seotools'
        // );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {        
        $this->app->singleton('seotools.metatags', function ($app) {
            // return new SEOMeta($app['config']->get('seotools.meta', []));
            return new SEOMeta($app['config']);
        });

        $this->app->singleton('seotools.opengraph', function ($app) {
            return new OpenGraph($app['config']->get('seotools::seotools.opengraph', []));            
        });

        $this->app->singleton('seotools.twitter', function ($app) {
            return new TwitterCards($app['config']->get('seotools::seotools.twitter.defaults', []));           
        });

        $this->app->singleton('seotools', function ($app) {
            return new SEOTools($app);
        });

        $this->app->bind('Grouphub\Seotools\Contracts\MetaTags', 'seotools.metatags');
        $this->app->bind('Grouphub\Seotools\Contracts\OpenGraph', 'seotools.opengraph');
        $this->app->bind('Grouphub\Seotools\Contracts\Twitter', 'seotools.twitter');
        $this->app->bind('Grouphub\Seotools\Contracts\SEOTools', 'seotools');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'Grouphub\Seotools\Contracts\SEOTools',
            'Grouphub\Seotools\Contracts\MetaTags',
            'Grouphub\Seotools\Contracts\Twitter',
            'Grouphub\Seotools\Contracts\OpenGraph',
            'seotools',
            'seotools.metatags',
            'seotools.opengraph',
            'seotools.twitter',
        ];
    }
}
