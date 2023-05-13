$(document).ready(function () {
  settings.init();
});

let settings = {};

settings.init = function () {
  $('.deleteUser').click(settings.deleteUser);
};

settings.deleteUser = function () {

  let message = $(this).data('message');

  if (confirm(message)) {
    window.location.href = $(this).data('url');
  }

  return false;
}
