import {jstree} from './jstree.js';

$(document).ready(function () {
  transcoding.init();
});

let transcoding = {};

transcoding.init = function () {
  $('.copyUrl').click(transcoding.copyUrl);
  $('#deleteTranscode').click(transcoding.delete);
  transcoding.fileTree();
};

transcoding.fileTree = function () {

  let fileTree = $('#fileTree');
  let url = fileTree.data('url');

  fileTree.jstree({
    'plugins': ['search'],
    'core': {
      'multiple': false,
      'dblclick_toggle': false,
      'data': {
        'url': url,
        'dataType': 'json'
      }
    },
  });

  fileTree.on('click', '.jstree-node', function (e) {
    fileTree.jstree(true).toggle_node(e.target);

    let currentSelected = $(this);
    if (currentSelected.hasClass('selectableFile')) {

      let previousSelected = $('.jstree-node.bg-indigo-300');
      previousSelected.removeClass('bg-indigo-300');
      previousSelected.addClass('bg-indigo-500 hover:bg-indigo-300');

      currentSelected.removeClass('bg-indigo-500 hover:bg-indigo-300');
      currentSelected.addClass('bg-indigo-300 hover:bg-indigo-200');

      // currentSelected.find('.jstree-anchor').click();
    }

    return false;
  });



  fileTree.on('select_node.jstree', function (e, data) {
    var customData = data.node.data.file_path;
    console.log(customData);
  });

  let fileTreeSearch = $('#fileTreeSearch');
  fileTreeSearch.on('input', function (e) {
    $("#fileTree").jstree(true).search(fileTreeSearch.val());
  });
}

transcoding.delete = function () {

  let message = $(this).data('message');

  if (confirm(message)) {
    window.location.href = $(this).data('url');
  }

  return false;
}

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
