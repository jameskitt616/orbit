$(document).ready(function () {
  transcoding.init();
});

let transcoding = {};

transcoding.init = function () {
  $('.copyUrl').click(transcoding.copyUrl);
};

transcoding.copyUrl = function () {
  let tooltipTextCopyUrl = $('.tooltipTextCopyUrl');
  let url = $('.copyUrl').data('url');
  navigator.clipboard.writeText(url);
  tooltipTextCopyUrl.show();
  setTimeout(function () {
    tooltipTextCopyUrl.fadeOut();
  }, 1000);
}
