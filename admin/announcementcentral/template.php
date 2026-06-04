<!-- template.php -->

<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-800">Create Announcement</h1>
    <p class="text-sm text-gray-400 mt-1">Choose a template and fill in the details</p>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">

    <!-- Template 1 -->
    <label class="cursor-pointer">
        <input type="radio" name="template" value="1" class="hidden template-radio" checked>
        <div class="template-card border-2 border-orange-500 rounded-xl overflow-hidden transition-all" data-template="1">
            <div class="bg-orange-500 px-4 py-3">
                <p class="text-[10px] text-white/80 uppercase tracking-widest">Template 1</p>
                <p class="text-sm font-semibold text-white mt-0.5">General Announcement</p>
            </div>
            <div class="px-4 py-3 bg-white">
                <p class="text-xs text-gray-400">Orange header with title and body message. Best for regular company updates.</p>
            </div>
        </div>
    </label>

    <!-- Template 2 -->
    <label class="cursor-pointer">
        <input type="radio" name="template" value="2" class="hidden template-radio">
        <div class="template-card border-2 border-transparent rounded-xl overflow-hidden transition-all hover:border-red-300" data-template="2">
            <div class="bg-red-500 px-4 py-3 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-white text-xs"></i>
                <div>
                    <p class="text-[10px] text-white/80 uppercase tracking-widest">Template 2</p>
                    <p class="text-sm font-semibold text-white mt-0.5">Urgent Alert</p>
                </div>
            </div>
            <div class="px-4 py-3 bg-white border-l-4 border-red-500">
                <p class="text-xs text-gray-400">Red alert style with urgent badge. Best for deadlines and important notices.</p>
            </div>
        </div>
    </label>

    <!-- Template 3 -->
    <label class="cursor-pointer">
        <input type="radio" name="template" value="3" class="hidden template-radio">
        <div class="template-card border-2 border-transparent rounded-xl overflow-hidden transition-all hover:border-slate-400" data-template="3">
            <div class="grid grid-cols-[60px_1fr]">
                <div class="bg-slate-800 flex flex-col items-center justify-center py-3 gap-0.5">
                    <span class="text-lg font-semibold text-orange-400 leading-none"><?= date('d') ?></span>
                    <span class="text-[9px] text-slate-400 uppercase"><?= date('M') ?></span>
                </div>
                <div class="bg-white px-3 py-3">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">Template 3</p>
                    <p class="text-sm font-semibold text-gray-800 mt-0.5">Event / Holiday</p>
                </div>
            </div>
        </div>
    </label>

</div>

<!-- Form -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">

    <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1.5">Title</label>
        <input type="text" id="ann-title" placeholder="Enter announcement title..."
            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-200 transition-all">
    </div>

    <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1.5">Message</label>
        <textarea id="ann-body" rows="4" placeholder="Enter announcement message..."
            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-200 resize-none transition-all"></textarea>
    </div>

    <!-- Expiration -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1.5">
                Expires On <span class="text-red-400">*</span>
            </label>
            <input type="date" id="ann-expire-date"
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-200 transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1.5">
                Expires At <span class="text-red-400">*</span>
            </label>
            <input type="time" id="ann-expire-time"
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-200 transition-all">
        </div>
    </div>

    <!-- Live Preview -->
    <div>
        <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1.5">Preview</label>
        <div id="ann-preview" class="border border-gray-200 rounded-xl overflow-hidden"></div>
    </div>

    <div class="flex justify-end gap-3 pt-2">
        <button onclick="submitAnnouncement()"
            class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-all">
            <i class="fa-solid fa-bullhorn text-xs"></i>
            Post Announcement
        </button>
    </div>

</div>

<script>
    let selectedTemplate = 1;

    document.querySelectorAll('.template-radio').forEach(radio => {
        radio.addEventListener('change', function () {
            selectedTemplate = parseInt(this.value);
            document.querySelectorAll('.template-card').forEach(card => {
                card.classList.remove('border-orange-500', 'border-red-500', 'border-slate-600');
                card.classList.add('border-transparent');
            });
            const colors = { 1: 'border-orange-500', 2: 'border-red-500', 3: 'border-slate-600' };
            this.closest('label').querySelector('.template-card')
                .classList.replace('border-transparent', colors[selectedTemplate]);
            updatePreview();
        });
    });

    document.getElementById('ann-title').addEventListener('input', updatePreview);
    document.getElementById('ann-body').addEventListener('input', updatePreview);

    function updatePreview() {
        const title = document.getElementById('ann-title').value || 'Announcement title here';
        const body  = document.getElementById('ann-body').value || 'Announcement message will appear here.';
        const today = new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
        const day   = new Date().getDate();
        const month = new Date().toLocaleString('en-PH', { month: 'short' }).toUpperCase();

        const expireDate = document.getElementById('ann-expire-date').value;
        const expireTime = document.getElementById('ann-expire-time').value;
        const expireLabel = (expireDate && expireTime)
            ? `<span class="text-[10px] text-orange-500 font-semibold">
                <i class="fa-solid fa-clock text-[9px]"></i>
                Expires: ${new Date(expireDate + 'T' + expireTime).toLocaleString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
               </span>`
            : '';

        const previews = {
            1: `
                <div class="bg-orange-500 px-5 py-4">
                    <p class="text-[10px] text-white/70 uppercase tracking-widest">General Announcement</p>
                    <h2 class="text-base font-bold text-white mt-1">${title}</h2>
                </div>
                <div class="px-5 py-4 bg-white">
                    <p class="text-sm text-gray-600">${body}</p>
                    <div class="flex items-center justify-between mt-3">
                        <p class="text-[10px] text-gray-400">${today}</p>
                        ${expireLabel}
                    </div>
                </div>`,

            2: `
                <div class="bg-red-500 px-5 py-3 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-white text-xs"></i>
                    <p class="text-[10px] text-white/80 uppercase tracking-widest">Urgent Alert</p>
                </div>
                <div class="px-5 py-4 bg-white border-l-4 border-red-500">
                    <h3 class="text-sm font-bold text-gray-800 mb-1">${title}</h3>
                    <p class="text-sm text-gray-600">${body}</p>
                    <div class="flex items-center gap-2 mt-3 flex-wrap">
                        <span class="bg-red-100 text-red-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Urgent</span>
                        <span class="text-[10px] text-gray-400">${today}</span>
                        ${expireLabel}
                    </div>
                </div>`,

            3: `
                <div class="grid" style="grid-template-columns: 80px 1fr;">
                    <div class="bg-slate-800 flex flex-col items-center justify-center py-4 gap-0.5">
                        <span class="text-2xl font-bold text-orange-400 leading-none">${day}</span>
                        <span class="text-[10px] text-slate-400 uppercase">${month}</span>
                    </div>
                    <div class="px-5 py-4 bg-white">
                        <p class="text-[10px] text-gray-400 uppercase tracking-widest">Event / Holiday</p>
                        <h3 class="text-sm font-bold text-gray-800 mt-1 mb-1">${title}</h3>
                        <p class="text-sm text-gray-600">${body}</p>
                        <div class="mt-2">${expireLabel}</div>
                    </div>
                </div>`
        };

        document.getElementById('ann-preview').innerHTML = previews[selectedTemplate];
    }

    document.getElementById('ann-expire-date').addEventListener('change', updatePreview);
    document.getElementById('ann-expire-time').addEventListener('change', updatePreview);

    function submitAnnouncement() {
        const title      = document.getElementById('ann-title').value.trim();
        const body       = document.getElementById('ann-body').value.trim();
        const expireDate = document.getElementById('ann-expire-date').value;
        const expireTime = document.getElementById('ann-expire-time').value;

        if (!title || !body) {
            alert('Please fill in the title and message.');
            return;
        }
        if (!expireDate || !expireTime) {
            alert('Please set an expiration date and time.');
            return;
        }

        const btn = document.querySelector('button[onclick="submitAnnouncement()"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Posting...';

        const formData = new FormData();
        formData.append('template',   selectedTemplate);
        formData.append('title',      title);
        formData.append('body',       body);
        formData.append('expires_at', `${expireDate} ${expireTime}:00`);

        fetch('<?= BASE_URL ?>/saveannouncement', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-6 right-6 z-[999] flex items-center gap-3 bg-green-500 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg opacity-0 transition-all duration-300';
                toast.innerHTML = '<i class="fa-solid fa-circle-check"></i> Announcement posted!';
                document.body.appendChild(toast);
                setTimeout(() => toast.classList.replace('opacity-0', 'opacity-100'), 10);

                document.getElementById('ann-title').value       = '';
                document.getElementById('ann-body').value        = '';
                document.getElementById('ann-expire-date').value = '';
                document.getElementById('ann-expire-time').value = '';
                updatePreview();

                setTimeout(() => {
                    toast.classList.replace('opacity-100', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-bullhorn text-xs"></i> Post Announcement';
                }, 2000);
            } else {
                alert('Failed to post: ' + (data.error ?? 'Unknown error'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-bullhorn text-xs"></i> Post Announcement';
            }
        })
        .catch(() => {
            alert('Something went wrong. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-bullhorn text-xs"></i> Post Announcement';
        });
    }

    updatePreview();
</script>