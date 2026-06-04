<?php
class SiteController extends Controller {
  public function index(): void {
    $this->renderWithLayout('landing', 'site/index', [
      'pageTitle' => 'Rebirth - Rise From the Ash. Root in Hope.',
    ]);
  }
}
