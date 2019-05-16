function ajax(handler, params, callback, isAsync = true) {
  $.ajax({
    url: handler,
    type: 'POST',
    data: JSON.stringify(params),
    contentType: 'application/json; charset=utf-8',
    dataType: 'json',
    success: callback,
    async: isAsync,
  });
}
