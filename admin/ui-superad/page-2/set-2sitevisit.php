<!-- sitevisit.php -->

<div class="mb-3">
    <p class="text-amber-700 text-[10px] font-semibold tracking-[0.15em] uppercase">Site Visit</p>
</div>
<div id="monSiteVisits" class="space-y-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
        <div class="h-3 w-full rounded bg-gray-100 animate-pulse mb-2"></div>
        <div class="h-3 w-3/4 rounded bg-gray-100 animate-pulse"></div>
    </div>
</div>

<script>
    function monVisitedBadge(visited) {
        return visited
            ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap bg-green-700 text-white border-green-200">Visited</span>`
            : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border whitespace-nowrap bg-red-700 text-white border-red-200">Not Visited</span>`;
    }

    // Tracker steps: Scheduled -> Ongoing/Visited -> Report Uploaded
    function monTrackerStatus(visit) {
        const hasPhotos = visit.photos.length > 0;
        if (visit.visited && hasPhotos) return 2;
        if (visit.visited && !hasPhotos) return 1;
        return 0;
    }

    function monRenderTracker(visit) {
        const steps = ['Scheduled', 'Site Visit Ongoing', 'Report Uploaded'];
        const currentStep = monTrackerStatus(visit);

        let html = '<div class="flex items-center justify-center w-full px-6 py-6">';

        steps.forEach((label, i) => {
            const done = i <= currentStep;

            const dot = done
                ? `<div class="w-12 h-12 rounded-full bg-amber-600 flex items-center justify-center text-white shrink-0 ring-4 ring-amber-100">
                       <i class="fa-solid fa-circle-check text-[22px] leading-none"></i>
                   </div>`
                : `<div class="w-12 h-12 rounded-full bg-white border-[3px] border-gray-200 shrink-0"></div>`;

            html += `
                <div class="flex flex-col items-center shrink-0" style="width:140px">
                    ${dot}
                    <span class="text-sm mt-2.5 font-semibold ${done ? 'text-amber-700' : 'text-gray-400'} text-center leading-tight whitespace-nowrap">${label}</span>
                </div>
            `;

            if (i < steps.length - 1) {
                html += `<div class="flex-1 h-1 -mt-8 ${i < currentStep ? 'bg-amber-500' : 'bg-gray-200'} rounded-full"></div>`;
            }
        });

        html += '</div>';
        return html;
    }

    function monRenderSiteVisitCard(visit, label) {
        const photosHtml = visit.photos.length
            ? `<div class="grid grid-cols-6 sm:grid-cols-8 gap-2 mt-3">
                ${visit.photos.map(p => `
                    <a href="${monEscapeHtml(p)}" target="_blank" rel="noopener" class="block aspect-square border border-gray-200 rounded overflow-hidden hover:border-amber-600 transition-colors">
                        <img src="${monEscapeHtml(p)}" class="w-full h-full object-cover">
                    </a>
                `).join('')}
               </div>`
            : `<p class="text-xs text-gray-400 mt-2">No photographs attached.</p>`;

        return `
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <p class="text-[10px] text-gray-400 font-semibold tracking-[0.1em] uppercase">${label}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Logged ${monFormatDateTimeLong(visit.created_at)} · ${monEscapeHtml(visit.designer_name)}</p>
                    </div>
                    ${monVisitedBadge(visit.visited)}
                </div>
                <div class="px-5 py-2 border-b border-gray-100 bg-gray-50/60">
                    ${monRenderTracker(visit)}
                </div>
                <div class="px-5 py-3">
                    <p class="text-[13px] text-gray-800">${monEscapeHtml(visit.address)}</p>
                    <p class="text-xs text-gray-400 mt-1">Visit date: ${monFormatDateTimeLong(visit.visit_datetime)}</p>
                    ${photosHtml}
                </div>
            </div>
        `;
    }

    function monRenderSiteVisits(siteVisits) {
        const container = document.getElementById('monSiteVisits');
        if (!siteVisits || siteVisits.length === 0) {
            container.innerHTML = `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                    <p class="text-sm text-gray-400">No site visit recorded yet for this inquiry.</p>
                </div>
            `;
            return;
        }
        const ordered = [...siteVisits].reverse();
        const total = siteVisits.length;
        container.innerHTML = ordered
            .map((visit, i) => monRenderSiteVisitCard(visit, total > 1 ? `Visit ${total - i}` : 'Site Visit'))
            .join('');
    }
</script>