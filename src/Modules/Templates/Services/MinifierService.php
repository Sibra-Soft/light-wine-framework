<?php
namespace LightWine\Modules\Templates\Services;

use LightWine\Modules\Templates\Interfaces\IMinifierService;

class MinifierService implements IMinifierService
{
    public function MinifyHtml(string $content): string {
        $search = array(
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        );

        $replace = array('>','<','\\1','');
        $buffer = preg_replace($search, $replace, $content);

        return $buffer;
    }

    public function MinifyStylesheet(string $content): string {
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);

        preg_match_all('/(\'[^\']*?\'|"[^"]*?")/ims', $css, $hit, PREG_PATTERN_ORDER);
        for ($i=0; $i < count($hit[1]); $i++) {
            $css = str_replace($hit[1][$i], '##########' . $i . '##########', $css);
        }

        $css = preg_replace('/;[\s\r\n\t]*?}[\s\r\n\t]*/ims', "}\r\n", $css);
        $css = preg_replace('/;[\s\r\n\t]*?([\r\n]?[^\s\r\n\t])/ims', ';$1', $css);
        $css = preg_replace('/[\s\r\n\t]*:[\s\r\n\t]*?([^\s\r\n\t])/ims', ':$1', $css);
        $css = preg_replace('/[\s\r\n\t]*,[\s\r\n\t]*?([^\s\r\n\t])/ims', ',$1', $css);
        $css = preg_replace('/[\s\r\n\t]*{[\s\r\n\t]*?([^\s\r\n\t])/ims', '{$1', $css);
        $css = preg_replace('/([\d\.]+)[\s\r\n\t]+(px|em|pt|%)/ims', '$1$2', $css);
        $css = preg_replace('/([^\d\.]0)(px|em|pt|%)/ims', '$1', $css);
        $css = preg_replace('/\p{Zs}+/ims',' ', $css);
        $css = str_replace(array("\r\n", "\r", "\n"), '', $css);

        for ($i=0; $i < count($hit[1]); $i++) {
            $css = str_replace('##########' . $i . '##########', $hit[1][$i], $css);
        }

        return $css;
    }

    public function MinifyJavascript(string $content): string {
        return \JShrink\Minifier::minify($content);
    }
}