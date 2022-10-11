<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Locale
        setlocale(LC_TIME, config('locale'), config('fallback_locale'));

        //
        Blade::directive('tabtitle', function($title = '') {
            return tab_title($title);
        });
        Blade::directive('ifnull', function($expression) {
            // get first parameter which is the variable to check
            $params = explode(',', $expression);
            if(strpos($params[0], '??') === FALSE) {
                $params[0] = $params[0].' ?? null';
            }

            $expression = implode(',', $params);
            return "<?php echo value_or_empty($expression); ?>";
        });
        // alias for call_user_func and its array variant
        Blade::directive('cuf', function($expression) {
            return "<?php echo e(call_user_func({$expression})); ?>";
        });
        Blade::directive('_cuf', function($expression) {
            // unescaped
            return "<?php echo call_user_func({$expression}); ?>";
        });
        Blade::directive('cufa', function($expression) {
            return "<?php echo e(call_user_func_array({$expression})); ?>";
        });
        Blade::directive('_cufa', function($expression) {
            // unescaped
            return "<?php echo call_user_func_array({$expression}); ?>";
        });
        // shortcut
        Blade::directive('empty_text', function($expression) {
            return "<?php echo empty_text({$expression}); ?>";
        });
        // shortcut
        Blade::directive('voe', function($expression) {
            return "<?php echo voe({$expression}); ?>";
        });
        Blade::directive('von', function($expression) {
            return "<?php echo von({$expression}); ?>";
        });
        Blade::directive('vo_', function($expression) {
            return "<?php echo vo_({$expression}); ?>";
        });
        Blade::directive('puser', function($expression) {
            return "<?php echo pretty_username({$expression}); ?>";
        });
        Blade::directive('nl2br', function($expression) {
            return "<?php echo nl2br({$expression}); ?>";
        });
        Blade::directive('langraw', function($expression) {
            return "<?php echo lang_or_raw({$expression}); ?>";
        });
    }
}
