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
    $this->router->get('/panel/appointments', 'PanelController@appointments');
    $this->router->get('/panel/community', 'PanelController@community');
    $this->router->get('/panel/progress', 'PanelController@progress');
    $this->router->get('/panel/resources', 'PanelController@resources');
    $this->router->get('/panel/settings', 'PanelController@settings');

    // Panel - POST (CRUD) — all use /panel/ prefix
    $this->router->post('/panel/checkin', 'PanelController@saveCheckin');
    $this->router->post('/panel/journal/create', 'PanelController@createJournal');
    $this->router->post('/panel/journal/delete', 'PanelController@deleteJournal');
    $this->router->post('/panel/appointments/create', 'PanelController@createAppointment');
    $this->router->post('/panel/appointments/cancel', 'PanelController@cancelAppointment');
    $this->router->post('/panel/messages/create', 'PanelController@createMessage');
    $this->router->post('/panel/messages/like', 'PanelController@likeMessage');
    $this->router->post('/panel/messages/send', 'PanelController@sendMessage');
    $this->router->post('/panel/settings/profile', 'PanelController@updateProfile');
    $this->router->post('/panel/settings/join-group', 'PanelController@joinGroup');

    // Therapist
    $this->router->get('/therapist/patients', 'PanelController@myPatients');
    $this->router->get('/therapist/patient/{id}', 'PanelController@patientDetail');

    // Messaging
    $this->router->get('/panel/messages', 'PanelController@messages');
    $this->router->get('/panel/messages/poll', 'PanelController@pollMessages');

    // SOS
    $this->router->get('/panel/sos', 'PanelController@sos');
    $this->router->post('/panel/sos/send', 'PanelController@sendSos');

    // Therapist features
    $this->router->get('/therapist/resources', 'PanelController@therapistResources');
    $this->router->post('/therapist/resources/create', 'PanelController@createTherapistResource');
    $this->router->post('/therapist/resources/delete', 'PanelController@deleteTherapistResource');
    $this->router->get('/therapist/messages', 'PanelController@therapistMessages');
    $this->router->get('/therapist/messages/{id}', 'PanelController@therapistConversation');
    $this->router->get('/therapist/availability', 'PanelController@therapistAvailability');
    $this->router->post('/therapist/availability/save', 'PanelController@saveTherapistAvailability');
    $this->router->get('/therapist/sos', 'PanelController@therapistSos');
    $this->router->post('/therapist/sos/acknowledge', 'PanelController@acknowledgeSos');
    $this->router->post('/therapist/sos/resolve', 'PanelController@resolveSos');
    $this->router->get('/therapist/patient/{id}/progress', 'PanelController@patientProgress');
    $this->router->post('/therapist/patient/{id}/progress/update', 'PanelController@updatePatientProgress');

    // SOS count + availability check (JSON endpoints)
    $this->router->get('/therapist/sos/count', 'PanelController@sosCount');
    $this->router->get('/therapist/availability/check', 'PanelController@checkAvailability');

    // Clinical Notes
    $this->router->post('/therapist/patient/{id}/notes/create', 'PanelController@createClinicalNote');

    // Relapse Tracking
    $this->router->get('/panel/relapses', 'PanelController@relapses');
    $this->router->post('/panel/relapses/create', 'PanelController@createRelapse');
    $this->router->post('/panel/relapses/delete', 'PanelController@deleteRelapse');

    // Treatment Plans
    $this->router->get('/panel/treatment-plan', 'PanelController@myTreatmentPlan');
    $this->router->get('/therapist/patient/{id}/plans', 'PanelController@therapistPlans');
    $this->router->get('/therapist/patient/{id}/plans/create', 'PanelController@therapistPlanCreate');
    $this->router->post('/therapist/patient/{id}/plans/create', 'PanelController@therapistPlanCreatePost');
    $this->router->get('/therapist/patient/{id}/plans/edit/{planId}', 'PanelController@therapistPlanEdit');
    $this->router->post('/therapist/patient/{id}/plans/update', 'PanelController@therapistPlanUpdate');
    $this->router->post('/therapist/patient/{id}/plans/delete', 'PanelController@therapistPlanDelete');

    // Notifications
    $this->router->get('/notifications/count', 'PanelController@notificationCount');
    $this->router->get('/notifications/list', 'PanelController@notificationList');
    $this->router->post('/notifications/read', 'PanelController@notificationRead');
    $this->router->post('/notifications/read-all', 'PanelController@notificationReadAll');

    // Patient Timeline
    $this->router->get('/therapist/patient/{id}/timeline', 'PanelController@patientTimeline');

    // Admin availability endpoint
    $this->router->get('/admin/appointments/availability', 'AdminController@getAvailability');

    // Admin - GET
    $this->router->get('/admin/dashboard', 'AdminController@dashboard');
    $this->router->get('/admin/users', 'AdminController@users');
    $this->router->get('/admin/appointments', 'AdminController@appointments');
    $this->router->get('/admin/resources', 'AdminController@resources');
    $this->router->get('/admin/moderation', 'AdminController@moderation');
    $this->router->get('/admin/settings', 'AdminController@settings');
    $this->router->get('/admin/analytics', 'AdminController@analytics');

    // Admin - POST (CRUD)
    $this->router->post('/admin/users/create', 'AdminController@createUser');
    $this->router->post('/admin/users/update', 'AdminController@updateUser');
    $this->router->post('/admin/users/delete', 'AdminController@deleteUser');
    $this->router->post('/admin/appointments/create', 'AdminController@createAppointment');
    $this->router->post('/admin/appointments/update', 'AdminController@updateAppointment');
    $this->router->post('/admin/resources/create', 'AdminController@createResource');
    $this->router->post('/admin/resources/update', 'AdminController@updateResource');
    $this->router->post('/admin/resources/delete', 'AdminController@deleteResource');
    $this->router->post('/admin/moderation/delete', 'AdminController@deleteMessage');
  }

  public function run(): void {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $this->router->dispatch($method, $uri);
  }
}
