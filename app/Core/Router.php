<?php

namespace App\Core;

class Router //Niet gelijk aan router zoals gegeven in fase 4. Verwijder router totdat je een project hebt dat je helemaal snapt en dat werkt.
{
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * Register a route for GET requests
     * 
     * @param string $uri
     * @param string $controller
     * @param string $action
     * @return void
     */
    public function get($uri, $controller, $action)
    {
        $this->routes['GET'][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Register a route for POST requests
     * 
     * @param string $uri
     * @param string $controller
     * @param string $action
     * @return void
     */
    public function post($uri, $controller, $action)
    {
        $this->routes['POST'][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Register a route for PUT requests
     * 
     * @param string $uri
     * @param string $controller
     * @param string $action
     * @return void
     */
    public function put($uri, $controller, $action)
    {
        $this->routes['PUT'][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Register a route for DELETE requests
     * 
     * @param string $uri
     * @param string $controller
     * @param string $action
     * @return void
     */
    public function delete($uri, $controller, $action)
    {
        $this->routes['DELETE'][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Load routes from a file
     * 
     * @param string $file
     * @return void
     */
    public function load($file)
    {
        require $file;
    }    /**
     * Match the current request to a route
     * 
     * @param string $uri
     * @param string $method
     * @return array|boolean
     */
    public function match($uri, $method)
    {
        // Handle HEAD requests by treating them as GET requests
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        
        // Ensure the method exists in our routes array
        if (!array_key_exists($method, $this->routes)) {
            return false;
        }
        
        if (array_key_exists($uri, $this->routes[$method])) {
            return $this->routes[$method][$uri];
        }

        // Check for dynamic routes with parameters
        foreach ($this->routes[$method] as $route => $params) {
            // Convert route to regex pattern
            $pattern = '@^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?<$1>[^/]+)', $route) . '$@';
            
            if (preg_match($pattern, $uri, $matches)) {
                $params['params'] = array_filter($matches, function ($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                
                return $params;
            }
        }
        
        return false;
    }

    /**
     * Dispatch the request to the appropriate controller action
     * 
     * @param string $uri
     * @param string $method
     * @return void
     */    public function dispatch($uri, $method)
    {
        // Remove query strings
        $uri = $this->removeQueryStringVariables($uri);
        
        // DEBUG: Log what we're trying to match
        error_log("ROUTER DEBUG: Trying to match URI: '{$uri}' with method: '{$method}'");
        
        // Match the route
        $route = $this->match($uri, $method);
        
        if ($route) {
            error_log("ROUTER DEBUG: Route found - Controller: {$route['controller']}, Action: {$route['action']}");
            
            $controller = "App\\Controllers\\{$route['controller']}";
            $action = $route['action'];
            $params = $route['params'] ?? [];
            
            error_log("ROUTER DEBUG: Full controller class: {$controller}");
            error_log("ROUTER DEBUG: Params: " . json_encode($params));
            
            if (class_exists($controller)) {
                error_log("ROUTER DEBUG: Controller class exists");
                $controller_instance = new $controller();
                
                if (method_exists($controller_instance, $action)) {
                    error_log("ROUTER DEBUG: Action method exists, calling with params: " . json_encode(array_values($params)));
                    call_user_func_array([$controller_instance, $action], array_values($params));
                    return;
                } else {
                    error_log("ROUTER DEBUG: Action method does NOT exist");
                }
            } else {
                error_log("ROUTER DEBUG: Controller class does NOT exist");
            }
        } else {
            error_log("ROUTER DEBUG: No route found for URI: '{$uri}'");
        }
        
        // Route not found
        header('HTTP/1.1 404 Not Found');
        echo '404 Page Not Found';
    }

    /**
     * Remove query string variables from the URL
     * 
     * @param string $uri
     * @return string
     */
    protected function removeQueryStringVariables($uri)
    {
        if ($uri != '') {
            $parts = explode('?', $uri, 2);
            
            if (strpos($parts[0], '=') === false) {
                $uri = $parts[0];
            } else {
                $uri = '';
            }
        }
        
        return $uri;
    }
}