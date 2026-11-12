'use strict';

// Give visitors immediate feedback when they choose a project rating.
document.querySelectorAll('.rating-options').forEach((ratingGroup) => {
  const panel = ratingGroup.closest('.feedback');
  const status = panel?.querySelector('.rating-status');

  ratingGroup.querySelectorAll('input[name="rating"]').forEach((input) => {
    input.addEventListener('change', () => {
      if (status) {
        status.textContent = `Selected rating: ${input.value} out of 5 stars.`;
      }

      ratingGroup.querySelectorAll('label').forEach((label) => {
        label.classList.toggle('is-selected', label.contains(input));
      });
    });
  });
});

// Keep comment entry clear and prevent whitespace-only submissions.
document.querySelectorAll('.comment-form').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const field = form.querySelector('textarea[name="comment"]');
    if (field && field.value.trim() === '') {
      event.preventDefault();
      field.focus();
      field.setCustomValidity('Please enter a comment before saving.');
      field.reportValidity();
      return;
    }
    field?.setCustomValidity('');
  });
});
