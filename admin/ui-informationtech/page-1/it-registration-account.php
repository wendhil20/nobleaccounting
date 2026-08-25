<?php
//it-registration-account.php
include ROOT_PATH . '/network/connect.php';
include ROOT_PATH . '/admin/authentication/index-authguard.php';
include ROOT_PATH . '/admin/authentication/index-roles.php';

$allowedRoles = [ROLE_IT];
include ROOT_PATH . '/admin/authentication/index-roleguard.php';

/* =========================================================
   AJAX HANDLER — Department & Branch CRUD (settings modal)
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['ajax_action'];
    $response = ['success' => false, 'message' => 'Invalid action.'];

    switch ($action) {

        case 'dept_add':
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $response['message'] = 'Department name is required.';
            } else {
                $stmt = $conn->prepare("INSERT INTO nobledepartment (name) VALUES (?)");
                $stmt->bind_param("s", $name);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'id' => $stmt->insert_id, 'name' => $name, 'message' => 'Department added.'];
                } else {
                    $response['message'] = 'Failed to add department. It might already exist.';
                }
                $stmt->close();
            }
            break;

        case 'dept_edit':
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if ($id === 0 || $name === '') {
                $response['message'] = 'Invalid department data.';
            } else {
                $stmt = $conn->prepare("UPDATE nobledepartment SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $name, $id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'id' => $id, 'name' => $name, 'message' => 'Department updated.'];
                } else {
                    $response['message'] = 'Failed to update department.';
                }
                $stmt->close();
            }
            break;

        case 'dept_delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id === 0) {
                $response['message'] = 'Invalid department.';
            } else {
                $check = $conn->prepare(
                    "SELECT COUNT(*) FROM noblerole WHERE role = (SELECT name FROM nobledepartment WHERE id = ?)"
                );
                $check->bind_param("i", $id);
                $check->execute();
                $check->bind_result($inUseCount);
                $check->fetch();
                $check->close();

                if ($inUseCount > 0) {
                    $response['message'] = 'Cannot delete: department is in use by ' . $inUseCount . ' account(s).';
                } else {
                    $stmt = $conn->prepare("DELETE FROM nobledepartment WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $response = ['success' => true, 'id' => $id, 'message' => 'Department deleted.'];
                    } else {
                        $response['message'] = 'Failed to delete department.';
                    }
                    $stmt->close();
                }
            }
            break;

        case 'branch_add':
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $response['message'] = 'Branch name is required.';
            } else {
                $stmt = $conn->prepare("INSERT INTO noblebranch (name, created_at) VALUES (?, NOW())");
                $stmt->bind_param("s", $name);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'id' => $stmt->insert_id, 'name' => $name, 'is_main' => 0, 'message' => 'Branch added.'];
                } else {
                    $response['message'] = 'Failed to add branch. It might already exist.';
                }
                $stmt->close();
            }
            break;

        case 'branch_edit':
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if ($id === 0 || $name === '') {
                $response['message'] = 'Invalid branch data.';
            } else {
                $stmt = $conn->prepare("UPDATE noblebranch SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $name, $id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'id' => $id, 'name' => $name, 'message' => 'Branch updated.'];
                } else {
                    $response['message'] = 'Failed to update branch.';
                }
                $stmt->close();
            }
            break;

        case 'branch_delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id === 0) {
                $response['message'] = 'Invalid branch.';
            } else {
                // Prevent deleting the Main Branch
                $mainCheck = $conn->prepare("SELECT is_main FROM noblebranch WHERE id = ?");
                $mainCheck->bind_param("i", $id);
                $mainCheck->execute();
                $mainCheck->bind_result($isMain);
                $mainCheck->fetch();
                $mainCheck->close();

                if ($isMain == 1) {
                    $response['message'] = 'Cannot delete: this is the Main Branch.';
                    echo json_encode($response);
                    exit;
                }

                $check = $conn->prepare("SELECT COUNT(*) FROM noblerole WHERE branch_id = ?");
                $check->bind_param("i", $id);
                $check->execute();
                $check->bind_result($inUseCount);
                $check->fetch();
                $check->close();

                if ($inUseCount > 0) {
                    $response['message'] = 'Cannot delete: branch is in use by ' . $inUseCount . ' account(s).';
                } else {
                    $stmt = $conn->prepare("DELETE FROM noblebranch WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        $response = ['success' => true, 'id' => $id, 'message' => 'Branch deleted.'];
                    } else {
                        $response['message'] = 'Failed to delete branch.';
                    }
                    $stmt->close();
                }
            }
            break;

        case 'branch_set_main':
            $id = intval($_POST['id'] ?? 0);
            if ($id === 0) {
                $response['message'] = 'Invalid branch.';
            } else {
                $conn->begin_transaction();
                try {
                    $conn->query("UPDATE noblebranch SET is_main = 0");
                    $stmt = $conn->prepare("UPDATE noblebranch SET is_main = 1 WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                    $conn->commit();
                    $response = ['success' => true, 'id' => $id, 'message' => 'Main branch updated.'];
                } catch (Exception $e) {
                    $conn->rollback();
                    $response['message'] = 'Failed to set main branch.';
                }
            }
            break;
    }

    echo json_encode($response);
    exit;
}

$success = '';
$error = '';

$departments = [];
$dept_result = $conn->query("SELECT id, name FROM nobledepartment ORDER BY name ASC");
if ($dept_result) {
    while ($row = $dept_result->fetch_assoc()) {
        $departments[] = $row;
    }
}

$branches = [];
$branch_result = $conn->query("SELECT id, name, is_main FROM noblebranch ORDER BY is_main DESC, name ASC");
if ($branch_result) {
    while ($row = $branch_result->fetch_assoc()) {
        $branches[] = $row;
    }
}

// Determine main branch id (for default selection in the dropdown)
$mainBranchId = null;
foreach ($branches as $b) {
    if (!empty($b['is_main'])) {
        $mainBranchId = $b['id'];
        break;
    }
}

if (!empty($_SESSION['reg_success'])) {
    $success = $_SESSION['reg_success'];
    unset($_SESSION['reg_success']); // clear agad
}

/* =========================================================
   MAIN REGISTRATION SUBMIT
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_action'])) {
    $name = trim($_POST['name'] ?? '');
    $email_prefix = trim($_POST['email_prefix'] ?? '');
    $email = $email_prefix . '@noble.com';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $dept_id = intval($_POST['department_id'] ?? 0);
    $branch_id = intval($_POST['branch_id'] ?? 0);

    if (empty($name) || empty($email_prefix) || empty($password) || empty($confirm) || $dept_id === 0 || $branch_id === 0) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^[a-zA-Z0-9._\-]+$/', $email_prefix)) {
        $error = 'Email prefix contains invalid characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check duplicate email
        $check = $conn->prepare("SELECT id FROM noblerole WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'That email is already taken.';
            $check->close();
        } else {
            $check->close();

            $dept_stmt = $conn->prepare("SELECT name FROM nobledepartment WHERE id = ?");
            $dept_stmt->bind_param("i", $dept_id);
            $dept_stmt->execute();
            $dept_stmt->bind_result($dept_name);
            $dept_stmt->fetch();
            $dept_stmt->close();

            $branch_stmt = $conn->prepare("SELECT name FROM noblebranch WHERE id = ?");
            $branch_stmt->bind_param("i", $branch_id);
            $branch_stmt->execute();
            $branch_stmt->bind_result($branch_name);
            $branch_stmt->fetch();
            $branch_stmt->close();

            if (empty($dept_name)) {
                $error = 'Selected department is invalid.';
            } elseif (empty($branch_name)) {
                $error = 'Selected branch is invalid.';
            } else {
                $role = $dept_name;
                $hashed = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO noblerole (name, email, role, password, branch) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $email, $role, $hashed, $branch_name);

                if ($stmt->execute()) {
                    // Store success message in session THEN redirect
                    $_SESSION['reg_success'] = 'Account for "' . htmlspecialchars($name) . '" registered successfully as ' . htmlspecialchars($role) . ' (' . htmlspecialchars($branch_name) . ').';
                    header('Location: ' . BASE_URL . '/superad');
                    exit;
                } else {
                    $error = 'Registration failed. Please try again.';
                }
                $stmt->close();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <?php include ROOT_PATH . '/link/top.php'; ?>
    <?php include ROOT_PATH . '/admin/navigation/sidebar.php'; ?>
</head>

<body>
    <main class="ml-56 min-h-screen p-8 bg-gray-50">
        <div class="max-w-lg mx-auto">
            <!-- Logo / Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-black shadow-lg mb-4">
                    <i class="fa-solid fa-circle-user text-4xl text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create an Account</h1>
                <p class="text-slate-500 text-sm mt-1">Fill in your details to register</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                <!-- Alerts -->
                <?php if ($success): ?>
                    <div
                        class="flex items-start gap-3 bg-emerald-50 border-b border-emerald-200 text-emerald-700 px-5 py-4 text-sm">
                        <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="flex items-start gap-3 bg-red-50 border-b border-red-200 text-red-600 px-5 py-4 text-sm">
                        <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" action="" class="px-6 py-7 space-y-5" id="registerForm">

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input type="text" id="name" name="name"
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Juan dela Cruz"
                                class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                   placeholder-slate-400 transition">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email_prefix" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-center border border-slate-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition">
                            <span class="flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input type="text" id="email_prefix" name="email_prefix"
                                value="<?= htmlspecialchars($_POST['email_prefix'] ?? '') ?>"
                                placeholder="e.g. juan.delacruz"
                                class="flex-1 pl-2 pr-1 py-2.5 text-sm text-slate-800 bg-white focus:outline-none placeholder-slate-400">
                            <span
                                class="pr-3 py-2.5 text-sm font-medium text-slate-500 bg-slate-50 border-l border-slate-300 px-3 select-none">
                                @noble.com
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Only the prefix is needed — <strong>@noble.com</strong>
                            is fixed.</p>
                    </div>

                    <!-- Department (Role) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="department_id" class="block text-sm font-medium text-slate-700">
                                Department <span class="text-red-500">*</span>
                            </label>
                            <button type="button" onclick="openManageModal('dept')"
                                class="flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-indigo-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </button>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </span>
                            <select id="department_id" name="department_id" class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                   bg-white appearance-none transition">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= (isset($_POST['department_id']) && $_POST['department_id'] == $dept['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Your department will be used as your role.</p>
                    </div>

                    <!-- Branch -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="branch_id" class="block text-sm font-medium text-slate-700">
                                Branch <span class="text-red-500">*</span>
                            </label>
                            <button type="button" onclick="openManageModal('branch')"
                                class="flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-indigo-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </button>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <select id="branch_id" name="branch_id" class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                   bg-white appearance-none transition">
                                <option value="">-- Select Branch --</option>
                                <?php foreach ($branches as $branch): ?>
                                    <?php
                                    $isSelected = isset($_POST['branch_id'])
                                        ? ($_POST['branch_id'] == $branch['id'])
                                        : (!empty($branch['is_main']));
                                    ?>
                                    <option value="<?= $branch['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($branch['name']) ?>    <?= !empty($branch['is_main']) ? ' (Main)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Branch assignment for this account.</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Min. 6 characters" class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                   placeholder-slate-400 transition">
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <input type="password" id="confirm_password" name="confirm_password"
                                placeholder="Re-enter your password" class="w-full pl-9 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                   placeholder-slate-400 transition">
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-slate-100 pt-1"></div>

                    <!-- Submit -->
                    <button type="submit" class="w-full bg-black hover:bg-red-500 active:bg-indigo-800 text-white
                           font-semibold text-sm py-3 rounded-lg transition-colors duration-150 shadow-sm">
                        Register Account
                    </button>

                </form>
            </div>

            <p class="text-center text-xs text-slate-400 mt-5">
                All fields marked <span class="text-red-400">*</span> are required.
            </p>
        </div>
    </main>

    <!-- ===================== MANAGE MODAL (Department / Branch CRUD) ===================== -->
    <div id="manageModalOverlay" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-lg overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                <h2 id="manageModalTitle" class="text-base font-semibold text-slate-800">Manage Departments</h2>
                <button type="button" onclick="closeManageModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4">
                <!-- Add new -->
                <div class="flex gap-2 mb-4">
                    <input type="text" id="manageNewName" placeholder="Enter name"
                        class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="button" onclick="submitAdd()"
                        class="px-4 py-2 bg-black hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        Add
                    </button>
                </div>

                <div id="manageMsg" class="text-xs mb-2 hidden"></div>

                <!-- List -->
                <div id="manageList" class="space-y-2 max-h-72 overflow-y-auto pr-1"></div>
                <p id="manageEmpty" class="text-sm text-slate-400 text-center py-4 hidden">No items yet.</p>
            </div>
        </div>
    </div>

    <script>
        const departmentsData = <?= json_encode($departments) ?>;
        const branchesData = <?= json_encode($branches) ?>;

        let currentType = 'dept'; // 'dept' or 'branch'
        let currentItems = [];

        const typeConfig = {
            dept: {
                title: 'Manage Departments',
                selectId: 'department_id',
                addAction: 'dept_add',
                editAction: 'dept_edit',
                deleteAction: 'dept_delete',
            },
            branch: {
                title: 'Manage Branches',
                selectId: 'branch_id',
                addAction: 'branch_add',
                editAction: 'branch_edit',
                deleteAction: 'branch_delete',
                setMainAction: 'branch_set_main',
            }
        };

        function openManageModal(type) {
            currentType = type;
            currentItems = (type === 'dept' ? departmentsData : branchesData).slice();
            document.getElementById('manageModalTitle').textContent = typeConfig[type].title;
            document.getElementById('manageNewName').value = '';
            hideMsg();
            renderList();
            const overlay = document.getElementById('manageModalOverlay');
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        function closeManageModal() {
            const overlay = document.getElementById('manageModalOverlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        function showMsg(text, isError) {
            const el = document.getElementById('manageMsg');
            el.textContent = text;
            el.className = 'text-xs mb-2 ' + (isError ? 'text-red-600' : 'text-emerald-600');
        }

        function hideMsg() {
            const el = document.getElementById('manageMsg');
            el.classList.add('hidden');
        }

        function renderList() {
            const listEl = document.getElementById('manageList');
            const emptyEl = document.getElementById('manageEmpty');
            listEl.innerHTML = '';

            if (currentItems.length === 0) {
                emptyEl.classList.remove('hidden');
                return;
            }
            emptyEl.classList.add('hidden');

            currentItems.forEach(item => {
                const row = document.createElement('div');
                row.className = 'flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2';
                row.id = 'row-' + item.id;

                const isBranch = currentType === 'branch';
                const isMain = isBranch && Number(item.is_main) === 1;

                const mainBadge = isMain
                    ? `<span class="text-[10px] font-semibold uppercase tracking-wide bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded">Main</span>`
                    : '';

                const setMainBtn = (isBranch && !isMain)
                    ? `<button type="button" class="text-slate-400 hover:text-indigo-600" onclick="setMain(${item.id})" title="Set as Main Branch">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.076 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </button>`
                    : '';

                const deleteBtn = isMain
                    ? `<span class="w-4 h-4 inline-block"></span>` // no delete for main branch
                    : `<button type="button" class="text-slate-400 hover:text-red-600" onclick="deleteItem(${item.id})" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>`;

                row.innerHTML = `
                    <span class="flex-1 text-sm text-slate-700 flex items-center gap-2" id="label-${item.id}">
                        <span>${escapeHtml(item.name)}</span> ${mainBadge}
                    </span>
                    ${setMainBtn}
                    <button type="button" class="text-slate-400 hover:text-indigo-600" onclick="startEdit(${item.id})" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    ${deleteBtn}
                `;
                listEl.appendChild(row);
            });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function startEdit(id) {
            const item = currentItems.find(i => i.id == id);
            if (!item) return;
            const labelEl = document.getElementById('label-' + id);
            labelEl.outerHTML = `<input type="text" id="label-${id}" value="${escapeHtml(item.name)}"
                class="flex-1 px-2 py-1 border border-indigo-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">`;

            const row = document.getElementById('row-' + id);
            row.querySelector('button[title="Edit"]').outerHTML = `
                <button type="button" class="text-emerald-500 hover:text-emerald-700" onclick="saveEdit(${id})" title="Save">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>`;
        }

        function submitAdd() {
            const input = document.getElementById('manageNewName');
            const name = input.value.trim();
            if (!name) { showMsg('Name cannot be empty.', true); return; }

            const cfg = typeConfig[currentType];
            postAjax(cfg.addAction, { name: name }).then(res => {
                if (res.success) {
                    const newItem = { id: res.id, name: res.name };
                    if (currentType === 'branch') newItem.is_main = res.is_main ?? 0;

                    currentItems.push(newItem);
                    updateSourceData();
                    addSelectOption(res.id, res.name);
                    renderList();
                    input.value = '';
                    showMsg(res.message, false);
                } else {
                    showMsg(res.message, true);
                }
            });
        }

        function saveEdit(id) {
            const input = document.getElementById('label-' + id);
            const newName = input.value.trim();
            if (!newName) { showMsg('Name cannot be empty.', true); return; }

            const cfg = typeConfig[currentType];
            postAjax(cfg.editAction, { id: id, name: newName }).then(res => {
                if (res.success) {
                    const item = currentItems.find(i => i.id == id);
                    if (item) item.name = res.name;
                    updateSourceData();
                    updateSelectOption(id, res.name);
                    renderList();
                    showMsg(res.message, false);
                } else {
                    showMsg(res.message, true);
                }
            });
        }

        function deleteItem(id) {
            if (!confirm('Delete this item?')) return;
            const cfg = typeConfig[currentType];
            postAjax(cfg.deleteAction, { id: id }).then(res => {
                if (res.success) {
                    currentItems = currentItems.filter(i => i.id != id);
                    updateSourceData();
                    removeSelectOption(id);
                    renderList();
                    showMsg(res.message, false);
                } else {
                    showMsg(res.message, true);
                }
            });
        }

        function setMain(id) {
            const cfg = typeConfig[currentType];
            if (!cfg.setMainAction) return;
            postAjax(cfg.setMainAction, { id: id }).then(res => {
                if (res.success) {
                    currentItems.forEach(i => { i.is_main = (i.id == id) ? 1 : 0; });
                    updateSourceData();
                    updateSelectMainLabels();
                    renderList();
                    showMsg(res.message, false);
                } else {
                    showMsg(res.message, true);
                }
            });
        }

        function updateSourceData() {
            if (currentType === 'dept') {
                departmentsData.length = 0;
                departmentsData.push(...currentItems);
            } else {
                branchesData.length = 0;
                branchesData.push(...currentItems);
            }
        }

        function addSelectOption(id, name) {
            const select = document.getElementById(typeConfig[currentType].selectId);
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = name;
            select.appendChild(opt);
        }

        function updateSelectOption(id, name) {
            const select = document.getElementById(typeConfig[currentType].selectId);
            const opt = select.querySelector(`option[value="${id}"]`);
            if (opt) {
                const isMain = branchesData.find(b => b.id == id && Number(b.is_main) === 1);
                opt.textContent = name + (currentType === 'branch' && isMain ? ' (Main)' : '');
            }
        }

        function updateSelectMainLabels() {
            if (currentType !== 'branch') return;
            const select = document.getElementById('branch_id');
            branchesData.forEach(b => {
                const opt = select.querySelector(`option[value="${b.id}"]`);
                if (opt) opt.textContent = b.name + (Number(b.is_main) === 1 ? ' (Main)' : '');
            });
        }

        function removeSelectOption(id) {
            const select = document.getElementById(typeConfig[currentType].selectId);
            const opt = select.querySelector(`option[value="${id}"]`);
            if (opt) {
                if (opt.selected) select.value = '';
                opt.remove();
            }
        }

        function postAjax(action, data) {
            const body = new URLSearchParams({ ajax_action: action, ...data });
            return fetch('', { method: 'POST', body: body })
                .then(r => r.json())
                .catch(() => ({ success: false, message: 'Network error.' }));
        }

        document.getElementById('manageModalOverlay').addEventListener('click', function (e) {
            if (e.target === this) closeManageModal();
        });
    </script>
</body>

</html>