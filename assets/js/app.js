document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#commentForm');
  const message = document.querySelector('#message');
  const counter = document.querySelector('#characterCount');
  const search = document.querySelector('#commentSearch');
  const cards = [...document.querySelectorAll('[data-comment]')];
  const emptySearch = document.querySelector('#emptySearch');

  const name = document.querySelector('#name');
  const lengthOf = (value) => [...value.trim()].length;
  const normalizeSearch = (value) => value.toLocaleLowerCase('es')
    .normalize('NFD').replace(/[\u0300\u0301\u0308]/g, '').normalize('NFC');

  const updateCounter = () => {
    if (!message || !counter) return;
    const length = lengthOf(message.value);
    counter.textContent = `${length} / 500`;
    counter.classList.toggle('text-danger', length > 500);
  };

  name?.addEventListener('input', () => name.setCustomValidity(''));
  message?.addEventListener('input', () => {
    message.setCustomValidity('');
    updateCounter();
  });
  updateCounter();

  form?.addEventListener('submit', (event) => {
    const nameLength = lengthOf(name.value);
    const messageLength = lengthOf(message.value);
    name.setCustomValidity(nameLength < 2 || nameLength > 60 ? 'Escribe un nombre de 2 a 60 caracteres.' : '');
    message.setCustomValidity(messageLength < 10 || messageLength > 500 ? 'El comentario debe contener entre 10 y 500 caracteres.' : '');
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  });

  search?.addEventListener('input', () => {
    const term = normalizeSearch(search.value.trim());
    let visible = 0;

    cards.forEach((card) => {
      const match = normalizeSearch(card.textContent).includes(term);
      card.hidden = !match;
      if (match) visible += 1;
    });

    if (emptySearch) emptySearch.hidden = term === '' || visible !== 0;
  });

  const alert = document.querySelector('.alert-dismissible');
  if (alert) {
    window.setTimeout(() => window.bootstrap?.Alert.getOrCreateInstance(alert).close(), 5000);
  }
});

