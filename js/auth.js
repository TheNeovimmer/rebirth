document.addEventListener('DOMContentLoaded', function () {
  var loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();
      window.location.href = '/panel/dashboard';
    });
  }

  var signupForm = document.getElementById('signupForm');
  if (signupForm) {
    signupForm.addEventListener('submit', function (e) {
      e.preventDefault();
      window.location.href = '/panel/dashboard';
    });
  }
});
