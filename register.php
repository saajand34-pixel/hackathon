<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For CORS if needed
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// CSV file path
$csvFile = 'registrations.csv';

// Check if file exists, create with headers if not
if (!file_exists($csvFile)) {
    $headers = ['Name', 'Email', 'Course', 'College Name', 'Registered At'];
    $fp = fopen($csvFile, 'w');
    fputcsv($fp, $headers);
    fclose($fp);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $college_name = trim($_POST['college_name'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($course) || empty($college_name)) {
        echo json_encode(['error' => 'Name, email, course, and college name are required.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email format.']);
        exit;
    }

    // Optional: Check for duplicate email in CSV
    $isDuplicate = false;
    if (($handle = fopen($csvFile, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (isset($data[1]) && strtolower($data[1]) === strtolower($email)) {
                $isDuplicate = true;
                break;
            }
        }
        fclose($handle);
    }
    if ($isDuplicate) {
        echo json_encode(['error' => 'Email already registered.']);
        exit;
    }

    // Append to CSV
    $fp = fopen($csvFile, 'a');
    if ($fp) {
        $data = [$name, $email, $course, $college_name, date('Y-m-d H:i:s')];
        fputcsv($fp, $data);
        fclose($fp);
        echo json_encode(['message' => 'Registration successful!']);
    } else {
        echo json_encode(['error' => 'Failed to save data.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>