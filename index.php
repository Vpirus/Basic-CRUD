<?php
// Start session for flash messages
session_start();

// Include files
require_once 'Classes/Database.php';
require_once 'Classes/Events.php';
require_once 'Classes/Participant.php';
require_once 'Classes/Registration.php';

// Connect to database
$db = new Database();
$conn = $db->connect();

// Create models
$eventsModel = new Events($conn);
$participantModel = new Participant($conn);
$registrationModel = new Registration($conn);

$message = '';
$messageType = 'info';

// Helper function
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Input validation
function validateRequired($fields, $data) {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $entity = $_POST['entity'] ?? '';
        $action = $_POST['action'] ?? '';

        // EVENTS
        if ($entity === 'event') {
            if ($action === 'create') {
                validateRequired(['evName', 'evDate', 'evVenue'], $_POST);
                $eventsModel->create([
                    'evName' => trim($_POST['evName']),
                    'evDate' => trim($_POST['evDate']),
                    'evVenue' => trim($_POST['evVenue']),
                    'evRFree' => (float)($_POST['evRFree'] ?? 0)
                ]);
                $_SESSION['flash_message'] = 'Event created successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
            
            if ($action === 'update') {
                validateRequired(['evCode', 'evName', 'evDate', 'evVenue'], $_POST);
                $eventsModel->update((int)$_POST['evCode'], [
                    'evName' => trim($_POST['evName']),
                    'evDate' => trim($_POST['evDate']),
                    'evVenue' => trim($_POST['evVenue']),
                    'evRFree' => (float)($_POST['evRFree'] ?? 0)
                ]);
                $_SESSION['flash_message'] = 'Event updated successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
            
            if ($action === 'delete') {
                validateRequired(['evCode'], $_POST);
                $eventsModel->delete((int)$_POST['evCode']);
                $_SESSION['flash_message'] = 'Event deleted successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
        }

        // PARTICIPANTS
        if ($entity === 'participant') {
            if ($action === 'create') {
                validateRequired(['evCode', 'partFName', 'partLName'], $_POST);
                $participantModel->create([
                    'evCode' => (int)$_POST['evCode'],
                    'partFName' => trim($_POST['partFName']),
                    'partLName' => trim($_POST['partLName']),
                    'partDRate' => (float)($_POST['partDRate'] ?? 0)
                ]);
                $_SESSION['flash_message'] = 'Participant created successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
            
            if ($action === 'update') {
                validateRequired(['partID', 'evCode', 'partFName', 'partLName'], $_POST);
                $participantModel->update((int)$_POST['partID'], [
                    'evCode' => (int)$_POST['evCode'],
                    'partFName' => trim($_POST['partFName']),
                    'partLName' => trim($_POST['partLName']),
                    'partDRate' => (float)($_POST['partDRate'] ?? 0)
                ]);
                $_SESSION['flash_message'] = 'Participant updated successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
            
            if ($action === 'delete') {
                validateRequired(['partID'], $_POST);
                $participantModel->delete((int)$_POST['partID']);
                $_SESSION['flash_message'] = 'Participant deleted successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
        }

        // REGISTRATIONS
        if ($entity === 'registration') {
            if ($action === 'create') {
                validateRequired(['partID', 'regDate', 'regPMode'], $_POST);
                
                // Get participant and event data to calculate fee
                $participant = $participantModel->getById((int)$_POST['partID']);
                $event = $eventsModel->getById((int)$participant['evCode']);
                
                // Calculate: evRFee - partDRate
                $regFPaid = $event['evRFree'] - $participant['partDRate'];
                
                $registrationModel->create([
                    'partID' => (int)$_POST['partID'],
                    'regDate' => trim($_POST['regDate']),
                    'regFPaid' => $regFPaid,
                    'regPMode' => trim($_POST['regPMode'])
                ]);
                $_SESSION['flash_message'] = 'Registration created successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
            
            if ($action === 'update') {
                validateRequired(['regCode', 'partID', 'regDate', 'regPMode'], $_POST);
                
                // Get participant and event data to calculate fee
                $participant = $participantModel->getById((int)$_POST['partID']);
                $event = $eventsModel->getById((int)$participant['evCode']);
                
                // Calculate: evRFee - partDRate
                $regFPaid = $event['evRFree'] - $participant['partDRate'];
                
                $registrationModel->update((int)$_POST['regCode'], [
                    'partID' => (int)$_POST['partID'],
                    'regDate' => trim($_POST['regDate']),
                    'regFPaid' => $regFPaid,
                    'regPMode' => trim($_POST['regPMode'])
                ]);
                $_SESSION['flash_message'] = 'Registration updated successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
            
            if ($action === 'delete') {
                validateRequired(['regCode'], $_POST);
                $registrationModel->delete((int)$_POST['regCode']);
                $_SESSION['flash_message'] = 'Registration deleted successfully!';
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Flash messages
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// Load data
try {
    $events = $eventsModel->getAll();
    $participants = $participantModel->getAll();
    $registrations = $registrationModel->getAll();
} catch (Exception $e) {
    $message = "Error loading data: " . $e->getMessage();
    $messageType = 'error';
    $events = [];
    $participants = [];
    $registrations = [];
}

// Create maps
$eventsMap = [];
foreach ($events as $ev) {
    $eventsMap[$ev['evCode']] = $ev;
}

$participantsMap = [];
foreach ($participants as $p) {
    $participantsMap[$p['partID']] = $p;
}

// Handle edit requests
$editEvent = null;
$editPart = null;
$editReg = null;

if (isset($_GET['edit_event'])) {
    try {
        $editEvent = $eventsModel->getById((int)$_GET['edit_event']);
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

if (isset($_GET['edit_part'])) {
    try {
        $editPart = $participantModel->getById((int)$_GET['edit_part']);
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

if (isset($_GET['edit_reg'])) {
    try {
        $editReg = $registrationModel->getById((int)$_GET['edit_reg']);
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Registration System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif;
            background: #fff;
            color: #000;
            padding: 20px;
            line-height: 1.5;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        h1 { 
            color: #000;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        h2 { 
            color: #000;
            margin: 30px 0 15px;
            font-size: 18px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        .alert-error {
            background: #f0f0f0;
        }
        .alert-success {
            background: #f0f0f0;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            border: 1px solid #000;
            padding: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #000;
            background: #fff;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: 2px solid #000;
        }
        button {
            background: #fff;
            color: #000;
            padding: 10px 20px;
            border: 1px solid #000;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        button:hover {
            background: #fff;
        }
        button:active {
            background: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border: 1px solid #000;
        }
        th {
            background: #000;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border: 1px solid #000;
        }
        .actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .actions a {
            color: #000;
            text-decoration: underline;
        }
        .actions a:hover {
            text-decoration: none;
        }
        .delete-form {
            display: inline;
        }
        .delete-btn {
            background: #fff;
            color: #000;
            border: 1px solid #000;
            padding: 5px 10px;
            font-size: 12px;
        }
        .delete-btn:hover {
            background: #fff;
            color: #000;
        }
        .cancel-link {
            display: inline-block;
            margin-left: 10px;
            color: #000;
            text-decoration: underline;
        }
        .cancel-link:hover {
            text-decoration: none;
        }
        .empty-state {
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
        .note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Events Registration System</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= e($messageType) ?>">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <!-- Event Form -->
            <div class="card">
                <h2><?= $editEvent ? 'Edit Event' : 'Add Event' ?></h2>
                <form method="post">
                    <input type="hidden" name="entity" value="event">
                    <input type="hidden" name="action" value="<?= $editEvent ? 'update' : 'create' ?>">
                    <?php if ($editEvent): ?>
                        <input type="hidden" name="evCode" value="<?= e($editEvent['evCode']) ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Event Name *</label>
                        <input type="text" name="evName" required value="<?= e($editEvent['evName'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="evDate" required value="<?= e($editEvent['evDate'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Venue *</label>
                        <input type="text" name="evVenue" required value="<?= e($editEvent['evVenue'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Registration Fee</label>
                        <input type="number" step="0.01" name="evRFree" value="<?= e($editEvent['evRFree'] ?? '0') ?>">
                    </div>
                    <button type="submit"><?= $editEvent ? 'Update' : 'Create' ?> Event</button>
                    <?php if ($editEvent): ?>
                        <a href="index.php" class="cancel-link">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Participant Form -->
            <div class="card">
                <h2><?= $editPart ? 'Edit Participant' : 'Add Participant' ?></h2>
                <form method="post">
                    <input type="hidden" name="entity" value="participant">
                    <input type="hidden" name="action" value="<?= $editPart ? 'update' : 'create' ?>">
                    <?php if ($editPart): ?>
                        <input type="hidden" name="partID" value="<?= e($editPart['partID']) ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Event *</label>
                        <select name="evCode" required>
                            <option value="">-- Select Event --</option>
                            <?php foreach ($events as $ev): ?>
                                <option value="<?= e($ev['evCode']) ?>" 
                                    <?= ($editPart && $editPart['evCode'] == $ev['evCode']) ? 'selected' : '' ?>>
                                    <?= e($ev['evName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="partFName" required value="<?= e($editPart['partFName'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="partLName" required value="<?= e($editPart['partLName'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Discount Rate</label>
                        <input type="number" step="0.01" name="partDRate" value="<?= e($editPart['partDRate'] ?? '0') ?>">
                    </div>
                    <button type="submit"><?= $editPart ? 'Update' : 'Create' ?> Participant</button>
                    <?php if ($editPart): ?>
                        <a href="index.php" class="cancel-link">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Registration Form -->
            <div class="card">
                <h2><?= $editReg ? 'Edit Registration' : 'Add Registration' ?></h2>
                <form method="post">
                    <input type="hidden" name="entity" value="registration">
                    <input type="hidden" name="action" value="<?= $editReg ? 'update' : 'create' ?>">
                    <?php if ($editReg): ?>
                        <input type="hidden" name="regCode" value="<?= e($editReg['regCode']) ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Participant *</label>
                        <select name="partID" required>
                            <option value="">-- Select Participant --</option>
                            <?php foreach ($participants as $p): ?>
                                <option value="<?= e($p['partID']) ?>"
                                    <?= ($editReg && $editReg['partID'] == $p['partID']) ? 'selected' : '' ?>>
                                    <?= e($p['partFName'] . ' ' . $p['partLName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Registration Date *</label>
                        <input type="date" name="regDate" required value="<?= e($editReg['regDate'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Payment Mode *</label>
                        <select name="regPMode" required>
                            <option value="">-- Select Payment Mode --</option>
                            <option value="Cash" <?= ($editReg && $editReg['regPMode'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
                            <option value="Card" <?= ($editReg && $editReg['regPMode'] == 'Card') ? 'selected' : '' ?>>Card</option>
                        </select>
                    </div>
                    <p class="note">Note: Fee Paid will be automatically calculated as (Event Fee - Discount Rate)</p>
                    <button type="submit"><?= $editReg ? 'Update' : 'Create' ?> Registration</button>
                    <?php if ($editReg): ?>
                        <a href="index.php" class="cancel-link">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- TABLES -->
        <h2>Events List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Registration Fee</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr><td colspan="6" class="empty-state">No events found.</td></tr>
                <?php else: ?>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td><?= e($ev['evCode']) ?></td>
                            <td><?= e($ev['evName']) ?></td>
                            <td><?= date('M d, Y', strtotime($ev['evDate'])) ?></td>
                            <td><?= e($ev['evVenue']) ?></td>
                            <td>₱<?= number_format($ev['evRFree'], 2) ?></td>
                            <td class="actions">
                                <a href="?edit_event=<?= e($ev['evCode']) ?>">Edit</a>
                                <form method="post" class="delete-form" onsubmit="return confirm('Delete this event and all associated data?')">
                                    <input type="hidden" name="entity" value="event">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="evCode" value="<?= e($ev['evCode']) ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Participants List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Discount Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($participants)): ?>
                    <tr><td colspan="6" class="empty-state">No participants found.</td></tr>
                <?php else: ?>
                    <?php foreach ($participants as $p): ?>
                        <tr>
                            <td><?= e($p['partID']) ?></td>
                            <td><?= e($eventsMap[$p['evCode']]['evName'] ?? 'Unknown Event') ?></td>
                            <td><?= e($p['partFName']) ?></td>
                            <td><?= e($p['partLName']) ?></td>
                            <td>₱<?= number_format($p['partDRate'], 2) ?></td>
                            <td class="actions">
                                <a href="?edit_part=<?= e($p['partID']) ?>">Edit</a>
                                <form method="post" class="delete-form" onsubmit="return confirm('Delete this participant?')">
                                    <input type="hidden" name="entity" value="participant">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="partID" value="<?= e($p['partID']) ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Registrations List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Participant</th>
                    <th>Event</th>
                    <th>Registration Date</th>
                    <th>Fee Paid</th>
                    <th>Payment Mode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registrations)): ?>
                    <tr><td colspan="7" class="empty-state">No registrations found.</td></tr>
                <?php else: ?>
                    <?php foreach ($registrations as $r): ?>
                        <?php 
                        $part = $participantsMap[$r['partID']] ?? null;
                        $event = $part ? ($eventsMap[$part['evCode']] ?? null) : null;
                        ?>
                        <tr>
                            <td><?= e($r['regCode']) ?></td>
                            <td><?= $part ? e($part['partFName'] . ' ' . $part['partLName']) : 'Unknown' ?></td>
                            <td><?= $event ? e($event['evName']) : 'Unknown' ?></td>
                            <td><?= date('M d, Y', strtotime($r['regDate'])) ?></td>
                            <td>₱<?= number_format($r['regFPaid'], 2) ?></td>
                            <td><?= e($r['regPMode']) ?></td>
                            <td class="actions">
                                <a href="?edit_reg=<?= e($r['regCode']) ?>">Edit</a>
                                <form method="post" class="delete-form" onsubmit="return confirm('Delete this registration?')">
                                    <input type="hidden" name="entity" value="registration">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="regCode" value="<?= e($r['regCode']) ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>