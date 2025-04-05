import { format_post_content } from './util.js';
document.addEventListener("DOMContentLoaded", () => {
  const content_text_area = document.getElementById('content');
  const preview_area = document.getElementById('content-preview');

  function update_preview() {
    if (!content_text_area || !preview_area) return;

    const content = content_text_area.value;
    if (content.trim() === '') {
      preview_area.innerHTML = '<p class="preview-placeholder">Your content preview will appear here...</p>';
      return;
    }

    const formattedContent = format_post_content(content);
    preview_area.innerHTML = formattedContent;
  }

  if (content_text_area && preview_area) {
    content_text_area.addEventListener('input', update_preview);
    update_preview();
  }
})