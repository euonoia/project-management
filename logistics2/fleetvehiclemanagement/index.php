<?php
include('../../database/connect.php');
session_start();
if (!isset($_SESSION['firstname']) && !isset($_SESSION['lastname']) && !isset($_SESSION['email'])) {
    header('Location: ../index.php');
    exit();
}

$firstname = $_SESSION['firstname'] ?? '';
$lastname = $_SESSION['lastname'] ?? '';
if ((($firstname === '' || $lastname === '') || !isset($_SESSION['user_id'])) && isset($_SESSION['email']) && isset($conn)) {
    // Always use the correct column name for your users table. If your PK is 'id', use that.
    $stmt = $conn->prepare("SELECT id AS user_id, firstname, lastname FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $_SESSION['email']);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res && $row = $res->fetch_assoc()) {
                if (!isset($_SESSION['user_id']) && isset($row['user_id'])) {
                    $_SESSION['user_id'] = (int)$row['user_id'];
                }
                $firstname = $row['firstname'];
                $lastname = $row['lastname'];
            }
        }
        $stmt->close();
    }
}
// Always define $userId before any use
$userId = $_SESSION['user_id'] ?? '';

$ownerName = trim($firstname . ' ' . $lastname);
function generateRegistrationId(): string {
    $prefix = 'REG-';
    $date = date('Ymd'); // YYYYMMDD
    $rand = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
    return $prefix . $date . '-' . $rand;
}

// Registration ID logic: persist registration_id in session until user logs out or starts a new registration



// Unified registration_id logic: always use the latest registration_id from either vehicles or insurance for the user
$registration = '';
if (isset($_GET['reg']) && $_GET['reg'] !== '') {
    $registration = $_GET['reg'];
    $_SESSION['pending_registration_id'] = $registration;
} else if ($userId !== '' && isset($conn)) {
    $sqlLatest = "SELECT reg_id FROM (\n        SELECT registration_id AS reg_id, id, created_at FROM vehicles WHERE user_id = ?\n        UNION ALL\n        SELECT registration_id_insurance AS reg_id, id, created_at FROM vehicle_insurance WHERE user_id = ?\n    ) AS all_regs ORDER BY created_at DESC, id DESC LIMIT 1";
    $stmtLatest = $conn->prepare($sqlLatest);
    $registrationFound = false;
    if ($stmtLatest) {
        $stmtLatest->bind_param("ss", $userId, $userId);
        if ($stmtLatest->execute()) {
            $resLatest = $stmtLatest->get_result();
            if ($resLatest && $rowLatest = $resLatest->fetch_assoc()) {
                $registration = $rowLatest['reg_id'];
                $_SESSION['pending_registration_id'] = $registration;
                $registrationFound = true;
            }
        }
        $stmtLatest->close();
    }
    if (!$registrationFound) {
        $registration = generateRegistrationId();
        $_SESSION['pending_registration_id'] = $registration;
    }
} else {
    $registration = generateRegistrationId();
    $_SESSION['pending_registration_id'] = $registration;
}





// Use the unified registration for both forms
$insurance_registration = $registration;
$_SESSION['pending_insurance_registration_id'] = $insurance_registration;

// Prefill form if a matching insurance exists for this user and registration
$insuranceForm = [];
if ($userId !== '' && isset($conn)) {
    $stmtIns = $conn->prepare("SELECT registration_id_insurance, insurance_provider AS insurance, policy_number, insurance_type, coverage_type, num_passengers_covered, start_date, expiration_date, premium_amount, renewal_reminders, status, agent_contact_person FROM vehicle_insurance WHERE registration_id_insurance = ? AND user_id = ? LIMIT 1");
    if ($stmtIns) {
        $stmtIns->bind_param("ss", $registration, $userId);
        if ($stmtIns->execute()) {
            $resIns = $stmtIns->get_result();
            if ($resIns && $rowIns = $resIns->fetch_assoc()) {
                $insuranceForm = $rowIns;
            }
        }
        $stmtIns->close();
    }
}

// Prefill form if a matching vehicle exists for this user and registration
$form = [];
if ($userId !== '' && isset($conn)) {
    $sql = "SELECT 
        v.registration_id, v.user_id, 
        u.firstname AS owner_firstname, u.lastname AS owner_lastname,
        v.conduction_sticker, v.vehicle_plate, v.car_brand, v.model, v.year, v.vehicle_type, v.color, v.chassis_number, v.engine_number, v.current_mileage,
        d.id AS document_id, d.registration_id AS doc_registration_id, d.user_id AS doc_user_id, d.mv_file_number, d.lto_orcr_number, d.registration_expiry, d.ltfrb_case_number, d.ltfrb_franchise_expiry, d.tnvs_accreditation_number, d.tnvs_expiry, d.passenger_capacity, d.fuel_type
    FROM vehicles v 
    INNER JOIN users u ON v.user_id = u.user_id
    LEFT JOIN documents d ON v.registration_id = d.registration_id AND v.user_id = d.user_id 
    WHERE v.user_id = ? AND v.registration_id = ? 
    LIMIT 1";
    $stmt2 = $conn->prepare($sql);
    if ($stmt2) {
        $stmt2->bind_param("ss", $userId, $registration);
        if ($stmt2->execute()) {
            $res2 = $stmt2->get_result();
            if ($res2 && $row2 = $res2->fetch_assoc()) {
                $form = $row2;
                $firstname = $row2['owner_firstname'] ?? $firstname;
                $lastname = $row2['owner_lastname'] ?? $lastname;
            } else {
                $form = [];
            }
        }
        $stmt2->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Vehicle Management</title>
</head>
<body>
    <header>
        <h1>Information</h1>
        <a href="../reservation/reserve.php">return</a>
    </header>

    <div class="registration">
        <!-- Integrated Vehicle & Insurance Registration Form -->
        <form action="../connections/fleetvehiclemanagementdb/register_vehicle_and_insurance.php" id="vehicle_insurance_registration_form" method="post" enctype="multipart/form-data" class="form-card">
            <div class="form-header">
                <h2 class="form-title">Vehicle & Insurance Registration</h2>
                <p class="form-subtitle">Provide vehicle and insurance details</p>
                <div class="form-group">
                    <label for="registration_id">Registration ID</label>
                    <input type="text" name="registration_id" id="registration_id" value="<?php echo htmlspecialchars($registration, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>
            </div>

            <fieldset class="section">
                <legend>Vehicle Details</legend>
                <div class="form-grid">
                    <!-- Vehicle fields (same as before) -->
                    <div class="form-group">
                        <label for="owner_firstname">Firstname</label>
                        <input type="text"  name="owner_firstname" value="<?php echo htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8'); ?>" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="owner_lastname">Lastname</label>
                        <input type="text"  name="owner_lastname" value="<?php echo htmlspecialchars($lastname, ENT_QUOTES, 'UTF-8'); ?>" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="vehicle_plate">Vehicle Plate Number</label>
                        <input type="text" id="vehicle_plate" name="vehicle_plate" value="<?php echo htmlspecialchars($form['vehicle_plate'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="conduction_sticker">Conduction Sticker No.</label>
                        <input type="text" id="conduction_sticker" name="conduction_sticker" value="<?php echo htmlspecialchars($form['conduction_sticker'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="car_brand">Car Brand</label>
                        <input type="text" id="car_brand" name="car_brand" value="<?php echo htmlspecialchars($form['car_brand'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($form['model'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($form['year'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>

                   <div class="form-group">
                    <label for="vehicle_type">Vehicle Type</label>
                    <select id="vehicle_type" name="vehicle_type" 
                            <?php echo !empty($form) ? 'disabled' : ''; ?> required>
                        <option value="">Select Vehicle Type</option>
                        <option value="sedan" <?php echo (isset($form['vehicle_type']) && $form['vehicle_type']==='sedan')?'selected':''; ?>>Sedan</option>
                        <option value="suv" <?php echo (isset($form['vehicle_type']) && $form['vehicle_type']==='suv')?'selected':''; ?>>SUV</option>
                        <option value="hatchback" <?php echo (isset($form['vehicle_type']) && $form['vehicle_type']==='hatchback')?'selected':''; ?>>Hatchback</option>
                        <option value="mpv" <?php echo (isset($form['vehicle_type']) && $form['vehicle_type']==='mpv')?'selected':''; ?>>MPV</option>
                        <option value="van" <?php echo (isset($form['vehicle_type']) && $form['vehicle_type']==='van')?'selected':''; ?>>Van</option>
                        <option value="others" <?php echo (isset($form['vehicle_type']) && $form['vehicle_type']==='others')?'selected':''; ?>>Others</option>
                    </select>
                    </div>

                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($form['color'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="chassis_number">Chassis Number</label>
                        <input type="text" id="chassis_number" name="chassis_number" value="<?php echo htmlspecialchars($form['chassis_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="engine_number">Engine Number</label>
                        <input type="text" id="engine_number" name="engine_number" value="<?php echo htmlspecialchars($form['engine_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>
                </div>
            </fieldset>
            <fieldset class="section">
                    <legend>LTFRB Franchise Details</legend>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="mv_file_number">MV File Number</label>
                        <input type="text" id="mv_file_number" name="mv_file_number" value="<?php echo htmlspecialchars($form['mv_file_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form['mv_file_number']) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="lto_orcr_number">LTO ORCR Number</label>
                        <input type="text" id="lto_orcr_number" name="lto_orcr_number" value="<?php echo htmlspecialchars($form['lto_orcr_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form['lto_orcr_number']) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="registration_expiry">LTO Registration Expiry</label>
                        <input type="date" id="registration_expiry" name="registration_expiry" value="<?php echo htmlspecialchars($form['registration_expiry'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form['registration_expiry']) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="ltfrb_case_number">Franchise / Case Number</label>
                        <input type="text" id="ltfrb_case_number" name="ltfrb_case_number" value="<?php echo htmlspecialchars($form['ltfrb_case_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form['ltfrb_case_number']) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="ltfrb_franchise_expiry">Franchise Expiry</label>
                        <input type="date" id="ltfrb_franchise_expiry" name="ltfrb_franchise_expiry" value="<?php echo htmlspecialchars($form['ltfrb_franchise_expiry'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form['ltfrb_franchise_expiry']) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="tnvs_accreditation">TNVS Accreditation Number</label>
                        <input type="text" id="tnvs_accreditation" name="tnvs_accreditation_number" value="<?php echo htmlspecialchars($form['tnvs_accreditation_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form['tnvs_accreditation_number']) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="tnvs_expiry">TNVS Expiry</label>
                        <input type="date" id="tnvs_expiry" name="tnvs_expiry" value="<?php echo htmlspecialchars($form['tnvs_expiry'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form['tnvs_expiry']) ? 'readonly' : ''; ?> required>
                    </div>
                  <div class="form-group">
                    <label for="passenger_capacity">Passenger Capacity</label>
                    <input type="text" id="passenger_capacity" name="passenger_capacity"
                            value="<?php echo htmlspecialchars($form['passenger_capacity'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                            <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>

                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type" <?php echo !empty($form) ? 'disabled' : ''; ?> required>
                            <option value="">Select Vehicle Fuel Type</option>
                            <option value="gasoline" <?php echo (isset($form['fuel_type']) && $form['fuel_type']==='gasoline')?'selected':''; ?>>Gasoline</option>
                            <option value="diesel" <?php echo (isset($form['fuel_type']) && $form['fuel_type']==='diesel')?'selected':''; ?>>Diesel</option>
                            <option value="electric" <?php echo (isset($form['fuel_type']) && $form['fuel_type']==='electric')?'selected':''; ?>>Electric</option>
                            <option value="hybrid" <?php echo (isset($form['fuel_type']) && $form['fuel_type']==='hybrid')?'selected':''; ?>>Hybrid</option>
                            <option value="others" <?php echo (isset($form['fuel_type']) && $form['fuel_type']==='others')?'selected':''; ?>>Others</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="current_mileage">Current Mileage</label>
                        <input type="text" id="current_mileage" name="current_mileage" value="<?php echo htmlspecialchars($form['current_mileage'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($form) ? 'readonly' : ''; ?> required>
                    </div>
                </div>
            </fieldset>

            <fieldset class="section">
                <legend>Insurance Details</legend>
                <div class="form-grid">
                    <!-- Insurance fields (same as before) -->
                    <div class="form-group">
                        <label for="insurance">Insurance Provider</label>
                        <input type="text" id="insurance" name="insurance" value="<?php echo htmlspecialchars($insuranceForm['insurance'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="policy_number">Policy Number</label>
                        <input type="text" id="policy_number" name="policy_number" value="<?php echo htmlspecialchars($insuranceForm['policy_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="insurance_type">Insurance Type</label>
                        <input type="text" id="insurance_type" name="insurance_type" value="<?php echo htmlspecialchars($insuranceForm['insurance_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="coverage_type">Coverage Type</label>
                        <input type="text" id="coverage_type" name="coverage_type" value="<?php echo htmlspecialchars($insuranceForm['coverage_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="num_passengers_covered">Number of Passengers Covered</label>
                        <input type="text" id="num_passengers_covered" name="num_passengers_covered" value="<?php echo htmlspecialchars($insuranceForm['num_passengers_covered'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date (coverage starts)</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($insuranceForm['start_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="expiration_date">Expiration Date</label>
                        <input type="date" id="expiration_date" name="expiration_date" value="<?php echo htmlspecialchars($insuranceForm['expiration_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="premium_amount">Premium Amount</label>
                        <input type="number" step="0.01" min="0" id="premium_amount" name="premium_amount" value="<?php echo htmlspecialchars($insuranceForm['premium_amount'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="renewal_reminders">Renewal Reminders (days before)</label>
                        <input type="number" min="0" id="renewal_reminders" name="renewal_reminders" value="<?php echo htmlspecialchars($insuranceForm['renewal_reminders'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" <?php echo !empty($insuranceForm) ? 'disabled' : ''; ?> required>
                            <option value="">Select Status</option>
                            <option value="active" <?php echo (isset($insuranceForm['status']) && $insuranceForm['status']==='active')?'selected':''; ?>>Active</option>
                            <option value="expired" <?php echo (isset($insuranceForm['status']) && $insuranceForm['status']==='expired')?'selected':''; ?>>Expired</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="agent_contact_person">Agent Contact Person</label>
                        <input type="text" id="agent_contact_person" name="agent_contact_person" value="<?php echo htmlspecialchars($insuranceForm['agent_contact_person'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo !empty($insuranceForm) ? 'readonly' : ''; ?> required>
                    </div>
                    <div class="form-group full-span">
                        <label for="scanned_copy">Scanned Copy</label>
                        <input type="file" id="scanned_copy" name="scanned_copy" accept="image/*" <?php echo !empty($insuranceForm) ? 'disabled' : ''; ?> required>
                        <small class="hint">Upload a clear image of the insurance document.</small>
                    </div>
                </div>
            </fieldset>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary" <?php echo !empty($form) && !empty($insuranceForm) ? 'disabled' : ''; ?>>Reset</button>
                <button type="submit" class="btn btn-primary" <?php echo !empty($form) && !empty($insuranceForm) ? 'disabled' : ''; ?>>Submit</button>
            </div>
        </form>
    </div>

    <script>
        // Dynamically update form headers based on whether fields are locked (readonly/disabled)
        document.addEventListener('DOMContentLoaded', function () {
            function updateHeaderForForm(formEl, type) {
                if (!formEl) return;
                const hasLockedFields = !!formEl.querySelector('.form-actions .btn-primary[disabled], button[type="submit"][disabled], input[type="submit"][disabled]');
                const header = formEl.querySelector('.form-header');
                const titleEl = header && header.querySelector('.form-title');
                const subtitleEl = header && header.querySelector('.form-subtitle');
                if (!header || !titleEl || !subtitleEl) return;

                if (hasLockedFields) {
                    if (type === 'vehicle') {
                        titleEl.textContent = 'Vehicle Details';
                        subtitleEl.textContent = 'This vehicle is already registered. Fields are locked.';
                    } else if (type === 'insurance') {
                        titleEl.textContent = 'Insurance Details';
                        subtitleEl.textContent = 'Insurance record exists for this registration. Fields are locked.';
                    }
                    header.classList.add('form-header--locked');
                } else {
                    if (type === 'vehicle') {
                        titleEl.textContent = 'Vehicle Registration';
                        subtitleEl.textContent = 'Provide vehicle details';
                    } else if (type === 'insurance') {
                        titleEl.textContent = 'Insurance Registration';
                        subtitleEl.textContent = 'Provide insurance details';
                    }
                    header.classList.remove('form-header--locked');
                }
            }

            updateHeaderForForm(document.getElementById('vehicle_registration_form'), 'vehicle');
            updateHeaderForForm(document.getElementById('insurance_registration_form'), 'insurance');
        });
        document.addEventListener("DOMContentLoaded", () => {
        const vehicleType = document.getElementById("vehicle_type");
        const passengerCapacity = document.getElementById("passenger_capacity");

        // Map vehicle type → default capacity
        const capacityMap = {
            sedan: 5,
            suv: 7,
            hatchback: 5,
            mpv: 7,
            van: 15,
            others: ''
        };

        vehicleType.addEventListener("change", () => {
            const selected = vehicleType.value;
            passengerCapacity.value = capacityMap[selected] ?? '';
        });
        });
    </script>
</body>
</html>
