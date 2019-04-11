window.onload = function() {
  var a = $('div#topmenu ul li div.drop-down-menu').prev(),
      dd_menu = $('div#topmenu ul li div.drop-down-menu');

  a.bind({
    'click': function(e) {
      e.preventDefault();
    },
    'mouseover': function(e) {
      var self = $(e.target);

      self.toggleClass('active');
      self.next().toggleClass('hidden');
    },
    'mouseout': function(e) {
      var self = $(e.target);

      if (!$(e.relatedTarget).hasClass('drop-down-menu') && !$(e.relatedTarget).hasClass('dd-ul')
          && !$(e.relatedTarget).hasClass('dd-li') && !$(e.relatedTarget.parentNode).hasClass('dd-li')) {
        self.toggleClass('active');
        self.next().toggleClass('hidden');
      }
    }
  });

  dd_menu.bind('mouseleave', function(e) {
    var self = $(e.target),
        current_tag = self.prop('tagName');

    if (current_tag == 'DIV') {
      self.toggleClass('hidden');
      self.prev().toggleClass('active');
    } else if (current_tag == 'A') {
      self = self.parent().parent().parent();
      self.toggleClass('hidden');
      self.prev().toggleClass('active');
    }
  });
}
