<?php
class Router {
  private array $routes = [];
  private array $patterns = [];

  public function get(string $path, string $handler): void {
    $this->addRoute('GET', $path, $handler);
  }

  public function post(string $path, string $handler): void {
    $this->addRoute('POST', $path, $handler);
  }

  private function addRoute(string $method, string $path, string $handler): void {
    if (str_contains($path, '{')) {
      $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
      $this->patterns[$method][] = ['regex' => '#^' . $regex . '$#', 'handler' => $handler];
    } else {
      $this->routes[$method][$path] = $handler;
    }
  }

  public function dispatch(string $method, string $uri): void {
    $path = '/' . trim(parse_url($uri, PHP_URL_PATH), '/');
    $path = $path === '' ? '/' : $path;

    // Exact match
    if (isset($this->routes[$method][$path])) {
      $handler = $this->routes[$method][$path];
      [$controller, $action] = explode('@', $handler);
      $controller = new $controller();
      call_user_func([$controller, $action]);
      return;
    }

    // Pattern match (route params)
    if (isset($this->patterns[$method])) {
      foreach ($this->patterns[$method] as $pattern) {
        if (preg_match($pattern['regex'], $path, $matches)) {
          $handler = $pattern['handler'];
          [$controller, $action] = explode('@', $handler);
          $controller = new $controller();
          $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
          call_user_func_array([$controller, $action], $params);
          return;
        }
      }
    }

    http_response_code(404);
    echo '<h1>404 - Page Not Found</h1>';
  }
}
