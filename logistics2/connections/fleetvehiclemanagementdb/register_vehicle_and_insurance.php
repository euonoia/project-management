<?php
session_start();
include('../../../database/connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../fleetvehiclemanagement/index.php');
    exit();
}

function g($key) { return isset($_POST[$key]) ? trim($_POST[$key]) : ''; }

$user_id = $_SESSION['user_id'];
$registration_id = g('registration_id');

// --- Vehicle fields ---
$vehicle_plate          = g('vehicle_plate');
$conduction_sticker     = g('conduction_sticker');
$car_brand              = g('car_brand');
$model                  = g('model');
$year                   = g('year');
$vehicle_type           = g('vehicle_type');
$color                  = g('color');
$chassis_number         = g('chassis_number');
$engine_number          = g('engine_number');
$passenger_capacity     = g('passenger_capacity');
$fuel_type               = g('fuel_type');
$current_mileage        = g('current_mileage');

// --- Document fields ---
$mv_file_number         = g('mv_file_number');
$lto_orcr_number        = g('lto_orcr_number');
$registration_expiry    = g('registration_expiry');
$ltfrb_case_number      = g('ltfrb_case_number');
$ltfrb_franchise_expiry = g('ltfrb_franchise_expiry');
$tnvs_accreditation_number = g('tnvs_accreditation_number');
$tnvs_expiry            = g('tnvs_expiry');

// --- Insurance fields ---
$insurance_provider    = g('insurance');
$policy_number         = g('policy_number');
$insurance_type        = g('insurance_type');
$coverage_type         = g('coverage_type');
$num_passengers        = g('num_passengers_covered');
$start_date            = g('start_date');
$expiration_date       = g('expiration_date');
$premium_amount        = g('premium_amount');
$renewal_reminders     = g('renewal_reminders');
$status                = g('status');
$agent_contact_person  = g('agent_contact_person');

// --- Validation ---
$required = [
    'registration_id' => $registration_id,
    'vehicle_plate'   => $vehicle_plate,
    'insurance'       => $insurance_provider,
    'policy_number'   => $policy_number,
    'insurance_type'  => $insurance_type,
    'coverage_type'   => $coverage_type,
    'num_passengers_covered' => $num_passengers,
    'start_date'      => $start_date,
    'expiration_date' => $expiration_date,
    'premium_amount'  => $premium_amount,
    'renewal_reminders' => $renewal_reminders,
    'status'          => $status,
    'agent_contact_person' => $agent_contact_person,
];
foreach ($required as $k => $v) {
    if ($v === '') {
        http_response_code(400);
        echo "Missing required field: {$k}";
        exit();
    }
}

// --- Handle insurance scanned_copy upload ---
if (!isset($_FILES['scanned_copy']) || $_FILES['scanned_copy']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo 'Missing or invalid scanned_copy upload.';
    exit();
}
$upload = $_FILES['scanned_copy'];
$allowedExt = ['jpg','jpeg','png','gif','webp'];
$origName = $upload['name'];
$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    http_response_code(400);
    echo 'Only image uploads are allowed (jpg, jpeg, png, gif, webp).';
    exit();
}
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($upload['tmp_name']);
if (strpos($mime, 'image/') !== 0) {
    http_response_code(400);
    echo 'Uploaded file is not a valid image.';
    exit();
}
$baseDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'insurance';
if (!is_dir($baseDir)) {
    if (!mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
        http_response_code(500);
        echo 'Failed to create upload directory.';
        exit();
    }
}
$uniqueName = 'INS_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath = $baseDir . DIRECTORY_SEPARATOR . $uniqueName;
if (!move_uploaded_file($upload['tmp_name'], $destPath)) {
    http_response_code(500);
    echo 'Failed to move uploaded file.';
    exit();
}
$scanned_copy_path = 'logistics2/uploads/insurance/' . $uniqueName;

// --- Insert into vehicles ---
$sql_vehicle = "INSERT INTO vehicles (
    registration_id, user_id, 
    vehicle_plate, conduction_sticker, car_brand, model, year,
    vehicle_type, color, chassis_number, engine_number,
    passenger_capacity, fuel_type, current_mileage
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_vehicle = $conn->prepare($sql_vehicle);
if (!$stmt_vehicle) {
    http_response_code(500);
    echo 'Prepare failed (vehicle): ' . $conn->error;
    exit();
}
$stmt_vehicle->bind_param(
    'ssssssssssssss',
    $registration_id,
    $user_id,
    $vehicle_plate,
    $conduction_sticker,
    $car_brand,
    $model,
    $year,
    $vehicle_type,
    $color,
    $chassis_number,
    $engine_number,
    $passenger_capacity,
    $fuel_type,
    $current_mileage
);
if (!$stmt_vehicle->execute()) {
    http_response_code(500);
    echo 'Insert failed (vehicle): ' . $stmt_vehicle->error;
    $stmt_vehicle->close();
    exit();
}
$stmt_vehicle->close();

// --- Insert into documents ---
$sql_doc = "INSERT INTO documents (
    registration_id, user_id, mv_file_number, lto_orcr_number, registration_expiry, ltfrb_case_number, ltfrb_franchise_expiry, passenger_capacity, tnvs_accreditation_number, tnvs_expiry
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_doc = $conn->prepare($sql_doc);
if ($stmt_doc) {
    $stmt_doc->bind_param(
        'ssssssssss',
        $registration_id,
        $user_id,
        $mv_file_number,
        $lto_orcr_number,
        $registration_expiry,
        $ltfrb_case_number,
        $ltfrb_franchise_expiry,
        $passenger_capacity,
        $tnvs_accreditation_number,
        $tnvs_expiry
    );
    $stmt_doc->execute();
    $stmt_doc->close();
}

// --- Insert into vehicle_insurance ---
$sql_ins = "INSERT INTO vehicle_insurance (
    registration_id_insurance,
    user_id,
    insurance_provider,
    policy_number,
    insurance_type,
    coverage_type,
    num_passengers_covered,
    start_date,
    expiration_date,
    premium_amount,
    renewal_reminders,
    status,
    agent_contact_person,
    scanned_copy_path
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_ins = $conn->prepare($sql_ins);
if (!$stmt_ins) {
    http_response_code(500);
    echo 'Prepare failed (insurance): ' . $conn->error;
    exit();
}
$stmt_ins->bind_param(
    'ssssssssssssss',
    $registration_id,
    $user_id,
    $insurance_provider,
    $policy_number,
    $insurance_type,
    $coverage_type,
    $num_passengers,
    $start_date,
    $expiration_date,
    $premium_amount,
    $renewal_reminders,
    $status,
    $agent_contact_person,
    $scanned_copy_path
);

if ($stmt_ins->execute()) {
    // Promote current user to driver role upon successful submission
    if ($update_role = $conn->prepare("UPDATE users SET role = 'driver' WHERE user_id = ?")) {
        $update_role->bind_param('s', $user_id);
        $update_role->execute();
        $update_role->close();
    }
    // Reflect updated role in the session for immediate effect
    $_SESSION['role'] = 'driver';

    $stmt_ins->close();
    header('Location: ../../fleetvehiclemanagement/index.php?registered=1&reg=' . urlencode($registration_id));
    exit();
} else {
    http_response_code(500);
    echo 'Insert failed (insurance): ' . $stmt_ins->error;
    $stmt_ins->close();
    exit();
}
