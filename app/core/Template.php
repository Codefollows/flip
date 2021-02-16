<?php
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;
use Twig\TwigFilter;
use \Twig\TwigFunction;
use \Twig\Environment;

class Template extends FilesystemLoader {

    public function __construct($paths = [], $rootPath = null) {
        parent::__construct($paths, $rootPath);
    }

    private $cache_enabled = true;

    /**
     * @param $path
     * @return TemplateWrapper|null
     */
    public function load($path) {
        $public_dir = web_root.'public/';

        try {
            $twig = new Environment($this, !$this->cache_enabled ? [] : ['cache' => 'app/cache']);

            $twig->addFunction(new TwigFunction('time', function () {
                return time();
            }));

            $twig->addFunction(new TwigFunction('url', function ($string, $internal = true) {
                return $internal ? web_root . $string : $string;
            }));

            $twig->addFunction(new TwigFunction('css', 
                function ($string) { return $this->getCss($string); }, 
                [ 
                    'is_safe' => ['html']
                ]
            ));

            $twig->addFunction(new TwigFunction('js', 
                function ($string) { return $this->getJs($string); }, 
                [ 
                    'is_safe' => ['html']
                ]
            ));

            $twig->addFunction(new TwigFunction('title', 
                function ($string) {return '<title>' . $string . '</title>'; }, 
                [ 
                    'is_safe' => ['html']
                ]
            ));

            $twig->addFunction(new TwigFunction('constant', function ($string) {
                return constant($string);
            }));

            $twig->addFunction(new TwigFunction('curdate', function ($string) {
                return date($string);
            }));

            $twig->addFunction(new TwigFunction('debugArr', function ($string) {
                return json_encode($string, JSON_PRETTY_PRINT);
            }));


            $twig->addFunction(new TwigFunction('json_decode', function ($string) {
                return json_decode($string, true);
            }));

            $twig->addFunction(new TwigFunction('in_array', function ($needle, $haystack) {
                return in_array($needle, $haystack);
            }));

            $twig->addFunction(new TwigFunction('friendlyTitle', function ($title) {
                return Functions::friendlyTitle($title);
            }));
            
            $twig->addFilter(new TwigFilter('array_chunk', function($array, $limit) {
                return array_chunk($array, $limit);
            }));

            return $twig->load($path . '.twig');
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            return null;
        }
    }

    public function setCacheEnabled($val) {
        $this->cache_enabled = $val;
    }

    private function getCss($string) {
        return '<link rel="stylesheet" type="text/css" href="'.web_root.'public/css/' . $string . '">';
    }

    private function getjs($string) {
        return '<script type="text/javascript" src="'.web_root.'public/js/' . $string . '"></script>';
    }
}