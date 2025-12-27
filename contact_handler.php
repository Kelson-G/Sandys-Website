<?php
// Contact Form Handler
// Update the $recipient_email variable with the actual email address

// Set recipient email (UPDATE THIS with your email address)
$recipient_email = ""; // Leave blank for now, user will add email address

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate recipient email is set
    if (empty($recipient_email)) {
        http_response_code(500);
        echo json_encode(['error' => 'Email configuration incomplete. Please contact the site administrator.']);
        exit;
    }
    
    // Get form data and sanitize
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $company = htmlspecialchars(trim($_POST['company'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        http_response_code(400);
        echo json_encode(['error' => 'Please fill in all required fields.']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Please enter a valid email address.']);
        exit;
    }
    
    // Prepare email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    
    // Prepare email body
    $email_body = "
    <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>";
    
    if (!empty($phone)) {
        $email_body .= "<p><strong>Phone:</strong> $phone</p>";
    }
    
    if (!empty($company)) {
        $email_body .= "<p><strong>Company/Organization:</strong> $company</p>";
    }
    
    $email_body .= "
            <p><strong>Subject:</strong> $subject</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br($message) . "</p>
        </body>
    </html>";
    
    // Send email
    if (mail($recipient_email, "New Contact: " . $subject, $email_body, $headers)) {
        http_response_code(200);
        echo json_encode(['success' => 'Thank you! Your message has been sent successfully. We will get back to you soon.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'There was an error sending your message. Please try again later.']);
    }
    
    exit;
}

// If not a POST request, return error
http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
?>
