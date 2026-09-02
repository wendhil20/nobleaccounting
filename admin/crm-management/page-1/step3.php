<?php
//step3.php
?>
<div class="crm-step-panel" id="crm-step-3">
    <p class="text-xs text-gray-400 mb-4">Who's handling this.</p>

    <div class="space-y-5">
        <!-- Sales Staff (Auto Assigned) -->
        <div>
            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                Sales Staff <span class="text-gray-400 normal-case">(auto-assigned)</span>
            </label>
            <input type="text" readonly value="<?= htmlspecialchars($currentSalesName) ?>"
                class="w-full px-3 py-2.5 text-sm text-gray-500 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed">
        </div>

        <!-- Designer Assign -->
        <div>
            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                Designer Assign
            </label>
            <select name="designer_id" id="crm_designer_id"
                class="w-full px-3 py-2.5 text-sm text-gray-800 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-600 focus:border-amber-600 transition">
                <option value="">Select designer</option>
                <?php foreach ($designers as $d): ?>
                    <option value="<?= intval($d['id']) ?>" <?= (intval($_POST['designer_id'] ?? 0) === intval($d['id'])) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Contract Amount: removed from here on purpose — set later by
             Sales, on the 2D & Quotation page, once the Quotation file is
             marked "Done". See crm2dquotationajax.php (save_contract_amount). -->
        <div>
            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                Contract Amount
            </label>
            <p class="text-xs text-gray-400 italic px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-md">
                This will be set by Sales once the Quotation file is marked "Done" during 2D &amp; Quotation.
            </p>
        </div>
    </div>

    <div class="flex justify-between mt-6">
        <button type="button" onclick="crmGoToStep(2)"
            class="text-gray-500 hover:text-gray-700 text-sm font-medium px-4 py-2.5 rounded-md border border-gray-300 hover:border-gray-400 transition">
            Back
        </button>
        <button type="button" onclick="crmGoToStep4()"
            class="bg-gray-900 hover:bg-gray-800 text-white font-medium px-6 py-2.5 rounded-md text-sm uppercase tracking-wide transition">
            Next: Review
        </button>
    </div>
</div>