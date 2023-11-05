import {jstree} from './jstree.js';

$(document).ready(function () {
  transcoding.init();
});

let transcoding = {};

transcoding.init = function () {
  $('.copyUrl').click(transcoding.copyUrl);
  $('#deleteTranscode').click(transcoding.delete);
  transcoding.fileTree();
  transcoding.detectRequestScheme();
};

transcoding.detectRequestScheme = function () {

  $('.urlProtocol').each(function () {
    let url = $(this).val();
    if (url.slice(0, 4) !== 'http') {
      let urlWithProtocol = window.location.protocol + '//' + url;
      $(this).val(urlWithProtocol);
    }
  })
}

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

      currentSelected.find('.jstree-anchor')[0].click();
    }

    return false;
  });


  fileTree.on('select_node.jstree', function (e, data) {
    var filePath = data.node.data.file_path;
    $('.filePath').val(filePath);
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

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(() => {
      tooltipTextCopyUrl.show();
      setTimeout(function () {
        tooltipTextCopyUrl.fadeOut();
      }, 1000);
    }, () => {
      console.error('Failed to copy');
    });
  } else {
    console.error('Clipboard API is not supported, falling back to legacy method.');

    fallbackCopyTextToClipboard(url);
  }
};

function fallbackCopyTextToClipboard(text) {
  var textArea = document.createElement("textarea");
  textArea.value = text;

  // Avoid scrolling to bottom
  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();

  try {
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Fallback: Copying text command was ' + msg);
    tooltipTextCopyUrl.show();
    setTimeout(function () {
      tooltipTextCopyUrl.fadeOut();
    }, 1000);
  } catch (err) {
    console.error('Fallback: Oops, unable to copy', err);
  }

  document.body.removeChild(textArea);
}
