<?php
namespace LightWine\Core;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\RegexBuilder\Services\RegexBuilderService;

class Route
{
    public static $Routes = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "DELETE" => [],
        "PATCH" => []
    ];

    /**
     * This functions generates the matching pattern for urls
     * @param string $url The url to convert to a matching pattern
     * @return string The matching pattern
     */
    private static function GenerateMatchingPattern(string $url): string {
        $parts = explode("/", $url);
        $pattern = RegexBuilderService::Expression()->startOfString("");
        $counter = 0;

        foreach($parts as $part){
            if(preg_match("/(?<=\{).+?(?=\})/", $part, $matches)){
                foreach($matches as $match){
                    $part = str_replace("{".$match."}", RegexBuilderService::Group($match)->raw(".[a-z-A-Z-0-9_.]+"), $part);
                }
            }

            if($counter == count($parts)-1){
                $pattern->raw($part)->endOfString();
            }else{
                $pattern->raw($part)->raw("/");
            }

            $counter++;
        }

        $pattern = str_replace("/", "\/", $pattern);

        return $pattern;
    }

    /**
     * Register a parameter for a route
     * @param string $route The name of the route
     * @param string $name The name of the parameter
     * @param string $type The datatype of the parameter (string, integer, date, time, datetime, boolean, etc.)
     * @param bool $isKey Tells if the parameter is a key
     * @param bool $isRequired Tells if the parameter is required for the request
     */
    public static function RegisterRouteParameter(string $route, string $name, string $type, bool $isKey, bool $isRequired){
        $routeMethod = strtoupper(StringHelpers::SplitString($route, "@", 0));
        $routeName = StringHelpers::SplitString($route, "@", 1);
        $routeArrayIndex = array_search($routeName, array_column(self::$Routes[$routeMethod], 'name'));
        $parameter = ["name" => $name, "type" => $type, "isKey" => ($isKey) ? 1 : 0, "isRequired" => ($isRequired) ? 1 : 0];

        array_push(self::$Routes[$routeMethod][$routeArrayIndex]["parameters"], $parameter);
    }

    /**
     * Register a route for a GET request
     * @param string $name The name of the route
     * @param string $url The url for the route
     * @param string $action The action for the route
     * @param string $middleware The middleware of the route
     * @param array $options Array of data to pass to the handler
     * @param string $domain The domain the url is registered for
     */
    public static function Get(string $name, string $url, string $action, string $middleware, array $options = [], string $domain = "*"){
        array_push(self::$Routes["GET"], [
            "domain" => $domain,
            "name" => $name,
            "url" => $url,
            "action" => $action,
            "middleware" => $middleware,
            "parameters" => [],
            "options" => $options,
            "regex_pattern" => self::GenerateMatchingPattern($url)
        ]);
    }

    /**
     * Register a route for a POST request
     * @param string $name The name of the route
     * @param string $url The url for the route
     * @param string $action The action for the route
     * @param string $middleware The middleware of the route
     * @param array $options Array of data to pass to the handler
     * @param string $domain The domain the url is registered for
     */
    public static function Post(string $name, string $url, string $action, string $middleware, array $options = [], string $domain = "*"){
        array_push(self::$Routes["POST"], [
            "domain" => $domain,
            "name" => $name,
            "url" => $url,
            "action" => $action,
            "middleware" => $middleware,
            "parameters" => [],
            "options" => $options,
            "regex_pattern" => self::GenerateMatchingPattern($url)
        ]);
    }

    /**
     * Register a route for a PUT request
     * @param string $name The name of the route
     * @param string $url The url for the route
     * @param string $action The action for the route
     * @param string $middleware The middleware of the route
     * @param array $options Array of data to pass to the handler
     * @param string $domain The domain the url is registered for
     */
    public static function Put(string $name, string $url, string $action, string $middleware, array $options = [], string $domain = "*"){
        array_push(self::$Routes["PUT"], [
            "domain" => $domain,
            "name" => $name,
            "url" => $url,
            "action" => $action,
            "middleware" => $middleware,
            "parameters" => [],
            "options" => $options,
            "regex_pattern" => self::GenerateMatchingPattern($url)
        ]);
    }

    /**
     * Register a route for a PATCH request
     * @param string $name The name of the route
     * @param string $url The url for the route
     * @param string $action The action for the route
     * @param string $middleware The middleware of the route
     * @param array $options Array of data to pass to the handler
     * @param string $domain The domain the url is registered for
     */
    public static function Patch(string $name, string $url, string $action, string $middleware, array $options = [], string $domain = "*"){
        array_push(self::$Routes["PATCH"], [
            "domain" => $domain,
            "name" => $name,
            "url" => $url,
            "action" => $action,
            "middleware" => $middleware,
            "parameters" => [],
            "options" => $options,
            "regex_pattern" => self::GenerateMatchingPattern($url)
        ]);
    }

    /**
     * Register a route for a DELETE request
     * @param string $name The name of the route
     * @param string $url The url for the route
     * @param string $action The action for the route
     * @param string $middleware The middleware of the route
     * @param array $options Array of data to pass to the handler
     * @param string $domain The domain the url is registered for
     */
    public static function Delete(string $name, string $url, string $action, string $middleware, array $options = [], string $domain = "*"){
        array_push(self::$Routes["DELETE"], [
            "domain" => $domain,
            "name" => $name,
            "url" => $url,
            "action" => $action,
            "middleware" => $middleware,
            "parameters" => [],
            "options" => $options,
            "regex_pattern" => self::GenerateMatchingPattern($url)
        ]);
    }

    /**
     * Creates a redirect route handler
     * @param string $url The url you want to catch
     * @param string $targetLocation The redirect location
     * @param int $type The type of redirect you want to use (301, 302)
     */
    public static function Redirect(string $url, string $targetLocation, int $type = 302, array $options = [], string $domain = "*"){
        array_push(self::$Routes["GET"], [
            "url" => $url,
            "name" => "redirect",
            "action" => $targetLocation,
            "domain" => $domain,
            "parameters" => [],
            "options" => $options,
            "middleware" => "redirect",
            "regex_pattern" => self::GenerateMatchingPattern($url)
        ]);
    }
}