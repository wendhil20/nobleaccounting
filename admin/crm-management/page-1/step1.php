<?php
//step1.php
?>
<div class="crm-step-panel active" id="crm-step-1">
    <p class="text-xs text-gray-400 mb-4">Basic client details and address.</p>

    <div class="space-y-5">
        <!-- Client Name -->
        <div>
            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                Client Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="client_name" id="crm_client_name"
                value="<?= htmlspecialchars($_POST['client_name'] ?? '') ?>" placeholder="Juan Dela Cruz"
                class="w-full px-3 py-2.5 text-sm text-gray-800 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-600 focus:border-amber-600 transition">
        </div>

        <!-- House No / Street -->
        <div>
            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                House No. / Street <span class="text-red-500">*</span>
            </label>
            <input type="text" name="house_street" id="crm_house_street" placeholder="e.g. Unit 2B, 123 Rizal Street"
                class="w-full px-3 py-2.5 text-sm text-gray-800 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-600 focus:border-amber-600 transition">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Region -->
            <div>
                <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                    Region
                </label>
                <select id="crm_region"
                    class="w-full px-3 py-2.5 text-sm text-gray-800 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-600 focus:border-amber-600 transition">
                    <option value="">— Select Region —</option>
                </select>
            </div>

            <!-- Province -->
            <div id="crm_province_wrapper">
                <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                    Province
                </label>
                <select id="crm_province" disabled
                    class="w-full px-3 py-2.5 text-sm text-gray-800 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-600 focus:border-amber-600 disabled:bg-gray-100 transition">
                    <option value="">— Select Province —</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- City / Municipality -->
            <div>
                <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                    City / Municipality <span class="text-red-500">*</span>
                </label>
                <select id="crm_city" disabled
                    class="w-full px-3 py-2.5 text-sm text-gray-800 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-600 focus:border-amber-600 disabled:bg-gray-100 transition">
                    <option value="">— Select City / Municipality —</option>
                </select>
            </div>

            <!-- Barangay -->
            <div>
                <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                    Barangay <span class="text-red-500">*</span>
                </label>
                <select id="crm_barangay" disabled
                    class="w-full px-3 py-2.5 text-sm text-gray-800 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-600 focus:border-amber-600 disabled:bg-gray-100 transition">
                    <option value="">— Select Barangay —</option>
                </select>
            </div>
        </div>

        <!-- Contact Number -->
        <div>
            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-500 mb-1.5">
                Contact Number <span class="text-red-500">*</span>
            </label>
            <input type="text" name="contact_number" id="crm_contact_number"
                value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" placeholder="09XXXXXXXXX"
                inputmode="numeric" pattern="[0-9]*" maxlength="11"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);"
                class="w-full px-3 py-2.5 text-sm text-gray-800 bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-amber-600 focus:border-amber-600 transition">
        </div>

        <!-- Hidden fields: nakukuha mula sa selected labels ng region/province/city/barangay -->
        <input type="hidden" name="region_name" id="crm_region_name">
        <input type="hidden" name="province_name" id="crm_province_name">
        <input type="hidden" name="city_name" id="crm_city_name">
        <input type="hidden" name="barangay_name" id="crm_barangay_name">
        <input type="hidden" name="postal_code" id="crm_postal_code">
    </div>

    <div class="flex justify-end mt-6">
        <button type="button" onclick="crmGoToStep2()"
            class="bg-gray-900 hover:bg-gray-800 text-white font-medium px-6 py-2.5 rounded-md text-sm uppercase tracking-wide transition">
            Next: Project Details
        </button>
    </div>
</div>