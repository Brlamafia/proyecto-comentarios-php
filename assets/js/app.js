document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#commentForm');
  const message = document.querySelector('#message');
  const counter = document.querySelector('#characterCount');
  const search = document.querySelector('#commentSearch');
  const cards = [...document.querySelectorAll('[data-comment]')];
  const emptySearch = document.querySelector('#emptySearch');

  const updateCounter = () => {
    if (!message || !counter) return;
    const length = message.value.length;
    counter.textContent = `${length} / 500`;
    counter.classList.toggle('text-danger', length > 500);
  };

  message?.addEventListener('input', updateCounter);
  updateCounter();

  form?.addEventListener('submit', (event) => {
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  });

  search?.addEventListener('input', () => {
    const term = search.value.trim().toLocaleLowerCase('es');
    let visible = 0;

    cards.forEach((card) => {
      const match = card.textContent.toLocaleLowerCase('es').includes(term);
      card.hidden = !match;
      if (match) visible += 1;
    });

    if (emptySearch) emptySearch.hidden = visible !== 0;
  });

  const alert = document.querySelector('.alert-dismissible');
  if (alert) {
    window.setTimeout(() => bootstrap.Alert.getOrCreateInstance(alert).close(), 5000);
  }
});

