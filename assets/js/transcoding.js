import {jstree} from './jstree.js';

$(document).ready(function () {
  transcoding.init();
});

let transcoding = {};

transcoding.init = function () {
  $('.copyUrl').click(transcoding.copyUrl);
  $('#deleteTranscode').click(transcoding.delete);
  transcoding.fileTree();
  $('#fileTreeSearchForm').submit(transcoding.submitSearchFileTreeForm);
};

transcoding.submitSearchFileTreeForm = function (e) {
  e.preventDefault();
  $("#fileTree").jstree(true).search($('#fileTreeSearch').val());

  return false;
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
    }
  });

  fileTree.on('click', '.jstree-node', function (e) {
    fileTree.jstree(true).toggle_node(e.target);
    return false;
  });

  fileTree.on("changed.jstree", function (e, data) {
    //   if (data.node.type === 'folder') {
    //     return false;
    //   }
    console.log("The selected nodes are:");
    console.log(data.selected);
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
