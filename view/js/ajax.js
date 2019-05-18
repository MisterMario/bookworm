function ajax(handler, params, callback, isAsync = true) {
  $.ajax({
    url: handler,
    type: 'POST',
    data: JSON.stringify(params),
    contentType: false,
    processData: false,
    dataType: 'json',
    success: callback,
    async: isAsync,
  });
}
