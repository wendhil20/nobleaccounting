<?php
// feedback2d.php
?>
<script>
    function q2dFeedbackItemsHtml(feedback) {
        return feedback.map(f => `
            <div class="border-l-2 ${f.is_resolved ? 'border-gray-300 bg-gray-50' : 'border-red-400 bg-red-50'} px-3 py-2 mb-2 last:mb-0 rounded-r">
                <p class="text-xs text-gray-800 whitespace-pre-line">${q2dEscapeHtml(f.message)}</p>
                <p class="text-[10.5px] text-gray-400 mt-1">
                    Cutting · ${q2dEscapeHtml(f.created_by_name)} · ${q2dEscapeHtml(f.created_at)}
                    ${f.is_resolved ? '<span class="text-green-700 font-semibold ml-1">Resolved</span>' : '<span class="text-red-700 font-semibold ml-1">Needs adjustment</span>'}
                </p>
            </div>
        `).join('');
    }

    // Upload widget: only shown to the designer when there's at least
    // one unresolved feedback item. Sending here does the same thing as
    // the Edit button in the Files table (unlock + upload) — just
    // reachable directly from the sidebar instead of scrolling down.
    function q2dFeedbackUploadHtml() {
        return `
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-[11px] font-semibold text-gray-500 mb-2">Send revised 2D file to Cutting</p>
                <label for="q2dFeedbackFileInput"
                    class="flex flex-col items-center justify-center gap-1.5 border border-dashed border-red-300 rounded-lg py-4 px-3 cursor-pointer hover:border-red-500 hover:bg-red-50 transition-colors">
                    <i class="fa-solid fa-file-arrow-up text-red-500"></i>
                    <span id="q2dFeedbackFileLabel" class="text-xs text-gray-600 text-center">Click to attach revised 2D PDF</span>
                    <span class="text-[10px] text-gray-400">PDF only, max 15MB</span>
                </label>
                <input id="q2dFeedbackFileInput" type="file" accept="application/pdf" class="hidden" onchange="q2dSendRevisedPdf(this)">
            </div>
        `;
    }

    function q2dRenderCuttingFeedback(feedback) {
        const badge = document.getElementById('q2dFeedbackBadge');
        const body = document.getElementById('q2dFeedbackSidebarBody');
        if (!badge || !body) return '';

        const unresolvedCount = (feedback || []).filter(f => !f.is_resolved).length;

        if (unresolvedCount > 0) {
            badge.textContent = unresolvedCount;
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }

        const canUpload = unresolvedCount > 0 && !Q2D_IS_SALES;

        body.innerHTML = (feedback && feedback.length)
            ? q2dFeedbackItemsHtml(feedback) + (canUpload ? q2dFeedbackUploadHtml() : '')
            : '<p class="text-sm text-gray-400 italic">No feedback yet.</p>';

        return '';
    }

    // Unlocks the 2D slot (only possible while there's unresolved
    // feedback — enforced server-side) then immediately uploads the
    // chosen file as the revision.
    async function q2dSendRevisedPdf(inputEl) {
        if (!inputEl.files.length) return;
        const file = inputEl.files[0];
        const label = document.getElementById('q2dFeedbackFileLabel');
        if (label) label.textContent = `Uploading ${file.name}...`;

        try {
            const unlockForm = new FormData();
            unlockForm.append('action', 'unlock_slot');
            unlockForm.append('inquiry_id', Q2D_INQUIRY_ID);
            unlockForm.append('slot', '2d');
            const unlockRes = await fetch(Q2D_AJAX_URL, { method: 'POST', body: unlockForm });
            const unlockData = await unlockRes.json();

            if (!unlockData.success) {
                crmShowToast(unlockData.message || 'Unable to open this file for revision.', 'error');
                if (label) label.textContent = 'Click to attach revised 2D PDF';
                inputEl.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('action', 'save_slot');
            formData.append('inquiry_id', Q2D_INQUIRY_ID);
            formData.append('slot', '2d');
            formData.append('design_2d_pdf', file);

            const res = await fetch(Q2D_AJAX_URL, { method: 'POST', body: formData });
            const data = await res.json();

            if (!data.success) {
                crmShowToast(data.message || 'Something went wrong.', 'error');
                if (label) label.textContent = 'Click to attach revised 2D PDF';
                inputEl.value = '';
                return;
            }

            crmShowToast(data.message || 'Revised file sent to Cutting.');
            inputEl.value = '';
            q2dLastSignature = '';
            await q2dFetchState();
        } catch (e) {
            console.error('q2dSendRevisedPdf:', e);
            crmShowToast('Connection error. Please try again.', 'error');
            if (label) label.textContent = 'Click to attach revised 2D PDF';
            inputEl.value = '';
        }
    }

    function q2dOpenFeedbackSidebar() {
        document.getElementById('q2dFeedbackOverlay').classList.remove('opacity-0', 'pointer-events-none');
        document.getElementById('q2dFeedbackSidebar').classList.remove('translate-x-full');
    }

    function q2dCloseFeedbackSidebar() {
        document.getElementById('q2dFeedbackOverlay').classList.add('opacity-0', 'pointer-events-none');
        document.getElementById('q2dFeedbackSidebar').classList.add('translate-x-full');
    }

    function q2dToggleFeedbackSidebar() {
        const sidebar = document.getElementById('q2dFeedbackSidebar');
        if (sidebar.classList.contains('translate-x-full')) {
            q2dOpenFeedbackSidebar();
        } else {
            q2dCloseFeedbackSidebar();
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') q2dCloseFeedbackSidebar();
    });
</script>