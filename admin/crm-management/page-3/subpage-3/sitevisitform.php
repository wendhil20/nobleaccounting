<?php
// crmsitevisit.php

include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_DESIGNER];

include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roleguard.php';


$svError = '';
$svSuccess = false;
$currentDesignerId = intval($_SESSION['account_id'] ?? 0);

$inquiryId = intval($_GET['id'] ?? $_POST['inquiry_id'] ?? 0);

if ($inquiryId <= 0) {
    $svError = 'Missing or invalid inquiry reference.';
} else {
    // Fetch the inquiry, making sure it belongs to the logged-in designer
    $stmt = $conn->prepare("
        SELECT id, control_no, client_name, address, contact_number, status
        FROM noblecrminquiry
        WHERE id = ? AND designer_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $inquiryId, $currentDesignerId);
    $stmt->execute();
    $inquiry = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$inquiry) {
        $svError = 'Inquiry not found, or not assigned to you.';
    }
}

// When this form is opened, mark the related notification as read
if (empty($svError) && $inquiryId > 0) {
    $notifStmt = $conn->prepare("
        UPDATE noblenotification SET is_read = 1
        WHERE request_id = ? AND user_id = ?
    ");
    $notifStmt->bind_param("ii", $inquiryId, $currentDesignerId);
    $notifStmt->execute();
    $notifStmt->close();
}

// --- Handle submit ---
if (empty($svError) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_sitevisit'])) {

    $visitAddress = trim($_POST['visit_address'] ?? '');
    $visitDatetime = trim($_POST['visit_datetime'] ?? '');
    $visited = trim($_POST['visited'] ?? '');

    if (empty($visitAddress) || empty($visitDatetime) || !in_array($visited, ['yes', 'no'], true)) {
        $svError = 'Please fill out all required fields.';
    } else {

        // --- Upload pictures (optional, multiple allowed) ---
        $uploadedPaths = [];
        $uploadDir = ROOT_PATH . '/uploads/crm-sitevisit/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        // Convert each uploaded picture to WEBP using GD
        function svConvertToWebp(string $tmpPath, string $ext): ?string
        {
            $ext = strtolower($ext);

            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                    $src = @imagecreatefromjpeg($tmpPath);
                    break;
                case 'png':
                    $src = @imagecreatefrompng($tmpPath);
                    if ($src) {
                        // Preserve transparency for PNG
                        imagepalettetotruecolor($src);
                        imagealphablending($src, true);
                        imagesavealpha($src, true);
                    }
                    break;
                case 'webp':
                    $src = @imagecreatefromwebp($tmpPath);
                    break;
                default:
                    $src = false;
            }

            if (!$src) {
                return null;
            }

            $webpTmp = tempnam(sys_get_temp_dir(), 'svwebp_') . '.webp';
            $ok = imagewebp($src, $webpTmp, 82); // 82 = quality
            imagedestroy($src);

            return $ok ? $webpTmp : null;
        }

        if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
            foreach ($_FILES['photos']['name'] as $i => $fileName) {
                if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK || $fileName === '') {
                    continue;
                }
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    continue; // skip non-image files
                }

                $tmpName = $_FILES['photos']['tmp_name'][$i];
                $safeName = 'sv_' . $inquiryId . '_' . time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.webp';
                $destPath = $uploadDir . $safeName;

                $webpTmp = svConvertToWebp($tmpName, $ext);

                if ($webpTmp && rename($webpTmp, $destPath)) {
                    $uploadedPaths[] = 'uploads/crm-sitevisit/' . $safeName;
                } elseif ($webpTmp) {
                    @unlink($webpTmp);
                }
                // If conversion fails, just skip that picture
            }
        }

        $photosCsv = implode(',', $uploadedPaths);

        $stmt = $conn->prepare("
            INSERT INTO noblecrm_sitevisit
                (inquiry_id, designer_id, address, visit_datetime, visited, photos, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param(
            "iissss",
            $inquiryId,
            $currentDesignerId,
            $visitAddress,
            $visitDatetime,
            $visited,
            $photosCsv
        );

        if ($stmt->execute()) {
            $stmt->close();

            // Mark the original inquiry as In Progress now that it has proceeded
            $updateStmt = $conn->prepare("
                UPDATE noblecrminquiry SET status = 'In Progress'
                WHERE id = ? AND designer_id = ?
            ");
            $updateStmt->bind_param("ii", $inquiryId, $currentDesignerId);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['sv_flash_success'] = 'Site visit details saved.';
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $svError = 'Something went wrong while saving. Please try again.';
            $stmt->close();
        }
    }
}

if (!empty($_SESSION['sv_flash_success'])) {
    $svSuccess = $_SESSION['sv_flash_success'];
    unset($_SESSION['sv_flash_success']);
}

// Fetch the history of submitted site visits for this inquiry
$svHistory = [];
if (empty($svError) && $inquiryId > 0) {
    $histStmt = $conn->prepare("
        SELECT id, address, visit_datetime, visited, photos, created_at
        FROM noblecrm_sitevisit
        WHERE inquiry_id = ? AND designer_id = ?
        ORDER BY created_at DESC
    ");
    $histStmt->bind_param("ii", $inquiryId, $currentDesignerId);
    $histStmt->execute();
    $svHistory = $histStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $histStmt->close();
}

$crmDesignerListUrl = BASE_URL . '/crmdesigner';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Visit Record</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body class="bg-gray-50 font-['Barlow_Condensed']">
    <main class="ml-56 min-h-screen p-6">

        <div class="max-w-4xl mx-auto">

            <!-- Document Sheet -->
            <div class="bg-white border border-gray-300 shadow-sm">

                <!-- Letterhead -->
                <div class="border-b-2 border-gray-800 px-7 pt-6 pb-5 flex items-start justify-between font-semibold">
                    <div>
                        <p class="text-[10px] tracking-[0.2em] uppercase text-gray-500 mb-1">Client Relationship Management</p>
                        <h1 class="text-xl font-bold text-gray-900 tracking-wide">Site Visit Record</h1>
                    </div>
                    <a href="<?= htmlspecialchars($crmDesignerListUrl) ?>"
                        class="text-xs font-medium text-gray-600 px-3 py-1.5 hover:bg-gray-100 transition-colors">
                         Back to Assigned List
                    </a>
                </div>

                <div class="px-7 py-6 text-sm">

                    <?php if (!empty($svError)): ?>
                        <div class="border border-gray-400 bg-gray-50 text-gray-800 text-sm px-4 py-2.5 mb-5">
                            <strong class="uppercase text-[11px] tracking-wide">Notice:</strong>
                            <?= htmlspecialchars($svError) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($svError) && $inquiry): ?>

                        <?php $svAlreadyDone = !empty($svHistory); ?>

                        <!-- Inquiry Summary -->
                        <table class="w-full border border-gray-300 text-sm mb-6">
                            <tbody>
                                <tr class="border-b border-gray-200">
                                    <td class="w-32 bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-gray-200">
                                        Control No.
                                    </td>
                                    <td class="px-4 py-2.5 font-semibold text-gray-900">
                                        <?= htmlspecialchars($inquiry['control_no']) ?>
                                    </td>
                                    <td class="w-28 bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-l border-gray-200">
                                        Status
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-900">
                                        <?= htmlspecialchars($inquiry['status']) ?>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200">
                                    <td class="bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-gray-200">
                                        Client Name
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-900">
                                        <?= htmlspecialchars($inquiry['client_name']) ?>
                                    </td>
                                    <td class="bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-l border-gray-200">
                                        Contact No.
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-900">
                                        <?= htmlspecialchars($inquiry['contact_number']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-gray-200 align-top">
                                        Registered Address
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-900" colspan="3">
                                        <?= htmlspecialchars($inquiry['address']) ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <?php if ($svAlreadyDone): ?>

                            <?php $latestVisit = $svHistory[0]; ?>
                            <?php $latestPhotos = array_filter(explode(',', $latestVisit['photos'] ?? '')); ?>
                            <?php $latestVisited = $latestVisit['visited'] === 'yes'; ?>

                            <!-- Status line -->
                            <div class="flex items-center gap-2 border-l-4 border-gray-800 bg-gray-50 px-4 py-2.5 mb-6">
                                <p class="text-sm text-gray-800">
                                    <strong class="uppercase tracking-wide text-[11px]">Recorded:</strong>
                                    A site visit has been logged for this inquiry.
                                </p>
                            </div>

                            <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4 font-semibold">
                                Latest Visit Details
                            </h2>

                            <table class="w-full border border-gray-300 text-sm mb-6">
                                <tbody>
                                    <tr class="border-b border-gray-200">
                                        <td class="w-40 bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-gray-200">
                                            Address Visited
                                        </td>
                                        <td class="px-4 py-2.5 text-gray-900">
                                            <?= htmlspecialchars($latestVisit['address']) ?>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-gray-200">
                                            Date &amp; Time of Visit
                                        </td>
                                        <td class="px-4 py-2.5 text-gray-900">
                                            <?= htmlspecialchars(date('F d, Y — g:i A', strtotime($latestVisit['visit_datetime']))) ?>
                                        </td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-gray-200">
                                            Visit Confirmed
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="inline-block text-[11px] font-semibold uppercase tracking-wide px-2.5 py-1 border <?= $latestVisited ? 'border-green-800 text-green-800' : 'border-gray-400 text-gray-600' ?>">
                                                <?= $latestVisited ? 'Yes — Visited / Proceed to 2D and Quotation' : 'No — Not Visited' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bg-gray-50 font-semibold text-[10px] uppercase tracking-wider text-gray-500 px-4 py-2.5 border-r border-gray-200">
                                            Logged On
                                        </td>
                                        <td class="px-4 py-2.5 text-gray-600 text-xs">
                                            <?= htmlspecialchars(date('F d, Y / g:i A', strtotime($latestVisit['created_at']))) ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Photos -->
                            <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4 font-semibold">
                                Attached Photographs
                                <?php if (!empty($latestPhotos)): ?>
                                    <span class="text-gray-400 normal-case">(<?= count($latestPhotos) ?> file<?= count($latestPhotos) === 1 ? '' : 's' ?>)</span>
                                <?php endif; ?>
                            </h2>

                            <?php if (empty($latestPhotos)): ?>
                                <p class="text-sm text-gray-500 italic mb-6">No photographs were submitted for this visit.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2.5 mb-6">
                                    <?php foreach ($latestPhotos as $photoPath): ?>
                                        <a href="<?= htmlspecialchars(BASE_URL . '/' . $photoPath) ?>" target="_blank"
                                            class="block aspect-square w-24 border border-gray-300 overflow-hidden hover:border-gray-800 transition-colors">
                                            <img src="<?= htmlspecialchars(BASE_URL . '/' . $photoPath) ?>"
                                                class="w-full h-full object-cover">
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Prior entries -->
                            <?php if (count($svHistory) > 1): ?>
                                <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4">
                                    Prior Entries
                                </h2>
                                <table class="w-full border border-gray-300 text-sm mb-4">
                                    <thead>
                                        <tr class="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500">
                                            <th class="text-left font-medium px-4 py-2 border-r border-b border-gray-200">Address</th>
                                            <th class="text-left font-medium px-4 py-2 border-r border-b border-gray-200">Date</th>
                                            <th class="text-left font-medium px-4 py-2 border-r border-b border-gray-200">Photos</th>
                                            <th class="text-left font-medium px-4 py-2 border-b border-gray-200">Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($svHistory, 1) as $entry): ?>
                                            <?php
                                            $entryVisited = $entry['visited'] === 'yes';
                                            $entryPhotos = array_filter(explode(',', $entry['photos'] ?? ''));
                                            ?>
                                            <tr class="border-b border-gray-200 last:border-b-0">
                                                <td class="px-4 py-2 text-gray-800"><?= htmlspecialchars($entry['address']) ?></td>
                                                <td class="px-4 py-2 text-gray-600 whitespace-nowrap">
                                                    <?= htmlspecialchars(date('M d, Y g:i A', strtotime($entry['visit_datetime']))) ?>
                                                </td>
                                                <td class="px-4 py-2 text-gray-600">
                                                    <?= count($entryPhotos) ?>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <span class="text-[11px] font-semibold uppercase tracking-wide <?= $entryVisited ? 'text-gray-900' : 'text-gray-500' ?>">
                                                        <?= $entryVisited ? 'Visited' : 'Not Visited' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>

                        <?php else: ?>

                            <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4">
                                No Prior Record
                            </h2>
                            <p class="text-sm text-gray-500 italic mb-6">No site visits have been recorded yet for this inquiry.</p>

                            <h2 class="text-[11px] uppercase tracking-[0.2em] text-gray-500 border-b border-gray-300 pb-2 mb-4">
                                Submit New Record
                            </h2>

                            <!-- Site Visit Form -->
                            <form method="POST" action="" enctype="multipart/form-data" class="space-y-5">

                                <input type="hidden" name="inquiry_id" value="<?= (int) $inquiry['id'] ?>">

                                <!-- Address -->
                                <div>
                                    <label class="block text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-1.5">
                                        Address <span class="text-gray-400 normal-case">(required)</span>
                                    </label>
                                    <textarea name="visit_address" rows="2" required
                                        class="w-full px-3 py-2 text-sm border border-gray-400 focus:outline-none focus:border-gray-800 bg-white"
                                        placeholder="Site visit address"><?= htmlspecialchars($inquiry['address']) ?></textarea>
                                </div>

                                <!-- Date and Time -->
                                <div>
                                    <label class="block text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-1.5">
                                        Date &amp; Time of Visit <span class="text-gray-400 normal-case">(required)</span>
                                    </label>
                                    <input type="datetime-local" name="visit_datetime" required
                                        class="w-full px-3 py-2 text-sm border border-gray-400 focus:outline-none focus:border-gray-800 bg-white">
                                </div>

                                <!-- Visit Yes / No -->
                                <div>
                                    <label class="block text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-1.5">
                                        Did the site visit happen? <span class="text-gray-400 normal-case">(required)</span>
                                    </label>
                                    <div class="flex gap-2.5">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="visited" value="yes" class="peer sr-only" required>
                                            <div class="text-center px-4 py-2 text-sm font-medium border border-gray-400 text-gray-600 peer-checked:border-gray-900 peer-checked:bg-gray-900 peer-checked:text-white transition-colors">
                                                Yes
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="visited" value="no" class="peer sr-only" required>
                                            <div class="text-center px-4 py-2 text-sm font-medium border border-gray-400 text-gray-600 peer-checked:border-gray-900 peer-checked:bg-gray-900 peer-checked:text-white transition-colors">
                                                No
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Upload Picture -->
                                <div>
                                    <label class="block text-[11px] uppercase tracking-wide font-semibold text-gray-600 mb-1.5">
                                        Attach Photograph(s) <span class="text-gray-400 normal-case">(optional)</span>
                                    </label>
                                    <label for="sv_photos"
                                        class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed border-gray-400 py-5 cursor-pointer hover:border-gray-800 hover:bg-gray-50 transition-colors">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span class="text-sm text-gray-600">Click to upload photographs</span>
                                        <span class="text-[11px] text-gray-400">JPG, PNG, or WEBP</span>
                                    </label>
                                    <input id="sv_photos" type="file" name="photos[]" accept="image/*" multiple class="hidden">
                                    <div id="sv_photo_preview" class="mt-2.5 grid grid-cols-4 sm:grid-cols-6 gap-2"></div>
                                </div>

                                <div class="pt-3 border-t border-gray-200 flex justify-end">
                                    <button type="submit" name="submit_sitevisit"
                                        class="px-5 py-2 text-sm font-semibold uppercase tracking-wide text-white bg-gray-900 hover:bg-gray-700 transition-colors">
                                        Save Record
                                    </button>
                                </div>
                            </form>

                        <?php endif; ?>

                    <?php endif; ?>

                </div>
                <!-- /body padding -->

                <!-- Footer -->
                <div class="border-t border-gray-300 px-7 py-3 text-[11px] text-gray-400 flex justify-between">
                    <span>Generated on <?= date('F d, Y g:i A') ?></span>
                    <span>Site Visit Record</span>
                </div>

            </div>
            <!-- /Document Sheet -->

        </div>

        <!-- Toast container: bottom-right -->
        <div id="crmToastContainer"
            class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-sm px-4 sm:px-0">
        </div>

        <script>
            function crmShowToast(message, type = 'success', duration = 4000) {
                const container = document.getElementById('crmToastContainer');
                const palette = type === 'success'
                    ? { wrap: 'bg-white border-gray-800 text-gray-800', icon: 'bg-gray-800 text-white', symbol: '✓' }
                    : { wrap: 'bg-white border-gray-400 text-gray-800', icon: 'bg-gray-400 text-white', symbol: '!' };

                const toast = document.createElement('div');
                toast.className = `pointer-events-auto flex items-start gap-2.5 border rounded-none shadow-lg px-4 py-3 text-sm
            ${palette.wrap}
            translate-x-6 opacity-0 scale-95 transition-all duration-300 ease-out`;
                toast.innerHTML = `
            <span class="shrink-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold ${palette.icon}">${palette.symbol}</span>
            <span class="flex-1 leading-relaxed">${message}</span>
            <button type="button" class="shrink-0 text-current opacity-50 hover:opacity-100 text-base leading-none" aria-label="Close">&times;</button>
        `;
                container.appendChild(toast);
                requestAnimationFrame(() => toast.classList.remove('translate-x-6', 'opacity-0', 'scale-95'));
                const remove = () => {
                    toast.classList.add('translate-x-6', 'opacity-0', 'scale-95');
                    setTimeout(() => toast.remove(), 300);
                };
                toast.querySelector('button').addEventListener('click', remove);
                if (duration > 0) setTimeout(remove, duration);
            }

            <?php if ($svSuccess): ?>
                crmShowToast(<?= json_encode($svSuccess) ?>, 'success');
            <?php endif; ?>

            // Photo preview thumbnails
            const svPhotosInput = document.getElementById('sv_photos');
            if (svPhotosInput) {
                svPhotosInput.addEventListener('change', function () {
                    const preview = document.getElementById('sv_photo_preview');
                    preview.innerHTML = '';
                    Array.from(this.files).forEach(file => {
                        if (!file.type.startsWith('image/')) return;
                        const url = URL.createObjectURL(file);
                        const img = document.createElement('img');
                        img.src = url;
                        img.className = 'w-full h-16 object-cover border border-gray-300';
                        preview.appendChild(img);
                    });
                });
            }
        </script>

    </main>
</body>

</html>