<?php
class App {
  private Router $router;

  public function __construct() {
    $this->router = new Router();
    $this->registerRoutes();
  }

  private function registerRoutes(): void {
    // Landing
    $this->router->get('/', 'SiteController@index');

    // Auth
    $this->router->get('/login', 'AuthController@login');
    $this->router->post('/login', 'AuthController@authenticate');
    $this->router->get('/signup', 'AuthController@signup');
    $this->router->post('/signup', 'AuthController@register');
    $this->router->get('/logout', 'AuthController@logout');
    $this->router->post('/logout', 'AuthController@logout');

    // Panel - GET
    $this->router->get('/panel/dashboard', 'PanelController@dashboard');
    $this->router->get('/panel/checkin', 'PanelController@checkin');
    $this->router->get('/panel/journal', 'PanelController@journal');
    $this->router->get('/panel/community', 'PanelController@community');
    $this->router->get('/panel/messages', 'PanelController@messages');
    $this->router->get('/panel/messages/poll', 'PanelController@pollMessages');
    $this->router->get('/panel/sos', 'PanelController@sos');
    $this->router->get('/panel/resources', 'PanelController@resources');
    $this->router->get('/panel/settings', 'PanelController@settings');

    // Panel - POST
    $this->router->post('/panel/checkin', 'PanelController@saveCheckin');
    $this->router->post('/panel/journal/create', 'PanelController@createJournal');
    $this->router->post('/panel/journal/update', 'PanelController@updateJournal');
    $this->router->post('/panel/journal/delete', 'PanelController@deleteJournal');
    $this->router->post('/panel/messages/create', 'PanelController@createMessage');
    $this->router->post('/panel/messages/like', 'PanelController@likeMessage');
    $this->router->post('/panel/messages/send', 'PanelController@sendMessage');
    $this->router->post('/panel/settings/profile', 'PanelController@updateProfile');
    $this->router->post('/panel/settings/join-group', 'PanelController@joinGroup');
    $this->router->post('/panel/sos/send', 'PanelController@sendSos');

    // Therapist - GET
    $this->router->get('/therapist/patients', 'PanelController@myPatients');
    $this->router->get('/therapist/patient/{id}', 'PanelController@patientDetail');
    $this->router->get('/therapist/resources', 'PanelController@therapistResources');
    $this->router->get('/therapist/messages', 'PanelController@therapistMessages');
    $this->router->get('/therapist/messages/{id}', 'PanelController@therapistConversation');
    $this->router->get('/therapist/sos', 'PanelController@therapistSos');
    $this->router->get('/therapist/sos/count', 'PanelController@sosCount');

    // Therapist - POST
    $this->router->post('/therapist/resources/create', 'PanelController@createTherapistResource');
    $this->router->post('/therapist/resources/delete', 'PanelController@deleteTherapistResource');
    $this->router->post('/therapist/sos/acknowledge', 'PanelController@acknowledgeSos');
    $this->router->post('/therapist/sos/resolve', 'PanelController@resolveSos');

    // Notifications
    $this->router->get('/notifications/count', 'PanelController@notificationCount');
    $this->router->get('/notifications/list', 'PanelController@notificationList');
    $this->router->post('/notifications/read', 'PanelController@notificationRead');
    $this->router->post('/notifications/read-all', 'PanelController@notificationReadAll');

    // Admin - GET
    $this->router->get('/admin/dashboard', 'AdminController@dashboard');
    $this->router->get('/admin/users', 'AdminController@users');
    $this->router->get('/admin/moderation', 'AdminController@moderation');
    $this->router->get('/admin/settings', 'AdminController@settings');

    // Admin - POST
    $this->router->post('/admin/users/create', 'AdminController@createUser');
    $this->router->post('/admin/users/update', 'AdminController@updateUser');
    $this->router->post('/admin/users/delete', 'AdminController@deleteUser');
    $this->router->post('/admin/users/assign', 'AdminController@assignPatient');
    $this->router->post('/admin/users/unassign', 'AdminController@unassignPatient');
    $this->router->post('/admin/moderation/delete', 'AdminController@deleteMessage');
  }

  public function run(): void {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $this->router->dispatch($method, $uri);
  }
}
