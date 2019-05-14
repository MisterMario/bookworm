var tmr1 = null;

function getResults() {
  if (tmr1 != null) clearTimeout(tmr1);
  tmr1 = setTimeout(animateDropResults, 1000);
}

function animateDropResults() {
  // Сюда стоит добавить проверку какие клавиши были нажаты, чтобы реагировать только на буквы, цифры, символы, ctr+v.
  var value = $('#content-head #search input[type=text]').val();
      drop_list = $('#search-results');

  if (value.length != 0) {

    ajax('/ajax/ajax_search.php', {search_string: value}, function(data) {

      if (data.status) {
        $('#content-head #search-results #result-lines').html(data.html);
        $('#content-head #search-results #more-results a').attr('href', 'http://bw.loc/search/' + value + "/");
        if (drop_list.css('display') == 'none')
          drop_list.slideDown(500);
      } else if (drop_list.css('dislplay') != 'none') {
        drop_list.css('margin-top', '0');
        drop_list.slideUp(500);
      }
    });

  } else {
    drop_list.css('margin-top', '0');
    drop_list.slideUp(500);
  }
}

function goToLink(self) {
  self = $(self);
  window.location.href = "http://bw.loc" + self.children('.link').text();
}
