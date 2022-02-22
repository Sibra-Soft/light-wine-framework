<?php
namespace LightWine\Modules\Templates\Interfaces;

interface IMinifierService
{
    /**
     * This function will minify the specified html content
     * @param string $content The HTML content you want to minify
     * @return string The minified content
     */
    public function MinifyHtml(string $content): string;

    /**
     * This function will minify the specified CSS content
     * @param string $input The CSS content you want to minify
     * @return string The minified content
     */
    public function MinifyStylesheet(string $content): string;

    /**
     * This function will minify the specified Javascript content
     * @param string $content The Javascript content you want to minify
     * @return string The minified content
     */
    public function MinifyJavascript(string $content): string;
}
