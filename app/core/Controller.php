<?php
class Controller {
  use Middleware;

  protected function render(string $view, array $data = []): string {
    extract($data);
    ob_start();
    require VIEWS_PATH . '/' . $view . '.php';
    return ob_get_clean();
  }

  protected function renderWithLayout(string $layout, string $view, array $data = []): void {
    $content = $this->render($view, $data);
    extract(array_merge($data, ['content' => $content]));
    require VIEWS_PATH . '/layouts/' . $layout . '.php';
  }

  protected function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
  }
}
