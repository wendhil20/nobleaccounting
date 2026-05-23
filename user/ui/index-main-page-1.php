<?php
// user/ui/index-main-page-1.php

include ROOT_PATH . '/network/connect.php';

if (empty($_SESSION['logged_in'])) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

function generateControlNo($conn)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    do {
        $rand = '';
        for ($i = 0; $i < 8; $i++) {
            $rand .= $chars[rand(0, strlen($chars) - 1)];
        }
        $control = 'NHREQUEST-' . $rand;
        $stmt = $conn->prepare("SELECT id FROM noblebudgetrequest WHERE control_no = ?");
        $stmt->bind_param("s", $control);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    return $control;
}

$control_no = generateControlNo($conn);
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Request Form NobleHome</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/user/navigation/top.php'; ?>
</head>

<body class="min-h-screen relative"
    style="background-image: url('<?= BASE_URL ?>/icon/building2.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <div class="absolute inset-0 bg-black/50 z-0"></div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- DESKTOP FORM (hidden on mobile)                    -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="hidden md:flex items-center justify-center px-4 py-12 relative z-10 min-h-screen">
        <div class="max-w-5xl w-full mx-auto">
            <div class="bg-white border border-gray-300 shadow-md rounded-sm overflow-hidden">

                <!-- Header -->
                <div class="grid grid-cols-[1fr_auto] border-b-2 border-gray-800">
                    <div class="flex items-center gap-4 px-6 py-4 border-r-2 border-gray-800">
                        <div class="w-14 h-14 shrink-0">
                            <img src="<?= BASE_URL ?>/icon/logo.png" alt="Noblehome Logo"
                                class="w-full h-full object-contain">
                        </div>
                        <div>
                            <h1 class="font-bold text-sm uppercase tracking-wide leading-tight">Noblehome Construction
                                Corporation</h1>
                            <p class="text-[10px] text-gray-500 mt-1 leading-relaxed">
                                1181 MC Premiere Bldg., EDSA Bldg., EDSA Balintawak Quezon City<br>
                                noblehomeconsl.ph@gmail.com &nbsp;|&nbsp; Tel. No. 02-88221295 &nbsp;|&nbsp; Cell. No.
                                0968-591-6544
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center justify-center px-6 py-2 border-b-2 border-gray-800">
                            <h2 class="font-bold text-sm uppercase tracking-widest whitespace-nowrap">Budget Request
                                Form</h2>
                        </div>
                        <div class="flex flex-row flex-1 text-[10px]">
                            <div class="flex flex-col border-r-2 border-gray-800 flex-1">
                                <span
                                    class="bg-orange-500 text-white font-bold px-4 py-1 uppercase tracking-wider text-center border-b-2 border-gray-800">Control
                                    No.</span>
                                <input type="text" value="<?= htmlspecialchars($control_no) ?>" readonly
                                    class="flex-1 px-4 py-1 font-mono text-xs outline-none bg-gray-50 text-center cursor-not-allowed">
                            </div>
                            <div class="flex flex-col flex-1">
                                <span
                                    class="bg-orange-500 text-white font-bold px-4 py-1 uppercase tracking-wider text-center border-b-2 border-gray-800">Date:</span>
                                <input type="date" value="<?= $today ?>" readonly
                                    class="flex-1 px-4 py-1 font-mono text-xs outline-none bg-gray-50 text-center cursor-not-allowed">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requestor + Purpose -->
                <div class="grid grid-cols-2 border-b-2 border-gray-800">
                    <div class="flex items-center gap-2 px-6 py-3 border-r-2 border-gray-800">
                        <span
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Requestor
                            Name:</span>
                        <input type="text" id="d-requestor-name"
                            class="flex-1 border-b-2 border-gray-400 outline-none text-sm py-0.5 focus:border-orange-500 bg-transparent transition-colors">
                    </div>
                    <div class="flex items-center gap-2 px-6 py-3">
                        <span
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Purpose
                            of Request:</span>
                        <input type="text" id="d-purpose"
                            class="flex-1 border-b-2 border-gray-400 outline-none text-sm py-0.5 focus:border-orange-500 bg-transparent transition-colors">
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto max-h-[220px] overflow-y-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-orange-500 text-white text-[11px] font-bold uppercase tracking-wider">
                                <th class="w-10 px-3 py-2 border-r border-orange-400 text-center">No.</th>
                                <th class="px-4 py-2 border-r border-orange-400 text-left">Items / Description</th>
                                <th class="px-4 py-2 border-r border-orange-400 text-left">Purpose</th>
                                <th class="w-24 px-4 py-2 border-r border-orange-400 text-center">Quantity</th>
                                <th class="w-28 px-4 py-2 border-r border-orange-400 text-center">Unit Price</th>
                                <th class="w-28 px-4 py-2 border-r border-orange-400 text-center">Amount</th>
                                <th class="px-4 py-2 text-left">Notes</th>
                            </tr>
                        </thead>
                        <tbody id="d-item-rows"></tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-800 bg-gray-50">
                                <td colspan="5"
                                    class="px-4 py-2 font-bold text-xs uppercase tracking-widest text-right border-r border-gray-300">
                                    Total:</td>
                                <td class="px-4 py-2 font-bold font-mono text-right" id="d-grand-total">₱ 0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Add Row + Submit -->
                <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between">
                    <button onclick="dAddRow()"
                        class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 active:scale-95 text-white text-xs font-semibold px-4 py-2 rounded transition-all">
                        <i class="fa-solid fa-plus text-[10px]"></i> Add Item
                    </button>
                    <div class="flex items-center gap-3">
                        <!-- Follow up toggle -->
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" id="d-follow-up-toggle" class="sr-only peer">
                                <div
                                    class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-orange-500 transition-colors">
                                </div>
                                <div
                                    class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-all peer-checked:translate-x-4">
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-gray-500 peer-checked:text-orange-500"
                                id="d-follow-up-label">Follow up attachment</span>
                        </label>
                        <span class="text-[10px] text-gray-400 font-mono">20260425-BRF-v1</span>
                        <button onclick="dOpenSubmitModal()"
                            class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 active:scale-95 text-white text-xs font-semibold px-4 py-2 rounded transition-all">
                            <i class="fa-solid fa-paper-plane text-[10px]"></i> Submit Request
                        </button>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="px-6 py-4 border-t-2 border-gray-800">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-600 mb-3">
                        <i class="fa-solid fa-paperclip mr-1"></i> Attachments
                        <span class="font-normal text-gray-400 normal-case tracking-normal ml-1">(Images will be
                            converted to WebP)</span>
                    </p>
                    <div id="d-drop-zone"
                        class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-orange-400 hover:bg-orange-50 transition-all group">
                        <i
                            class="fa-solid fa-cloud-arrow-up text-2xl text-gray-300 group-hover:text-orange-400 transition-colors mb-2"></i>
                        <span
                            class="text-xs text-gray-400 group-hover:text-orange-500 transition-colors font-medium">Click
                            or drag images here</span>
                        <span class="text-[10px] text-gray-300 mt-0.5">JPG, PNG, WEBP — multiple allowed</span>
                    </div>
                    <input type="file" id="d-attachment-input" accept="image/jpeg,image/png,image/webp,application/pdf"
                        multiple class="hidden">

                    <div id="d-attachment-preview" class="mt-3 flex flex-wrap gap-2"></div>
                </div>

                <!-- Signatures -->
                <div class="grid grid-cols-2 border-t-2 border-gray-800">
                    <div class="px-8 py-5 border-r-2 border-gray-800">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-regular fa-circle-user text-gray-400 text-lg"></i>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Approved
                                By:</span>
                        </div>
                        <div class="border-b-2 border-gray-400 mt-8 mb-1"></div>
                        <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">Head</p>
                    </div>
                    <div class="px-8 py-5">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-regular fa-circle-user text-gray-400 text-lg"></i>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Received
                                By:</span>
                        </div>
                        <div class="border-b-2 border-gray-400 mt-8 mb-1"></div>
                        <p class="text-[10px] text-center text-gray-500 font-medium uppercase tracking-wider">&nbsp;</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Submit Modal -->
    <div id="d-submit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-sm uppercase tracking-widest text-gray-800">Submit Request</h3>
                <button onclick="dCloseSubmitModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-gray-500">Select the head you want to send this request to:</p>
                <div id="d-heads-list" class="space-y-2 max-h-48 overflow-y-auto">
                    <div class="text-center text-gray-400 text-sm py-4"><i class="fa-solid fa-spinner fa-spin mr-2"></i>
                        Loading...</div>
                </div>
                <div id="d-selected-head-display"
                    class="hidden bg-orange-50 border border-orange-200 rounded-lg px-4 py-3">
                    <p class="text-xs text-orange-600 font-semibold uppercase tracking-wider mb-1">Sending to:</p>
                    <p id="d-selected-head-name" class="text-sm font-bold text-gray-800"></p>
                    <p id="d-selected-head-role" class="text-[10px] text-gray-500 uppercase tracking-wide"></p>
                </div>

                <!-- Category selector -->
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Request Category</p>
                    <div class="grid grid-cols-3 gap-2">
                        <div onclick="dSelectCategory('project', this)"
                            class="d-cat-card flex flex-col items-center gap-1 px-2 py-3 border-2 border-transparent rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all text-center">
                            <i class="fa-solid fa-helmet-safety text-gray-300 text-lg d-cat-icon"></i>
                            <span class="text-xs font-semibold text-gray-600">Project</span>
                            <span class="text-[9px] text-gray-400">Tied to a project</span>
                        </div>
                        <div onclick="dSelectCategory('client', this)"
                            class="d-cat-card flex flex-col items-center gap-1 px-2 py-3 border-2 border-transparent rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all text-center">
                            <i class="fa-solid fa-user-tie text-gray-300 text-lg d-cat-icon"></i>
                            <span class="text-xs font-semibold text-gray-600">Client</span>
                            <span class="text-[9px] text-gray-400">Tied to a client</span>
                        </div>
                        <div onclick="dSelectCategory('nhcc', this)"
                            class="d-cat-card flex flex-col items-center gap-1 px-2 py-3 border-2 border-transparent rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all text-center">
                            <i class="fa-solid fa-building text-gray-300 text-lg d-cat-icon"></i>
                            <span class="text-xs font-semibold text-gray-600">NHCC</span>
                            <span class="text-[9px] text-gray-400">Internal use</span>
                        </div>
                    </div>
                    <!-- Reference input (slides in for project/client) -->
                    <div id="d-category-ref-section" class="hidden mt-3">
                        <label id="d-category-ref-label"
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Project
                            name</label>
                        <input type="text" id="d-category-ref-input" placeholder="Enter project name..."
                            class="w-full border-b-2 border-gray-300 outline-none text-sm py-1.5 bg-transparent focus:border-orange-500 transition-colors">
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
                <button onclick="dCloseSubmitModal()"
                    class="text-sm text-gray-500 hover:text-gray-700 font-medium px-4 py-2 rounded transition-all">Cancel</button>
                <button id="d-confirm-submit-btn" onclick="dConfirmSubmit()" disabled
                    class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded transition-all">
                    <i class="fa-solid fa-paper-plane text-xs"></i> Confirm & Submit
                </button>
            </div>
        </div>
    </div>


    <!-- ═══════════════════════════════════════════════════ -->
    <!-- MOBILE WIZARD (shown on mobile only)               -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div class="md:hidden flex flex-col min-h-dvh relative z-10 pt-16 overflow-hidden">

        <!-- Top bar -->
        <div class="bg-orange-500 px-4 pt-5 pb-4 flex-shrink-0 sticky z-50">
            <!-- Logo row -->
            <div class="flex items-center gap-2 mb-4">
                <img src="<?= BASE_URL ?>/icon/logo.png" alt="Logo" class="h-8 w-auto object-contain">
                <div class="w-px h-7 bg-white/30"></div>
                <div>
                    <p class="text-white font-bold text-xs leading-tight">NobleHome</p>
                    <p class="text-white/70 text-[10px]">Budget Request Form</p>
                </div>
                <div class="ml-auto text-right">
                    <p class="text-white/70 text-[9px] uppercase tracking-wider">Control No.</p>
                    <p class="text-white font-mono text-[10px] font-bold"><?= htmlspecialchars($control_no) ?></p>
                </div>
            </div>

            <!-- Step indicator -->
            <div class="flex items-center gap-1.5 mb-3">
                <div id="m-dot-1" class="h-2 rounded-full bg-white transition-all duration-300" style="width:24px">
                </div>
                <div id="m-dot-2" class="h-2 rounded-full bg-white/35 transition-all duration-300" style="width:8px">
                </div>
                <div id="m-dot-3" class="h-2 rounded-full bg-white/35 transition-all duration-300" style="width:8px">
                </div>
                <div id="m-dot-4" class="h-2 rounded-full bg-white/35 transition-all duration-300" style="width:8px">
                </div>
                <span class="ml-auto text-white/80 text-[11px] font-semibold" id="m-step-label">Step 1 of 4</span>
            </div>

            <!-- Step title -->
            <h2 class="text-white font-bold text-lg leading-tight" id="m-step-title">Request Info</h2>
            <p class="text-white/60 text-xs mt-0.5" id="m-step-sub">Fill in your name and purpose</p>
        </div>

        <!-- Step panels -->
        <div class="flex-1 overflow-y-auto bg-slate-100 overflow-x-hidden">

            <!-- ── Step 1: Info ── -->
            <div id="m-step-1" class="p-4 space-y-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 space-y-4">
                    <div>
                        <label
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Requestor
                            Name</label>
                        <input type="text" id="m-requestor-name" placeholder="Enter your full name"
                            class="w-full border-b-2 border-gray-200 focus:border-orange-500 outline-none text-sm py-1.5 bg-transparent transition-colors">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Purpose
                            of Request</label>
                        <input type="text" id="m-purpose" placeholder="What is this request for?"
                            class="w-full border-b-2 border-gray-200 focus:border-orange-500 outline-none text-sm py-1.5 bg-transparent transition-colors">
                    </div>
                    <div>
                        <label
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Date</label>
                        <input type="date" value="<?= $today ?>" readonly
                            class="w-full border-b-2 border-gray-200 outline-none text-sm py-1.5 bg-transparent text-gray-500 cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- ── Step 2: Items ── -->
            <div id="m-step-2" class="hidden p-4">
                <div id="m-item-cards" class="space-y-3"></div>

                <!-- Grand total -->
                <div class="bg-orange-500 rounded-xl px-4 py-3 flex items-center justify-between mt-2">
                    <span class="text-white/80 text-xs font-bold uppercase tracking-wider">Total Amount</span>
                    <span class="text-white font-bold font-mono text-lg" id="m-grand-total">₱ 0.00</span>
                </div>

                <!-- Add item button -->
                <button onclick="mAddCard()"
                    class="w-full mt-3 flex items-center justify-center gap-2 border-2 border-dashed border-orange-300 text-orange-500 font-semibold text-sm rounded-xl py-3 hover:bg-orange-50 transition-all active:scale-98">
                    <i class="fa-solid fa-plus text-xs"></i> Add Item
                </button>
            </div>

            <!-- ── Step 3: Attachments ── -->
            <div id="m-step-3" class="hidden p-4 space-y-3">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">
                        <i class="fa-solid fa-paperclip mr-1"></i> Attach Proof / Images
                    </p>
                    <!-- Follow up toggle (mobile) -->
                    <label
                        class="flex items-center gap-3 cursor-pointer select-none mb-3 p-3 bg-orange-50 border border-orange-200 rounded-xl">
                        <div class="relative shrink-0">
                            <input type="checkbox" id="m-follow-up-toggle" class="sr-only peer">
                            <div
                                class="w-10 h-6 bg-gray-200 rounded-full peer peer-checked:bg-orange-500 transition-colors">
                            </div>
                            <div
                                class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-all peer-checked:translate-x-4">
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Follow up attachment</p>
                            <p class="text-[10px] text-gray-400">I'll send the proof later</p>
                        </div>
                    </label>
                    <div id="m-drop-zone"
                        class="flex flex-col items-center justify-center w-full py-10 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-orange-400 hover:bg-orange-50 transition-all">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-orange-300 mb-2"></i>
                        <span class="text-sm text-gray-400 font-medium">Tap to upload images</span>
                        <span class="text-[10px] text-gray-300 mt-1">JPG, PNG, WEBP — multiple allowed</span>
                    </div>
                    <input type="file" id="m-attachment-input" accept="image/jpeg,image/png,image/webp,application/pdf"
                        multiple class="hidden">
                    <div id="m-attachment-preview" class="mt-3 flex flex-wrap gap-2"></div>
                </div>
                <p class="text-[10px] text-gray-400 text-center">Images will be converted to WebP automatically</p>
            </div>

            <!-- ── Step 4: Submit ── -->
            <div id="m-step-4" class="hidden p-4 space-y-3">
                <!-- Category selector (mobile) -->
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Request Category</p>
                    <div class="grid grid-cols-3 gap-2">
                        <div onclick="mSelectCategory('project', this)"
                            class="m-cat-card flex flex-col items-center gap-1 px-2 py-3 border-2 border-transparent rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all text-center">
                            <i class="fa-solid fa-helmet-safety text-gray-300 text-xl m-cat-icon"></i>
                            <span class="text-xs font-semibold text-gray-600">Project</span>
                            <span class="text-[9px] text-gray-400">Tied to a project</span>
                        </div>
                        <div onclick="mSelectCategory('client', this)"
                            class="m-cat-card flex flex-col items-center gap-1 px-2 py-3 border-2 border-transparent rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all text-center">
                            <i class="fa-solid fa-user-tie text-gray-300 text-xl m-cat-icon"></i>
                            <span class="text-xs font-semibold text-gray-600">Client</span>
                            <span class="text-[9px] text-gray-400">Tied to a client</span>
                        </div>
                        <div onclick="mSelectCategory('nhcc', this)"
                            class="m-cat-card flex flex-col items-center gap-1 px-2 py-3 border-2 border-transparent rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all text-center">
                            <i class="fa-solid fa-building text-gray-300 text-xl m-cat-icon"></i>
                            <span class="text-xs font-semibold text-gray-600">NHCC</span>
                            <span class="text-[9px] text-gray-400">Internal use</span>
                        </div>
                    </div>
                    <div id="m-category-ref-section" class="hidden mt-3">
                        <label id="m-category-ref-label"
                            class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Project
                            name</label>
                        <input type="text" id="m-category-ref-input" placeholder="Enter project name..."
                            class="w-full border-b-2 border-gray-300 outline-none text-sm py-1.5 bg-transparent focus:border-orange-500 transition-colors">
                    </div>
                </div>
                <!-- Summary card -->
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 space-y-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Summary</p>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Requestor</span>
                        <span class="font-semibold text-gray-800" id="m-summary-name">—</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Purpose</span>
                        <span class="font-semibold text-gray-800 text-right max-w-[60%]" id="m-summary-purpose">—</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Items</span>
                        <span class="font-semibold text-gray-800" id="m-summary-items">0 items</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total</span>
                        <span class="font-bold text-orange-600 font-mono" id="m-summary-total">₱ 0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Attachments</span>
                        <span class="font-semibold text-gray-800" id="m-summary-attachments">0 files</span>
                    </div>
                </div>

                <!-- Choose head -->
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Send To (Head)</p>
                    <div id="m-heads-list" class="space-y-2">
                        <div class="text-center text-gray-400 text-sm py-4">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading...
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end scroll area -->

        <!-- Bottom nav -->
        <div class="bg-white border-t border-gray-100 px-4 py-4 flex-shrink-0 safe-bottom">
            <div class="flex items-center gap-3">
                <button id="m-btn-back" onclick="mPrevStep()"
                    class="hidden w-12 h-12 flex items-center justify-center rounded-xl border-2 border-gray-200 text-gray-500 active:bg-gray-100 transition-all flex-shrink-0">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </button>
                <button id="m-btn-next" onclick="mNextStep()"
                    class="flex-1 bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-bold text-base py-3.5 rounded-xl transition-all">
                    Next <i class="fa-solid fa-chevron-right text-sm ml-1"></i>
                </button>
            </div>
        </div>

    </div><!-- end mobile wizard -->


    <script>
        // ═══════════════════════════════════════════
        // SHARED STATE
        // ═══════════════════════════════════════════
        let attachmentFiles = [];
        let mCardCount = 0;
        let mSelectedHeadId = null;
        const controlNo = '<?= htmlspecialchars($control_no) ?>';
        const dateRequested = '<?= $today ?>';

        // ═══════════════════════════════════════════
        // MOBILE WIZARD
        // ═══════════════════════════════════════════
        let mCurrentStep = 1;
        const mTotalSteps = 4;

        // ── Category selector (shared) ───────────────────────
        let dSelectedCategory = null;
        let mSelectedCategory = null;

        function dSelectCategory(cat, el) {
            dSelectedCategory = cat;
            document.querySelectorAll('.d-cat-card').forEach(c => {
                c.classList.remove('border-orange-500', 'bg-orange-50');
                c.classList.add('border-transparent');
                c.querySelector('.d-cat-icon').classList.replace('text-orange-500', 'text-gray-300');
            });
            el.classList.add('border-orange-500', 'bg-orange-50');
            el.classList.remove('border-transparent');
            el.querySelector('.d-cat-icon').classList.replace('text-gray-300', 'text-orange-500');
            _updateCatRefSection('d-category-ref-section', 'd-category-ref-label', 'd-category-ref-input', cat);
            _updateDSubmitBtn();
        }

        function mSelectCategory(cat, el) {
            mSelectedCategory = cat;
            document.querySelectorAll('.m-cat-card').forEach(c => {
                c.classList.remove('border-orange-500', 'bg-orange-50');
                c.classList.add('border-transparent');
                c.querySelector('.m-cat-icon').classList.replace('text-orange-500', 'text-gray-300');
            });
            el.classList.add('border-orange-500', 'bg-orange-50');
            el.classList.remove('border-transparent');
            el.querySelector('.m-cat-icon').classList.replace('text-gray-300', 'text-orange-500');
            _updateCatRefSection('m-category-ref-section', 'm-category-ref-label', 'm-category-ref-input', cat);
        }

        function _updateCatRefSection(sectionId, labelId, inputId, cat) {
            const section = document.getElementById(sectionId);
            const label = document.getElementById(labelId);
            const input = document.getElementById(inputId);
            if (cat === 'nhcc') {
                section.classList.add('hidden');
                input.value = '';
            } else {
                section.classList.remove('hidden');
                if (cat === 'project') {
                    label.textContent = 'Project name';
                    input.placeholder = 'Enter project name...';
                } else {
                    label.textContent = 'Client name';
                    input.placeholder = 'Enter client name...';
                }
                setTimeout(() => input.focus(), 50);
            }
        }

        // Re-enable submit button only when both head + category are selected
        function _updateDSubmitBtn() {
            const btn = document.getElementById('d-confirm-submit-btn');
            btn.disabled = !(dSelectedHeadId && dSelectedCategory);
        }

        const mStepTitles = [
            '', // index 0 unused
            'Request Info',
            'Add Items',
            'Attachments',
            'Review & Submit'
        ];
        const mStepSubs = [
            '',
            'Fill in your name and purpose',
            'Add the items you need',
            'Attach proof or receipts',
            'Confirm and send your request'
        ];

        function mUpdateUI() {
            // Hide all panels
            for (let i = 1; i <= mTotalSteps; i++) {
                document.getElementById('m-step-' + i).classList.add('hidden');
            }
            document.getElementById('m-step-' + mCurrentStep).classList.remove('hidden');

            // Dots
            for (let i = 1; i <= mTotalSteps; i++) {
                const dot = document.getElementById('m-dot-' + i);
                if (i === mCurrentStep) {
                    dot.style.width = '24px';
                    dot.style.background = '#fff';
                } else if (i < mCurrentStep) {
                    dot.style.width = '8px';
                    dot.style.background = 'rgba(255,255,255,0.7)';
                } else {
                    dot.style.width = '8px';
                    dot.style.background = 'rgba(255,255,255,0.35)';
                }
            }

            document.getElementById('m-step-label').textContent = 'Step ' + mCurrentStep + ' of ' + mTotalSteps;
            document.getElementById('m-step-title').textContent = mStepTitles[mCurrentStep];
            document.getElementById('m-step-sub').textContent = mStepSubs[mCurrentStep];

            // Back button
            const backBtn = document.getElementById('m-btn-back');
            mCurrentStep > 1 ? backBtn.classList.remove('hidden') : backBtn.classList.add('hidden');

            // Next button label
            const nextBtn = document.getElementById('m-btn-next');
            if (mCurrentStep === mTotalSteps) {
                nextBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Submit Request';
            } else {
                nextBtn.innerHTML = 'Next <i class="fa-solid fa-chevron-right text-sm ml-1"></i>';
            }

            // Step 4 — fill summary + load heads
            if (mCurrentStep === 4) {
                mFillSummary();
                mLoadHeads();
            }
        }

        function mNextStep() {
            // Validate step 1
            if (mCurrentStep === 1) {
                const name = document.getElementById('m-requestor-name').value.trim();
                const purpose = document.getElementById('m-purpose').value.trim();
                if (!name || !purpose) {
                    mShowToast('Please fill in your name and purpose.', 'error');
                    return;
                }
            }

            // Validate step 3 (attachments required)
            if (mCurrentStep === 3 && attachmentFiles.length === 0 && !document.getElementById('m-follow-up-toggle').checked) {
                mShowToast('Please attach at least one file, or toggle "Follow up attachment".', 'error');
                return;
            }

            // Last step = submit
            if (mCurrentStep === mTotalSteps) {
                mSubmit();
                return;
            }

            mCurrentStep++;
            mUpdateUI();
        }

        function mPrevStep() {
            if (mCurrentStep > 1) { mCurrentStep--; mUpdateUI(); }
        }

        // ── Item Cards (Step 2) ──────────────────────
        function mAddCard() {
            mCardCount++;
            const container = document.getElementById('m-item-cards');
            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl border border-gray-100 p-4 shadow-sm';
            card.dataset.card = mCardCount;
            card.innerHTML = `
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Item #${mCardCount}</span>
                    <button onclick="mRemoveCard(this)" class="text-red-400 hover:text-red-600 text-xs transition-colors">
                        <i class="fa-solid fa-trash-can"></i> Remove
                    </button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Description</label>
                        <input type="text" placeholder="Item description..."
                            class="w-full border-b-2 border-gray-200 focus:border-orange-500 outline-none text-sm py-1 bg-transparent transition-colors">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Purpose</label>
                        <input type="text" placeholder="Purpose of this item..."
                            class="w-full border-b-2 border-gray-200 focus:border-orange-500 outline-none text-sm py-1 bg-transparent transition-colors">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Quantity</label>
                            <input type="number" min="0" placeholder="0" data-type="qty"
                                oninput="mCalcCard(this)"
                                class="w-full border-b-2 border-gray-200 focus:border-orange-500 outline-none text-sm py-1 bg-transparent transition-colors text-center">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Unit Price</label>
                            <input type="number" min="0" step="0.01" placeholder="0.00" data-type="price"
                                oninput="mCalcCard(this)"
                                class="w-full border-b-2 border-gray-200 focus:border-orange-500 outline-none text-sm py-1 bg-transparent transition-colors text-right">
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex-1">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Notes</label>
                            <input type="text" placeholder="Optional notes..."
                                class="w-full border-b-2 border-gray-200 focus:border-orange-500 outline-none text-sm py-1 bg-transparent transition-colors">
                        </div>
                        <div class="ml-4 text-right">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Amount</label>
                            <span class="m-card-amount font-bold font-mono text-gray-800 text-sm">₱ 0.00</span>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(card);
            card.querySelector('input').focus();
        }

        function mRemoveCard(btn) {
            const card = btn.closest('[data-card]');
            card.style.opacity = '0';
            card.style.transition = 'opacity 0.2s';
            setTimeout(() => { card.remove(); mCalcTotal(); }, 200);
        }

        function mCalcCard(input) {
            const card = input.closest('[data-card]');
            const qty = parseFloat(card.querySelector('[data-type="qty"]').value) || 0;
            const price = parseFloat(card.querySelector('[data-type="price"]').value) || 0;
            card.querySelector('.m-card-amount').textContent =
                '₱ ' + (qty * price).toLocaleString('en-PH', { minimumFractionDigits: 2 });
            mCalcTotal();
        }

        function mCalcTotal() {
            let total = 0;
            document.querySelectorAll('#m-item-cards .m-card-amount').forEach(el => {
                total += parseFloat(el.textContent.replace('₱', '').replace(/,/g, '')) || 0;
            });
            document.getElementById('m-grand-total').textContent =
                '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 });
        }

        // ── Attachments (shared) ─────────────────────
        function handleFiles(files) {
            files.forEach(file => {
                // PDF — i-base64 lang, walang canvas convert
                if (file.type === 'application/pdf') {
                    const reader = new FileReader();
                    reader.onload = ev => {
                        const id = Date.now() + '_' + Math.random().toString(36).slice(2);
                        const name = file.name;
                        attachmentFiles.push({ id, name, webpBase64: ev.target.result, isPdf: true });
                        renderPreviews();
                    };
                    reader.readAsDataURL(file);
                    return;
                }

                const reader = new FileReader();
                reader.onload = ev => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.width; canvas.height = img.height;
                        canvas.getContext('2d').drawImage(img, 0, 0);
                        const webpBase64 = canvas.toDataURL('image/webp', 0.85);
                        const id = Date.now() + '_' + Math.random().toString(36).slice(2);
                        const name = file.name.replace(/\.[^.]+$/, '') + '.webp';
                        attachmentFiles.push({ id, name, webpBase64 });
                        renderPreviews();
                    };
                    img.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        function renderPreviews() {
            ['d-attachment-preview', 'm-attachment-preview'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.innerHTML = attachmentFiles.map(f => `
    <div class="relative group/thumb w-20 h-20 rounded overflow-hidden border border-gray-200 shadow-sm">
        ${f.isPdf
                        ? `<div class="w-full h-full bg-red-50 flex flex-col items-center justify-center">
                <i class="fa-solid fa-file-pdf text-red-500 text-2xl"></i>
                <span class="text-[8px] text-red-400 mt-1 px-1 text-center truncate w-full">PDF</span>
               </div>`
                        : `<img src="${f.webpBase64}" class="w-full h-full object-cover">`
                    }
                        <div class="absolute inset-0 bg-black/0 group-hover/thumb:bg-black/40 transition-all flex items-center justify-center">
                            <button onclick="removeAttachment('${f.id}')"
                                class="opacity-0 group-hover/thumb:opacity-100 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs transition-all">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <p class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-[8px] px-1 truncate">${f.name}</p>
                    </div>`).join('');
            });
        }

        function removeAttachment(id) {
            attachmentFiles = attachmentFiles.filter(f => f.id !== id);
            renderPreviews();
        }

        // Mobile drop zone
        document.getElementById('m-drop-zone').addEventListener('click', () =>
            document.getElementById('m-attachment-input').click());
        document.getElementById('m-attachment-input').addEventListener('change', e => {
            handleFiles([...e.target.files]);
            e.target.value = '';
        });

        // ── Step 4: Summary ──────────────────────────
        function mFillSummary() {
            const name = document.getElementById('m-requestor-name').value.trim();
            const purpose = document.getElementById('m-purpose').value.trim();
            const cards = document.querySelectorAll('#m-item-cards [data-card]').length;
            const total = document.getElementById('m-grand-total').textContent;

            document.getElementById('m-summary-name').textContent = name || '—';
            document.getElementById('m-summary-purpose').textContent = purpose || '—';
            document.getElementById('m-summary-items').textContent = cards + ' item' + (cards !== 1 ? 's' : '');
            document.getElementById('m-summary-total').textContent = total;
            document.getElementById('m-summary-attachments').textContent = attachmentFiles.length + ' file' + (attachmentFiles.length !== 1 ? 's' : '');
        }

        // ── Step 4: Heads list ───────────────────────
        function mLoadHeads() {
            fetch('<?= BASE_URL ?>/fetchheads')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('m-heads-list');
                    if (!data.length) {
                        list.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">No heads available.</p>';
                        return;
                    }
                    list.innerHTML = data.map(h => `
                        <div onclick="mSelectHead(${h.id}, '${h.name}', '${h.role}')"
                            id="m-head-${h.id}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-transparent hover:border-orange-300 hover:bg-orange-50 cursor-pointer transition-all">
                            <div class="w-9 h-9 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user text-orange-500 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">${h.name}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">${h.role}</p>
                            </div>
                        </div>`).join('');
                });
        }

        function mSelectHead(id, name, role) {
            mSelectedHeadId = id;
            document.querySelectorAll('[id^="m-head-"]').forEach(el => {
                el.classList.remove('border-orange-500', 'bg-orange-50');
                el.classList.add('border-transparent');
            });
            const el = document.getElementById('m-head-' + id);
            el.classList.add('border-orange-500', 'bg-orange-50');
            el.classList.remove('border-transparent');
        }

        // ── Submit ───────────────────────────────────
        function mSubmit() {
            if (!mSelectedHeadId) {
                mShowToast('Please select a head to send to.', 'error');
                return;
            }

            // ── Idagdag ito ──
            if (!mSelectedCategory) {
                mShowToast('Please select a request category.', 'error');
                return;
            }
            if (mSelectedCategory !== 'nhcc' && !document.getElementById('m-category-ref-input').value.trim()) {
                mShowToast('Please enter the project or client name.', 'error');
                return;
            }

            const name = document.getElementById('m-requestor-name').value.trim();
            const purpose = document.getElementById('m-purpose').value.trim();

            const items = [];
            document.querySelectorAll('#m-item-cards [data-card]').forEach(card => {
                const inputs = card.querySelectorAll('input');
                const amount = card.querySelector('.m-card-amount').textContent.replace('₱', '').replace(/,/g, '').trim();
                items.push({
                    description: inputs[0]?.value || '',
                    purpose: inputs[1]?.value || '',
                    quantity: inputs[2]?.value || 0,
                    unit_price: inputs[3]?.value || 0,
                    amount: amount || '0.00',
                    notes: inputs[4]?.value || ''
                });
            });

            const nextBtn = document.getElementById('m-btn-next');
            nextBtn.disabled = true;
            nextBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Submitting...';

            fetch('<?= BASE_URL ?>/submitrequest', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    control_no: controlNo,
                    requestor_name: name,
                    purpose: purpose,
                    date_requested: dateRequested,
                    sent_to: mSelectedHeadId,
                    items: items,
                    attachments: attachmentFiles.map(f => ({ name: f.name, data: f.webpBase64 })),
                    attachment_status: document.getElementById('m-follow-up-toggle').checked ? 'follow_up' : 'attached',
                    request_category: mSelectedCategory,
                    request_reference: mSelectedCategory !== 'nhcc'
                        ? document.getElementById('m-category-ref-input').value.trim()
                        : null
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        mShowToast('Request submitted successfully!', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        mShowToast('Failed: ' + (data.error || 'Unknown error'), 'error');
                        nextBtn.disabled = false;
                        nextBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Submit Request';
                    }
                })
                .catch(() => {
                    mShowToast('Network error. Please try again.', 'error');
                    nextBtn.disabled = false;
                    nextBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> Submit Request';
                });
        }

        function mShowToast(msg, type = 'success') {
            const existing = document.getElementById('m-toast');
            if (existing) existing.remove();
            const color = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fa-check' : 'fa-triangle-exclamation';
            const t = document.createElement('div');
            t.id = 'm-toast';
            t.className = `fixed bottom-24 left-4 right-4 z-[999] flex items-center gap-3 ${color} text-white text-sm font-semibold px-4 py-3 rounded-xl shadow-lg transition-all duration-300 opacity-0`;
            t.innerHTML = `<i class="fa-solid ${icon}"></i> ${msg}`;
            document.body.appendChild(t);
            setTimeout(() => t.classList.replace('opacity-0', 'opacity-100'), 10);
            setTimeout(() => { t.classList.replace('opacity-100', 'opacity-0'); setTimeout(() => t.remove(), 300); }, 3000);
        }

        // Init mobile: start with 3 cards
        for (let i = 0; i < 3; i++) mAddCard();
        mUpdateUI();


        // ═══════════════════════════════════════════
        // DESKTOP LOGIC
        // ═══════════════════════════════════════════
        let dRowCount = 0;
        let dSelectedHeadId = null;

        // Desktop drop zone
        document.getElementById('d-drop-zone').addEventListener('click', () =>
            document.getElementById('d-attachment-input').click());
        document.getElementById('d-attachment-input').addEventListener('change', e => {
            handleFiles([...e.target.files]);
            e.target.value = '';
        });
        const ddz = document.getElementById('d-drop-zone');
        ddz.addEventListener('dragover', e => { e.preventDefault(); ddz.classList.add('border-orange-500', 'bg-orange-50'); });
        ddz.addEventListener('dragleave', () => ddz.classList.remove('border-orange-500', 'bg-orange-50'));
        ddz.addEventListener('drop', e => {
            e.preventDefault();
            ddz.classList.remove('border-orange-500', 'bg-orange-50');
            // ✅ Allow PDF too
            handleFiles([...e.dataTransfer.files].filter(f =>
                f.type.startsWith('image/') || f.type === 'application/pdf'
            ));
        });

        function dAddRow() {
            dRowCount++;
            const tbody = document.getElementById('d-item-rows');
            const tr = document.createElement('tr');
            tr.className = 'border-t border-gray-200 hover:bg-orange-50 transition-colors group';
            tr.dataset.row = dRowCount;
            tr.innerHTML = `
                <td class="px-3 py-2 text-center text-xs text-gray-400 font-mono border-r border-gray-200">${dRowCount}</td>
                <td class="px-2 py-1 border-r border-gray-200">
                    <input type="text" placeholder="Description..." class="w-full outline-none text-sm py-1 px-1 bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
                </td>
                <td class="px-2 py-1 border-r border-gray-200">
                    <input type="text" placeholder="Purpose..." class="w-full outline-none text-sm py-1 px-1 bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
                </td>
                <td class="px-2 py-1 border-r border-gray-200">
                    <input type="number" min="0" placeholder="0" oninput="dCalcRow(this)" data-type="qty"
                        class="w-full outline-none text-sm py-1 px-1 text-center bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
                </td>
                <td class="px-2 py-1 border-r border-gray-200">
                    <input type="number" min="0" step="0.01" placeholder="0.00" oninput="dCalcRow(this)" data-type="price"
                        class="w-full outline-none text-sm py-1 px-1 text-right bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
                </td>
                <td class="px-3 py-2 border-r border-gray-200 text-right font-mono text-sm text-gray-700 d-row-amount">₱ 0.00</td>
                <td class="px-2 py-1">
                    <div class="flex items-center gap-1">
                        <input type="text" placeholder="Notes..." class="flex-1 outline-none text-sm py-1 px-1 bg-transparent focus:bg-white rounded focus:ring-1 focus:ring-orange-300 transition-all">
                        <button onclick="dRemoveRow(this)" class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition-all ml-1 shrink-0">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </td>`;
            tbody.appendChild(tr);
            tr.querySelector('input').focus();
        }

        function dCalcRow(input) {
            const tr = input.closest('tr');
            const qty = parseFloat(tr.querySelector('[data-type="qty"]').value) || 0;
            const price = parseFloat(tr.querySelector('[data-type="price"]').value) || 0;
            tr.querySelector('.d-row-amount').textContent =
                '₱ ' + (qty * price).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            dCalcTotal();
        }

        function dCalcTotal() {
            let total = 0;
            document.querySelectorAll('.d-row-amount').forEach(cell => {
                total += parseFloat(cell.textContent.replace('₱', '').replace(/,/g, '')) || 0;
            });
            document.getElementById('d-grand-total').textContent =
                '₱ ' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function dRemoveRow(btn) {
            const tr = btn.closest('tr');
            tr.style.opacity = '0'; tr.style.transition = 'opacity 0.2s';
            setTimeout(() => {
                tr.remove();
                document.querySelectorAll('#d-item-rows tr').forEach((row, i) => { row.cells[0].textContent = i + 1; });
                dRowCount = document.querySelectorAll('#d-item-rows tr').length;
                dCalcTotal();
            }, 200);
        }

        function dOpenSubmitModal() {
            document.getElementById('d-submit-modal').classList.remove('hidden');
            dSelectedHeadId = null;
            dSelectedCategory = null;
            document.querySelectorAll('.d-cat-card').forEach(c => {
                c.classList.remove('border-orange-500', 'bg-orange-50');
                c.classList.add('border-transparent');
            });
            document.getElementById('d-category-ref-section').classList.add('hidden');
            document.getElementById('d-confirm-submit-btn').disabled = true;
            document.getElementById('d-selected-head-display').classList.add('hidden');
            dLoadHeads();
        }

        function dCloseSubmitModal() {
            document.getElementById('d-submit-modal').classList.add('hidden');
        }

        function dLoadHeads() {
            fetch('<?= BASE_URL ?>/fetchheads')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('d-heads-list');
                    if (!data.length) { list.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">No heads available.</p>'; return; }
                    list.innerHTML = data.map(h => `
                        <div onclick="dSelectHead(${h.id}, '${h.name}', '${h.role}')"
                            id="d-head-option-${h.id}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg border-2 border-transparent hover:border-orange-300 hover:bg-orange-50 cursor-pointer transition-all">
                            <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user text-orange-500 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">${h.name}</p>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">${h.role}</p>
                            </div>
                        </div>`).join('');
                });
        }

        function dSelectHead(id, name, role) {
            dSelectedHeadId = id;
            document.querySelectorAll('[id^="d-head-option-"]').forEach(el => {
                el.classList.remove('border-orange-500', 'bg-orange-50');
                el.classList.add('border-transparent');
            });
            document.getElementById('d-head-option-' + id).classList.add('border-orange-500', 'bg-orange-50');
            document.getElementById('d-selected-head-name').textContent = name;
            document.getElementById('d-selected-head-role').textContent = role;
            document.getElementById('d-selected-head-display').classList.remove('hidden');
            _updateDSubmitBtn();
        }

        function dConfirmSubmit() {
            if (!dSelectedHeadId) return;

            // ── Attachment check ──
            const isFollowUp = document.getElementById('d-follow-up-toggle').checked;
            if (!isFollowUp && attachmentFiles.length === 0) {
                const n = document.createElement('div');
                n.className = 'fixed bottom-5 right-5 z-50 bg-red-500 text-white text-sm font-semibold px-5 py-3 rounded-lg shadow-lg flex items-center gap-2';
                n.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Please attach at least one proof, or toggle "Follow up attachment".';
                document.body.appendChild(n);
                setTimeout(() => n.remove(), 3000);
                return;
            }

            // ── Category check ──
            if (!dSelectedCategory) {
                const n = document.createElement('div');
                n.className = 'fixed bottom-5 right-5 z-50 bg-red-500 text-white text-sm font-semibold px-5 py-3 rounded-lg shadow-lg flex items-center gap-2';
                n.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Please select a request category.';
                document.body.appendChild(n);
                setTimeout(() => n.remove(), 3000);
                return;
            }

            // ── Reference name check (only for project/client) ──
            if (dSelectedCategory !== 'nhcc' && !document.getElementById('d-category-ref-input').value.trim()) {
                const n = document.createElement('div');
                n.className = 'fixed bottom-5 right-5 z-50 bg-red-500 text-white text-sm font-semibold px-5 py-3 rounded-lg shadow-lg flex items-center gap-2';
                n.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Please enter the project or client name.';
                document.body.appendChild(n);
                setTimeout(() => n.remove(), 3000);
                return;
            }

            // ── Basic field check ──
            const requestorName = document.getElementById('d-requestor-name').value;
            const purpose = document.getElementById('d-purpose').value;
            if (!requestorName || !purpose) { alert('Please fill in Requestor Name and Purpose.'); return; }

            // ── Collect items ──
            const items = [];
            document.querySelectorAll('#d-item-rows tr').forEach(tr => {
                const inputs = tr.querySelectorAll('input');
                const amount = tr.querySelector('.d-row-amount')?.textContent.replace('₱', '').replace(/,/g, '').trim();
                items.push({
                    description: inputs[0]?.value || '',
                    purpose: inputs[1]?.value || '',
                    quantity: inputs[2]?.value || 0,
                    unit_price: inputs[3]?.value || 0,
                    amount: amount || '0.00',
                    notes: inputs[4]?.value || ''
                });
            });

            const btn = document.getElementById('d-confirm-submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Submitting...';

            fetch('<?= BASE_URL ?>/submitrequest', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    control_no: controlNo,
                    requestor_name: requestorName,
                    purpose: purpose,
                    date_requested: dateRequested,
                    sent_to: dSelectedHeadId,
                    items: items,
                    attachments: attachmentFiles.map(f => ({ name: f.name, data: f.webpBase64 })),
                    attachment_status: isFollowUp ? 'follow_up' : 'attached',
                    request_category: dSelectedCategory,
                    request_reference: dSelectedCategory !== 'nhcc'
                        ? document.getElementById('d-category-ref-input').value.trim()
                        : null
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        dCloseSubmitModal();
                        const n = document.createElement('div');
                        n.className = 'fixed top-5 right-5 z-50 bg-green-500 text-white text-sm font-semibold px-5 py-3 rounded-lg shadow-lg flex items-center gap-2';
                        n.innerHTML = '<i class="fa-solid fa-check"></i> Request submitted successfully!';
                        document.body.appendChild(n);
                        setTimeout(() => window.location.reload(), 1500); // ← dagdag ito
                    } else {
                        alert('Failed: ' + data.error);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i> Confirm & Submit';
                    }
                })
                .catch(() => {
                    alert('Network error. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i> Confirm & Submit';
                });
        }

        // Init desktop rows
        for (let i = 0; i < 5; i++) dAddRow();
    </script>
</body>

</html>