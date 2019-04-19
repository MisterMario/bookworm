function animateDropResults(self) {
  var value = $(self).val(),
      drop_list = $('#search-results');

  if (value.length > 0 && drop_list.css('display') == 'none')
    drop_list.slideDown(500);
  else if (value.length == 0) {
    drop_list.css('margin-top', '0');
    drop_list.slideUp(500);
  }
}

function goToLink(self) {
  self = $(self);
  window.location.href = "http://bw.loc" + self.children('.link').text();
}
