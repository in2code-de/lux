var elements = document.querySelectorAll('[data-lux-toggle]');
for (var i = 0; i < elements.length; i++) {
  elements[i].addEventListener('click', function(event) {
    var thisElement = event.target;
    if (thisElement.classList.contains('fa-chevron-down')) {
      thisElement.classList.remove('fa-chevron-down');
      thisElement.classList.add('fa-chevron-up');
    } else {
      thisElement.classList.remove('fa-chevron-up');
      thisElement.classList.add('fa-chevron-down');
    }
    var target = thisElement.getAttribute('data-lux-toggle');
    var targetElements = document.querySelectorAll('[data-lux-toggle-target="' + target + '"]');

    for (var j = 0; j < targetElements.length; j++) {
      targetElements[j].classList.toggle('hide');
    }
  });
}
