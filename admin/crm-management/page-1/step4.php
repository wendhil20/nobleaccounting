<?php
//step4.php
?>
<div class="crm-step-panel" id="crm-step-4">
    <p class="text-xs text-gray-400 mb-4">Please confirm the details before submitting.</p>

    <div id="crm_review_content" class="mb-6"></div>

    <div class="pt-4 border-t border-[#E4DFD1] flex items-center justify-between">
        <button type="button" onclick="crmGoToStep(3)"
            class="text-gray-500 hover:text-gray-700 text-sm font-medium px-4 py-2.5 rounded-md border border-gray-300 hover:border-gray-400 transition">
             Back
        </button>
        <button type="submit" name="submit_inquiry"
            class="px-6 py-2.5 text-sm font-medium tracking-wide uppercase text-white bg-amber-700 rounded-md hover:bg-amber-600 active:opacity-80 transition">
            Submit Form
        </button>
    </div>
</div>