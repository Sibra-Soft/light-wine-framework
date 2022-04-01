<?php
namespace LightWine\Core\Helpers;

class HttpContextHelpers
{
    /**
     * Minifies the specified html content
     * @param string $content The content that must be minified
     * @return string The minified content
     */
    public static function MinifyHtml(string $content): string {
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

    /**
     * Minifies the specified stylesheet content
     * @param string $content The content that must be minified
     * @return string The minified content
     */
    public static function MinifyStylesheet(string $content): string {
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

    /**
     * Minifies the specified Javascript content
     * @param string $content The content that must be minified
     * @return string The minified content
     */
    public static function MinifyJavascript(string $content): string {
        return \JShrink\Minifier::minify($content);
    }

    /**
     * Logoff the current user
     */
    public static function Logoff(){
        unset($_SESSION["Checksum"]);
        header("location: /");
    }
}