function showMessageBox(text, code = 0) {
  var self = $('#message-box'),
      title = "ИНФОРМАЦИЯ";

  if (code == 1) {
    self.addClass('error');
    title = "ОШИБКА";
  } else self.removeClass('error');

  self.find('#message-title').html(title);
  self.children('#message-body').html(text);
  self.removeClass('hidden');
  self.animate({'bottom':'7%', 'opacity': '1'}, 300);
}

function hideMessageBox() {
  var self = $('#message-box'),
      styles = {'bottom': 0 - self.outerHeight() + 'px', 'opacity': '0'};
  self.animate(styles, 300, function() { self.addClass('hidden'); });
}
