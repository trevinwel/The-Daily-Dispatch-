<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/User.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Article.php';

Auth::requireAdmin();

$articleModel = new Article();
$categories   = $articleModel->getCategories();
$user         = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Article — Admin</title>
<link rel="stylesheet" href="/newssite/assets/css/admin.css">
</head>
<body>

<aside class="admin-sidebar">
  <div class="sidebar-brand">Daily<span>Dispatch</span></div>
  <ul class="sidebar-nav">
    <li><a href="/newssite/admin/"><span class="ico">📊</span> Dashboard</a></li>
    <li><a href="/newssite/admin/dashboard.php" class="active"><span class="ico">✏️</span> New Article</a></li>
    <li><a href="/newssite/" target="_blank"><span class="ico">🌐</span> View Site</a></li>
    <li><a href="/newssite/logout.php"><span class="ico">🚪</span> Logout</a></li>
  </ul>
</aside>

<main class="admin-main">
  <div class="admin-topbar">
    <h1>Publish New Article</h1>
    <span class="admin-topbar-meta">Logged in as <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <div id="upload-status"></div>

  <div class="admin-card">
    <div class="admin-card-header"><h2>Article Details</h2></div>
    <div class="admin-card-body">
      <form id="article-form" class="admin-form" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <input type="hidden" name="content" id="content-hidden">

        <div class="form-row">
          <div class="form-group">
            <label for="headline">Headline *</label>
            <input type="text" id="headline" name="headline" placeholder="Write a clear, compelling headline…" maxlength="100" required>
            <div class="counter" id="headline-counter">0 / 100</div>
          </div>
          <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
              <option value="">— Select category —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= (int) $cat['id'] ?>">
                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Article Content *</label>
          <div class="toolbar" id="editor-toolbar">
            <select id="font-family" title="Font Family">
              <option value="">Font Family</option>
              <option value="Arial, sans-serif">Arial</option>
              <option value="Georgia, serif">Georgia</option>
              <option value="'Times New Roman', serif">Times New Roman</option>
              <option value="'Courier New', monospace">Courier New</option>
              <option value="Verdana, sans-serif">Verdana</option>
              <option value="Tahoma, sans-serif">Tahoma</option>
              <option value="'Trebuchet MS', sans-serif">Trebuchet</option>
            </select>
            <select id="font-size" title="Font Size">
              <option value="">Font Size</option>
              <option value="1">Small</option>
              <option value="3">Normal</option>
              <option value="4">Large</option>
              <option value="5">X-Large</option>
              <option value="6">XX-Large</option>
            </select>
            <div class="toolbar-sep"></div>
            <button type="button" class="toolbar-btn" data-cmd="bold" title="Bold"><b>B</b></button>
            <button type="button" class="toolbar-btn" data-cmd="italic" title="Italic"><i>I</i></button>
            <button type="button" class="toolbar-btn" data-cmd="underline" title="Underline"><u>U</u></button>
            <button type="button" class="toolbar-btn" data-cmd="strikeThrough" title="Strikethrough"><s>S</s></button>
            <div class="toolbar-sep"></div>
            <button type="button" class="toolbar-btn" data-cmd="justifyLeft" title="Align Left">&#8676;</button>
            <button type="button" class="toolbar-btn" data-cmd="justifyCenter" title="Center">&#8596;</button>
            <button type="button" class="toolbar-btn" data-cmd="justifyRight" title="Align Right">&#8677;</button>
            <div class="toolbar-sep"></div>
            <button type="button" class="toolbar-btn" data-cmd="insertUnorderedList" title="Bullet List">&#8226;&#8212;</button>
            <button type="button" class="toolbar-btn" data-cmd="insertOrderedList" title="Numbered List">1.</button>
            <div class="toolbar-sep"></div>
            <button type="button" class="toolbar-btn" data-cmd="indent" title="Indent">&#8677;&#8677;</button>
            <button type="button" class="toolbar-btn" data-cmd="outdent" title="Outdent">&#8676;&#8676;</button>
            <div class="toolbar-sep"></div>
            <select id="text-color-picker" title="Text Color">
              <option value="">Text Color</option>
              <option value="#cc0b1a">Red</option>
              <option value="#1a1a1a">Black</option>
              <option value="#1d4ed8">Blue</option>
              <option value="#16a34a">Green</option>
              <option value="#d97706">Orange</option>
              <option value="#7c3aed">Purple</option>
              <option value="#888888">Gray</option>
            </select>
            <div class="toolbar-sep"></div>
            <button type="button" class="toolbar-btn" data-cmd="removeFormat" title="Clear Formatting" style="font-size:.7rem;width:auto;padding:0 .5rem;">Clear</button>
          </div>
          <div id="content-editor"
               contenteditable="true"
               placeholder="Write your article content here…"
               spellcheck="true"></div>
          <div class="counter" id="content-counter">0 / 10,000</div>
        </div>

        <div class="form-group">
          <label for="image">Featured Image (JPG, PNG, WEBP — max 2MB)</label>
          <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
        </div>

        <div style="display:flex;align-items:center;gap:1.5rem;margin-top:.5rem;">
          <button type="submit" class="submit-btn" id="submit-btn">🚀 Publish Article</button>
          <a href="/newssite/admin/" style="font-size:.85rem;color:var(--muted);">← Back to Dashboard</a>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
(function() {
  const editor    = document.getElementById('content-editor');
  const hidden    = document.getElementById('content-hidden');
  const counter   = document.getElementById('content-counter');
  const hlInput   = document.getElementById('headline');
  const hlCounter = document.getElementById('headline-counter');
  const status    = document.getElementById('upload-status');
  const form      = document.getElementById('article-form');
  const submitBtn = document.getElementById('submit-btn');

  function getTextLength() {
    return (editor.innerText || editor.textContent || '').trim().length;
  }

  function updateContentCounter() {
    const len = getTextLength();
    counter.textContent = len.toLocaleString() + ' / 10,000';
    counter.className = 'counter' + (len > 9000 ? ' counter-warn' : '') + (len > 10000 ? ' counter-over' : '');
  }

  function updateHlCounter() {
    const len = hlInput.value.length;
    hlCounter.textContent = len + ' / 100';
    hlCounter.className = 'counter' + (len > 90 ? ' counter-warn' : '') + (len >= 100 ? ' counter-over' : '');
  }

  editor.addEventListener('input', updateContentCounter);
  hlInput.addEventListener('input', updateHlCounter);
  updateContentCounter();
  updateHlCounter();

  document.querySelectorAll('.toolbar-btn[data-cmd]').forEach(btn => {
    btn.addEventListener('mousedown', function(e) {
      e.preventDefault();
      document.execCommand(this.dataset.cmd, false, null);
      editor.focus();
      updateToolbarState();
    });
  });

  document.getElementById('font-family').addEventListener('change', function() {
    if (this.value) {
      document.execCommand('fontName', false, this.value);
      editor.focus();
    }
    this.selectedIndex = 0;
  });

  document.getElementById('font-size').addEventListener('change', function() {
    if (this.value) {
      document.execCommand('fontSize', false, this.value);
      editor.focus();
    }
    this.selectedIndex = 0;
  });

  document.getElementById('text-color-picker').addEventListener('change', function() {
    if (this.value) {
      document.execCommand('foreColor', false, this.value);
      editor.focus();
    }
    this.selectedIndex = 0;
  });

  function updateToolbarState() {
    const cmds = ['bold','italic','underline','strikeThrough'];
    cmds.forEach(cmd => {
      const btn = document.querySelector('.toolbar-btn[data-cmd="' + cmd + '"]');
      if (btn) btn.classList.toggle('active', document.queryCommandState(cmd));
    });
  }

  editor.addEventListener('keyup', updateToolbarState);
  editor.addEventListener('mouseup', updateToolbarState);
  editor.addEventListener('selectionchange', updateToolbarState);

  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const htmlContent = editor.innerHTML.trim();
    const textContent = (editor.innerText || editor.textContent || '').trim();

    if (!hlInput.value.trim()) {
      status.className = 'error';
      status.textContent = '✗ Headline is required.';
      return;
    }
    if (hlInput.value.length > 100) {
      status.className = 'error';
      status.textContent = '✗ Headline exceeds 100 characters.';
      return;
    }
    if (!textContent) {
      status.className = 'error';
      status.textContent = '✗ Article content is required.';
      return;
    }
    if (textContent.length > 10000) {
      status.className = 'error';
      status.textContent = '✗ Content exceeds 10,000 characters.';
      return;
    }
    if (!document.getElementById('category_id').value) {
      status.className = 'error';
      status.textContent = '✗ Please select a category.';
      return;
    }

    const imageInput = document.getElementById('image');
    if (imageInput.files.length > 0) {
      const file = imageInput.files[0];
      const allowed = ['image/jpeg','image/png','image/webp'];
      if (!allowed.includes(file.type)) {
        status.className = 'error';
        status.textContent = '✗ Only JPG, PNG, and WEBP images allowed.';
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        status.className = 'error';
        status.textContent = '✗ Image must be under 2MB.';
        return;
      }
    }

    hidden.value = htmlContent;

    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Publishing…';
    status.className = '';
    status.style.display = 'none';

    try {
      const fd = new FormData(form);
      const res = await fetch('/newssite/admin/upload_handler.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const data = await res.json();

      if (data.success) {
        status.className = 'success';
        status.textContent = '✓ ' + data.message + ' (ID: ' + data.article_id + ')';
        form.reset();
        editor.innerHTML = '';
        updateContentCounter();
        updateHlCounter();
      } else {
        status.className = 'error';
        status.textContent = '✗ ' + (data.error || (data.errors ? data.errors.join(' | ') : 'Upload failed.'));
      }
    } catch (err) {
      status.className = 'error';
      status.textContent = '✗ Network error. Please try again.';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = '🚀 Publish Article';
      status.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
})();
</script>
</body>
</html>