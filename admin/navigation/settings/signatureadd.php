<main class="ml-56 min-h-screen bg-slate-100 p-8">

  <!-- Header -->
  <div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Signature</h1>
    <p class="text-slate-500 text-sm mt-1">Manage system settings and uploads</p>
  </div>

  <!-- Signature Card -->
  <div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

    <!-- Card Header -->
    <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 1 1 3.182 3.182L7.5 19.213l-4.5 1.5 1.5-4.5L16.862 3.487z"/>
        </svg>
      </div>
      <div>
        <h2 class="text-base font-semibold text-slate-800">Signature</h2>
        <p class="text-xs text-slate-400 mt-0.5">Draw or upload a PNG with transparent background</p>
      </div>
    </div>

    <!-- Tab Switcher -->
    <div class="flex border-b border-slate-100">
      <button
        id="tab-draw"
        onclick="switchTab('draw')"
        class="flex-1 py-3 text-sm font-medium text-blue-600 border-b-2 border-blue-600 transition-all"
      >
        ✏️ Draw Signature
      </button>
      <button
        id="tab-upload"
        onclick="switchTab('upload')"
        class="flex-1 py-3 text-sm font-medium text-slate-400 border-b-2 border-transparent hover:text-slate-600 transition-all"
      >
        📁 Upload File
      </button>
    </div>

    <div class="p-6">

      <!-- ===== DRAW TAB ===== -->
      <div id="panel-draw">

        <!-- Toolbar -->
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
          <div class="flex items-center gap-2">
            <!-- Pen color -->
            <label class="text-xs text-slate-500">Color</label>
            <input type="color" id="pen-color" value="#1e293b"
              class="w-8 h-8 rounded-lg cursor-pointer border border-slate-200 p-0.5 bg-white"
              oninput="updatePen()">

            <!-- Pen size -->
            <label class="text-xs text-slate-500 ml-2">Size</label>
            <input type="range" id="pen-size" min="1" max="12" value="3"
              class="w-20 accent-blue-600"
              oninput="updatePen(); document.getElementById('pen-size-val').textContent = this.value">
            <span id="pen-size-val" class="text-xs text-slate-500 w-4">3</span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Eraser toggle -->
            <button id="eraser-btn" onclick="toggleEraser()"
              title="Eraser"
              class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">
              🧹 Eraser
            </button>
            <!-- Undo -->
            <button onclick="undoDraw()"
              title="Undo"
              class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">
              ↩ Undo
            </button>
            <!-- Clear -->
            <button onclick="clearCanvas()"
              class="px-3 py-1.5 rounded-lg text-xs font-medium border border-red-100 text-red-500 hover:bg-red-50 transition-all">
              🗑 Clear
            </button>
          </div>
        </div>

        <!-- Canvas -->
        <div class="relative rounded-xl border-2 border-slate-200 overflow-hidden"
             style="background-image:linear-gradient(45deg,#e2e8f0 25%,transparent 25%),linear-gradient(-45deg,#e2e8f0 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#e2e8f0 75%),linear-gradient(-45deg,transparent 75%,#e2e8f0 75%);background-size:12px 12px;background-position:0 0,0 6px,6px -6px,-6px 0;">
          <canvas id="sig-canvas"
            class="block w-full cursor-crosshair touch-none"
            style="height:400px;">
          </canvas>
          <div id="canvas-hint" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <p class="text-slate-400 text-sm select-none">Draw your signature here...</p>
          </div>
        </div>

        <!-- Draw submit -->
        <div class="mt-5 flex items-center gap-3">
          <button
            type="button"
            id="draw-upload-btn"
            onclick="submitDrawnSignature()"
            disabled
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-medium shadow-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:bg-blue-700 active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
            </svg>
            Save Signature
          </button>
          <p class="text-xs text-slate-400">Transparent background auto-applied</p>
        </div>

      </div>

      <!-- ===== UPLOAD TAB ===== -->
      <div id="panel-upload" class="hidden">

        <!-- Drop Zone -->
        <div
          id="drop-zone"
          onclick="document.getElementById('sig-input').click()"
          ondragover="event.preventDefault(); this.classList.add('border-blue-400','bg-blue-50')"
          ondragleave="this.classList.remove('border-blue-400','bg-blue-50')"
          ondrop="handleDrop(event)"
          class="group relative flex flex-col items-center justify-center gap-3 border-2 border-dashed border-slate-200 rounded-xl h-52 cursor-pointer transition-all duration-200 hover:border-blue-400 hover:bg-blue-50"
        >
          <div id="preview-wrapper" class="hidden absolute inset-0 flex items-center justify-center p-4">
            <div class="relative rounded-lg overflow-hidden"
                 style="background-image:linear-gradient(45deg,#e2e8f0 25%,transparent 25%),linear-gradient(-45deg,#e2e8f0 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#e2e8f0 75%),linear-gradient(-45deg,transparent 75%,#e2e8f0 75%);background-size:12px 12px;background-position:0 0,0 6px,6px -6px,-6px 0;">
              <img id="preview-img" src="" alt="Signature preview" class="max-h-36 max-w-full object-contain">
            </div>
          </div>

          <div id="placeholder" class="flex flex-col items-center gap-2 pointer-events-none select-none">
            <div class="w-12 h-12 rounded-full bg-slate-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
              <svg class="w-6 h-6 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
              </svg>
            </div>
            <div class="text-center">
              <p class="text-sm font-medium text-slate-600 group-hover:text-blue-600 transition-colors">Click or drag to upload</p>
              <p class="text-xs text-slate-400 mt-0.5">PNG with transparent background</p>
            </div>
          </div>

          <input type="file" id="sig-input" accept="image/png" class="hidden" onchange="handleFile(this.files[0])">
        </div>

        <!-- File Info -->
        <div id="file-info" class="hidden mt-4 flex items-center justify-between bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909"/>
              </svg>
            </div>
            <div>
              <p id="file-name" class="text-sm font-medium text-slate-700 truncate max-w-xs"></p>
              <p id="file-size" class="text-xs text-slate-400 mt-0.5"></p>
            </div>
          </div>
          <button type="button" onclick="clearFile()" class="text-slate-400 hover:text-red-500 transition-colors ml-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Warning -->
        <div id="warn-opaque" class="hidden mt-3 flex items-start gap-2 text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
          <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
          </svg>
          <p class="text-xs leading-relaxed">This image may not have a transparent background. For best results, use a PNG with no background.</p>
        </div>

        <!-- Upload submit -->
        <div class="mt-6 flex items-center gap-3">
          <button
            type="button"
            id="upload-btn"
            onclick="submitSignature()"
            disabled
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-medium shadow-sm transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:bg-blue-700 active:scale-95"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
            </svg>
            Upload Signature
          </button>
          <p class="text-xs text-slate-400">Max file size: 10 MB</p>
        </div>

      </div>

      <!-- Success (shared) -->
      <div id="success-msg" class="hidden mt-4 flex items-center gap-2 text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </svg>
        <p class="text-sm font-medium">Signature saved successfully!</p>
      </div>

    </div>
  </div>

  <!-- Saved Signatures List -->
<div class="max-w-2xl mt-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-800">Saved Signatures</h2>
            <p class="text-xs text-slate-400 mt-0.5">Ang naka-active ay gagamitin sa pag-approve</p>
        </div>
        <span id="sig-count" class="text-xs text-slate-400"></span>
    </div>
    <div id="sig-list" class="p-6">
        <p class="text-sm text-slate-400 text-center py-4">
            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading...
        </p>
    </div>
</div>

</main>

<script>

// ─── SAVED SIGNATURES LIST ───────────────────────────────────────
function loadSignatures() {
    fetch('<?= BASE_URL ?>/fetchsignatures')
        .then(res => res.json())
        .then(renderSignatures);
}

function renderSignatures(sigs) {
    const list = document.getElementById('sig-list');
    const count = document.getElementById('sig-count');
    count.textContent = sigs.length + ' signature' + (sigs.length !== 1 ? 's' : '');

    if (!sigs.length) {
        list.innerHTML = `<p class="text-sm text-slate-400 text-center py-6">Wala ka pang naka-save na signature.</p>`;
        return;
    }

    list.innerHTML = sigs.map(s => `
        <div class="flex items-center gap-4 p-3 rounded-xl border-2 mb-3 transition-all ${s.is_active == 1 ? 'border-blue-400 bg-blue-50' : 'border-slate-100 hover:border-slate-200'}">
            <!-- Preview -->
            <div class="w-32 h-16 rounded-lg shrink-0 flex items-center justify-center overflow-hidden"
                style="background-image:linear-gradient(45deg,#e2e8f0 25%,transparent 25%),linear-gradient(-45deg,#e2e8f0 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#e2e8f0 75%),linear-gradient(-45deg,transparent 75%,#e2e8f0 75%);background-size:10px 10px;background-position:0 0,0 5px,5px -5px,-5px 0;">
                <img src="<?= BASE_URL ?>/${s.path}" class="max-h-14 max-w-full object-contain">
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800">${s.label}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">${new Date(s.created_at).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'numeric', minute:'2-digit' })}</p>
                ${s.is_active == 1 
                    ? `<span class="inline-flex items-center gap-1 mt-1 text-[10px] font-semibold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-circle-check text-[9px]"></i> Active — gagamitin sa approve
                       </span>` 
                    : ''}
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 shrink-0">
                ${s.is_active != 1 
                    ? `<button onclick="setActive(${s.id})"
                        class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-all">
                        Set as Active
                       </button>` 
                    : ''}
                <button onclick="deleteSig(${s.id})"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-100 text-red-500 hover:bg-red-50 transition-all">
                    <i class="fa-solid fa-trash text-[10px]"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function setActive(id) {
    fetch('<?= BASE_URL ?>/setactivesignature', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) loadSignatures();
    });
}

function deleteSig(id) {
    if (!confirm('Delete this signature?')) return;
    fetch('<?= BASE_URL ?>/deletesignature', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) loadSignatures();
    });
}

// I-reload ang list after mag-save
const _origSubmitBlob = submitBlob;
// Override para ma-refresh ang list pagkatapos mag-save
const origFetch = window.fetch;


// Load on page start
loadSignatures();


// ─── TAB SWITCHING ───────────────────────────────────────────────
function switchTab(tab) {
  const isDraw = tab === 'draw';
  document.getElementById('panel-draw').classList.toggle('hidden', !isDraw);
  document.getElementById('panel-upload').classList.toggle('hidden', isDraw);

  document.getElementById('tab-draw').className =
    'flex-1 py-3 text-sm font-medium transition-all ' +
    (isDraw ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-400 border-b-2 border-transparent hover:text-slate-600');
  document.getElementById('tab-upload').className =
    'flex-1 py-3 text-sm font-medium transition-all ' +
    (!isDraw ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-400 border-b-2 border-transparent hover:text-slate-600');

  document.getElementById('success-msg').classList.add('hidden');
}

// ─── CANVAS DRAWING ──────────────────────────────────────────────
const canvas  = document.getElementById('sig-canvas');
const ctx     = canvas.getContext('2d');
let drawing   = false;
let erasing   = false;
let hasDrawn  = false;
let undoStack = [];

// Resize canvas to match CSS size (important for HiDPI)
function resizeCanvas() {
  const rect = canvas.getBoundingClientRect();
  canvas.width  = rect.width  * window.devicePixelRatio;
  canvas.height = rect.height * window.devicePixelRatio;
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  ctx.lineCap   = 'round';
  ctx.lineJoin  = 'round';
  updatePen();
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

function updatePen() {
  ctx.strokeStyle = erasing ? 'rgba(0,0,0,1)' : document.getElementById('pen-color').value;
  ctx.lineWidth   = parseInt(document.getElementById('pen-size').value);
  ctx.globalCompositeOperation = erasing ? 'destination-out' : 'source-over';
}

function toggleEraser() {
  erasing = !erasing;
  const btn = document.getElementById('eraser-btn');
  btn.className = erasing
    ? 'px-3 py-1.5 rounded-lg text-xs font-medium border border-blue-300 text-blue-600 bg-blue-50 transition-all'
    : 'px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all';
  updatePen();
}

function getPos(e) {
  const rect = canvas.getBoundingClientRect();
  const src  = e.touches ? e.touches[0] : e;
  return { x: src.clientX - rect.left, y: src.clientY - rect.top };
}

function startDraw(e) {
  e.preventDefault();
  drawing = true;
  saveUndo();
  const p = getPos(e);

  // Draw a dot immediately on click/tap so single clicks leave a mark
  const size = parseInt(document.getElementById('pen-size').value);
  ctx.save();
  ctx.globalCompositeOperation = erasing ? 'destination-out' : 'source-over';
  ctx.fillStyle = erasing ? 'rgba(0,0,0,1)' : document.getElementById('pen-color').value;
  ctx.beginPath();
  ctx.arc(p.x, p.y, size / 2, 0, Math.PI * 2);
  ctx.fill();
  ctx.restore();

  // Then start the path for continued drawing
  ctx.beginPath();
  ctx.moveTo(p.x, p.y);

  if (!hasDrawn) {
    hasDrawn = true;
    document.getElementById('canvas-hint').style.display = 'none';
    document.getElementById('draw-upload-btn').disabled = false;
  }
}

function draw(e) {
  if (!drawing) return;
  e.preventDefault();
  const p = getPos(e);
  ctx.lineTo(p.x, p.y);
  ctx.stroke();
  if (!hasDrawn) {
    hasDrawn = true;
    document.getElementById('canvas-hint').style.display = 'none';
    document.getElementById('draw-upload-btn').disabled = false;
  }
}

function endDraw(e) {
  if (!drawing) return;
  drawing = false;
  ctx.closePath();
}

canvas.addEventListener('mousedown',  startDraw);
canvas.addEventListener('mousemove',  draw);
canvas.addEventListener('mouseup',    endDraw);
canvas.addEventListener('mouseleave', endDraw);
canvas.addEventListener('touchstart', startDraw, { passive: false });
canvas.addEventListener('touchmove',  draw,       { passive: false });
canvas.addEventListener('touchend',   endDraw);

function saveUndo() {
  undoStack.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
  if (undoStack.length > 30) undoStack.shift();
}

function undoDraw() {
  if (!undoStack.length) return;
  ctx.putImageData(undoStack.pop(), 0, 0);
  const isEmpty = isCanvasBlank();
  if (isEmpty) {
    hasDrawn = false;
    document.getElementById('canvas-hint').style.display = '';
    document.getElementById('draw-upload-btn').disabled = true;
  }
}

function isCanvasBlank() {
  const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
  return !data.some(v => v !== 0);
}

function clearCanvas() {
  saveUndo();
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  hasDrawn = false;
  document.getElementById('canvas-hint').style.display = '';
  document.getElementById('draw-upload-btn').disabled = true;
}

function submitDrawnSignature() {
  canvas.toBlob(blob => {
    if (!blob) return;
    submitBlob(blob, document.getElementById('draw-upload-btn'));
  }, 'image/png');
}

// ─── FILE UPLOAD ─────────────────────────────────────────────────
let selectedFile = null;

function handleFile(file) {
  if (!file) return;
  if (file.type !== 'image/png') { alert('Please upload a PNG file only.'); return; }
  if (file.size > 10 * 1024 * 1024) {
    alert('File is too large. Maximum size is 10 MB.'); return; }
  selectedFile = file;
  const reader = new FileReader();
  reader.onload = function(e) {
    const dataURL = e.target.result;
    document.getElementById('preview-img').src = dataURL;
    document.getElementById('preview-wrapper').classList.remove('hidden');
    document.getElementById('placeholder').classList.add('hidden');
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = (file.size / 1024).toFixed(1) + ' KB';
    document.getElementById('file-info').classList.remove('hidden');
    document.getElementById('upload-btn').disabled = false;
    checkTransparency(dataURL);
    document.getElementById('success-msg').classList.add('hidden');
  };
  reader.readAsDataURL(file);
}

function checkTransparency(dataURL) {
  const img = new Image();
  img.onload = function() {
    const c = document.createElement('canvas');
    c.width = img.width; c.height = img.height;
    const x = c.getContext('2d');
    x.drawImage(img, 0, 0);
    const d = x.getImageData(0, 0, c.width, c.height).data;
    let hasAlpha = false;
    for (let i = 3; i < d.length; i += 4) { if (d[i] < 255) { hasAlpha = true; break; } }
    document.getElementById('warn-opaque').classList.toggle('hidden', hasAlpha);
  };
  img.src = dataURL;
}

function handleDrop(event) {
  event.preventDefault();
  document.getElementById('drop-zone').classList.remove('border-blue-400','bg-blue-50');
  const file = event.dataTransfer.files[0];
  if (file) handleFile(file);
}

function clearFile() {
  selectedFile = null;
  document.getElementById('sig-input').value = '';
  document.getElementById('preview-wrapper').classList.add('hidden');
  document.getElementById('placeholder').classList.remove('hidden');
  document.getElementById('file-info').classList.add('hidden');
  document.getElementById('warn-opaque').classList.add('hidden');
  document.getElementById('success-msg').classList.add('hidden');
  document.getElementById('upload-btn').disabled = true;
}

function submitSignature() {
  if (!selectedFile) return;
  submitBlob(selectedFile, document.getElementById('upload-btn'));
}

// ─── SHARED SUBMIT ───────────────────────────────────────────────
function submitBlob(blob, btn) {
  btn.disabled = true;
  btn.innerHTML = `
    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
    Saving...
  `;

  const formData = new FormData();
  formData.append('signature', blob, 'signature.png');

  fetch('<?= BASE_URL; ?>/uploadsignature', { method: 'POST', body: formData })
    .then(res => res.json())
   .then(data => {
      if (data.success) {
        document.getElementById('success-msg').classList.remove('hidden');
        loadSignatures(); // ← i-reload agad pagkatapos ng successful save
      } else {
        alert(data.message || 'Upload failed. Please try again.');
      }
    })
    .catch(() => alert('An error occurred. Please try again.'))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
        </svg>
        ${btn.id === 'draw-upload-btn' ? 'Save Signature' : 'Upload Signature'}
      `;
    });
}
</script>