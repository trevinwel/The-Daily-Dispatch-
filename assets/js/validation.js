

'use strict';

const LIMITS = {
  headline: 100,
  content:  10000,
  username: 20,
};

/** 
 * @param {string} fieldId     
 * @param {string} counterId    
 * @param {number} maxLength    
 */

 
function attachCounter(fieldId, counterId, maxLength) {
  const field   = document.getElementById(fieldId);
  const counter = document.getElementById(counterId);

  if (!field || !counter) return;

  function update() {
    const len       = field.value.length;
    const remaining = maxLength - len;

    counter.textContent = `${len} / ${maxLength}`;

    if (remaining < 0) {
      
      field.value = field.value.slice(0, maxLength);
      counter.textContent = `${maxLength} / ${maxLength}`;
      counter.classList.add('counter--over');
      counter.classList.remove('counter--warn');
    } else if (remaining <= Math.ceil(maxLength * 0.1)) {
      counter.classList.add('counter--warn');
      counter.classList.remove('counter--over');
    } else {
      counter.classList.remove('counter--warn', 'counter--over');
    }
  }

  field.addEventListener('input',  update);
  field.addEventListener('paste',  () => setTimeout(update, 0));
  field.addEventListener('keyup',  update);
  update(); 
}


function validateArticleForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return;

  form.addEventListener('submit', function (e) {
    const headline = form.querySelector('[name="headline"]');
    const content  = form.querySelector('[name="content"]');
    const category = form.querySelector('[name="category_id"]');
    const errors   = [];

    if (!headline || headline.value.trim().length === 0) {
      errors.push('Headline is required.');
    } else if (headline.value.length > LIMITS.headline) {
      errors.push(`Headline cannot exceed ${LIMITS.headline} characters.`);
    }

    if (!content || content.value.trim().length === 0) {
      errors.push('Article content is required.');
    } else if (content.value.length > LIMITS.content) {
      errors.push(`Content cannot exceed ${LIMITS.content} characters.`);
    }

    if (!category || category.value === '' || category.value === '0') {
      errors.push('Please select a category.');
    }

    
    const imageInput = form.querySelector('[name="image"]');
    if (imageInput && imageInput.files.length > 0) {
      const file         = imageInput.files[0];
      const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
      const maxSize      = 2 * 1024 * 1024; // 2MB

      if (!allowedTypes.includes(file.type)) {
        errors.push('Only JPG, PNG, and WEBP images are allowed.');
      }
      if (file.size > maxSize) {
        errors.push('Image must be under 2MB.');
      }
    }

    if (errors.length > 0) {
      e.preventDefault();
      displayErrors(errors, form);
    }
  });
}

function displayErrors(errors, form) {
  let errorBox = form.querySelector('.form-errors');
  if (!errorBox) {
    errorBox = document.createElement('div');
    errorBox.className = 'form-errors';
    form.prepend(errorBox);
  }
  errorBox.innerHTML = errors.map(err =>
    `<p class="form-error">⚠ ${err}</p>`
  ).join('');
  errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
}


document.addEventListener('DOMContentLoaded', () => {
  attachCounter('headline',  'headline-counter',  LIMITS.headline);
  attachCounter('content',   'content-counter',   LIMITS.content);
  attachCounter('username',  'username-counter',  LIMITS.username);
  validateArticleForm('article-form');
});