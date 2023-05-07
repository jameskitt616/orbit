$(document).ready(function () {
  transcoding.init();
});

let transcoding = {};

transcoding.init = function () {
  $('.copyUrl').click(transcoding.copyUrl);
};

transcoding.copyUrl = function () {
  let index = $(this).data('index');
  let tooltipTextCopyUrl = $('.tooltipTextCopyUrl_' + index);
  let url = $(this).data('url');
  navigator.clipboard.writeText(url);
  tooltipTextCopyUrl.show();
  setTimeout(function () {
    tooltipTextCopyUrl.fadeOut();
  }, 1000);
}
