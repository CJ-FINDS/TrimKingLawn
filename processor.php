<?php
// Free Estimate form handler for trimkinglawn.com
// The original processor.php could not be recovered from archive.org, so this is a new one.

// ---- SETTINGS: change these two lines ----
$to   = 'trimkinglawn@aol.com';          // where estimate requests are delivered
$from = 'no-reply@trimkinglawn.com';     // must be a real mailbox/domain on your new hosting
// ------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: freeestimate.html');
    exit;
}

// Spam trap: real visitors never see or fill this field
if (!empty($_POST['website'])) {
    header('Location: thankyou.html');
    exit;
}

// Placeholder texts the form starts with (treated as "empty")
$placeholders = array(
    'custName'  => 'Enter Your Name',
    'custAddr'  => 'Enter Your Address',
    'custCity'  => 'Enter Your City',
    'custZip'   => 'Enter Your Zip',
    'custEmail' => 'Enter Your Email',
    'custPhone' => 'Enter Your Phone Number',
);

function clean($v) {
    $v = trim((string)$v);
    return str_replace(array("\r", "\n", "%0a", "%0d"), ' ', $v);   // blocks email header injection
}

$data = array();
foreach ($placeholders as $key => $ph) {
    $val = isset($_POST[$key]) ? clean($_POST[$key]) : '';
    $data[$key] = ($val === $ph) ? '' : $val;
}

$services = array();
if (!empty($_POST['services']) && is_array($_POST['services'])) {
    foreach ($_POST['services'] as $s) { $services[] = clean($s); }
}
$other = isset($_POST['otherQuestions']) ? trim(strip_tags($_POST['otherQuestions'])) : '';

// Basic validation
$errors = array();
if ($data['custName'] === '') { $errors[] = 'Please enter your name.'; }
if ($data['custEmail'] === '' && $data['custPhone'] === '') { $errors[] = 'Please enter an email address or phone number so we can reach you.'; }
if ($data['custEmail'] !== '' && !filter_var($data['custEmail'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'The email address does not look valid.'; }

if ($errors) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Free Estimate</title></head><body style="font-family:Arial,sans-serif;padding:30px">';
    echo '<h2>Almost there</h2><ul>';
    foreach ($errors as $e) { echo '<li>' . htmlspecialchars($e) . '</li>'; }
    echo '</ul><p><a href="javascript:history.back()">&laquo; Go back and fix the form</a></p></body></html>';
    exit;
}

$body  = "New Free Estimate Request\n";
$body .= "-------------------------\n";
$body .= "Name:    " . $data['custName'] . "\n";
$body .= "Address: " . $data['custAddr'] . "\n";
$body .= "City:    " . $data['custCity'] . "\n";
$body .= "Zip:     " . $data['custZip'] . "\n";
$body .= "Email:   " . $data['custEmail'] . "\n";
$body .= "Phone:   " . $data['custPhone'] . "\n\n";
$body .= "Services: " . ($services ? implode(', ', $services) : '(none selected)') . "\n";
if ($other !== '') { $body .= "\nOther questions:\n" . $other . "\n"; }
$body .= "\nSent from the website form on " . date('Y-m-d H:i') . "\n";

$headers  = "From: Trim King Website <" . $from . ">\r\n";
if ($data['custEmail'] !== '') { $headers .= "Reply-To: " . $data['custEmail'] . "\r\n"; }
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = mail($to, 'Free Estimate Request from ' . $data['custName'], $body, $headers);

if ($sent) {
    header('Location: thankyou.html');
} else {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Free Estimate</title></head><body style="font-family:Arial,sans-serif;padding:30px">';
    echo '<h2>Sorry, something went wrong</h2><p>Please call us at 732.671.8358 and we will help you right away.</p></body></html>';
}
exit;
