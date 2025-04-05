export function sanitize_html(input) {
  let div = document.createElement('div');
  div.textContent = input;
  return div.innerHTML;
}

export function format_post_content(content) {
  content = sanitize_html(content);

  // Bold
  content = content.replace(/\*\*(.*?)\*\*/gs, '<strong>$1</strong>');
  content = content.replace(/__(.*?)__/gs, '<strong>$1</strong>');

  // Italic
  content = content.replace(/\*(.*?)\*/gs, '<em>$1</em>');
  content = content.replace(/_(.*?)_/gs, '<em>$1</em>');

  // Links
  content = content.replace(/\[(.*?)\]\((.*?)\)/gs, '<a href="$2">$1</a>');

  // Headings
  content = content.replace(/^(#)(.*?)$/gm, '<h1>$2</h1>');
  content = content.replace(/^(##)(.*?)$/gm, '<h2>$2</h2>');
  content = content.replace(/^(###)(.*?)$/gm, '<h3>$2</h3>');
  content = content.replace(/^(####)(.*?)$/gm, '<h4>$2</h4>');
  content = content.replace(/^(#####)(.*?)$/gm, '<h5>$2</h5>');
  content = content.replace(/^(######)(.*?)$/gm, '<h6>$2</h6>');

  // Lists (unordered)
  content = content.replace(/^\s*[-*]\s+(.*?)$/gm, '<ul><li>$1</li></ul>');

  // Lists (ordered)
  content = content.replace(/^\s*\d+\.\s+(.*?)$/gm, '<ol><li>$1</li></ol>');

  // Code (single-line)
  content = content.replace(/\`(.*?)\`/gs, '<code>$1</code>');

  // Newlines to paragraph and line break
  content = content.replace(/\n\n/g, '</p><p>');
  content = '<p>' + content + '</p>';
  content = content.replace(/\n/g, '<br>');

  content = linkify(content);

  return content;
}

export function linkify(text) {
  return text.replace(
    /(https?:\/\/[^\s<]+)/gi,
    '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
  );
}
