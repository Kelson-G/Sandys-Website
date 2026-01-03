<?php
declare(strict_types=1);

// Contact Form Handler
// Update the $recipient_email variable with the actual email address

// Set recipient email (UPDATE THIS with your email address)
$recipient_email = ""; // Leave blank for now, user will add email address

/**
 * Sanitize form input
 */
function sanitizeInput(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get POST data safely
 */
function getPostData(string $key): string {
    return sanitizeInput($_POST[$key] ?? '');
}

/**
 * Send JSON response and exit
 */
function sendResponse(int $statusCode, array $data): never {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

// Check if form was submitted
match ($_SERVER['REQUEST_METHOD'] ?? null) {
    'POST' => handleContactForm(),
    default => sendResponse(405, ['error' => 'Method not allowed.'])
};

/**
 * Handle contact form submission
 */
function handleContactForm(): never {
    global $recipient_email;
    
    // Validate recipient email is set
    if (empty($recipient_email)) {
        sendResponse(500, ['error' => 'Email configuration incomplete. Please contact the site administrator.']);
    }
    
    // Get form data and sanitize
    $name = getPostData('name');
    $email = getPostData('email');
    $phone = getPostData('phone');
    $company = getPostData('company');
    $subject = getPostData('subject');
    $message = getPostData('message');
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        sendResponse(400, ['error' => 'Please fill in all required fields.']);
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(400, ['error' => 'Please enter a valid email address.']);
    }
    
    // Prepare email headers using named arguments
    $headers = buildEmailHeaders(from: $email);
    
    // Prepare email body
    $email_body = buildEmailBody(
        name: $name,
        email: $email,
        phone: $phone,
        company: $company,
        subject: $subject,
        message: $message
    );
    
    // Send email
    if (mail($recipient_email, "New Contact: " . $subject, $email_body, $headers)) {
        sendResponse(200, ['success' => 'Thank you! Your message has been sent successfully. We will get back to you soon.']);
    } else {
        sendResponse(500, ['error' => 'There was an error sending your message. Please try again later.']);
    }
}

/**
 * Build email headers
 */
function buildEmailHeaders(string $from): string {
    return implode("\r\n", [
        "MIME-Version: 1.0",
        "Content-type: text/html; charset=UTF-8",
        "From: {$from}",
        "Reply-To: {$from}",
    ]);
}

/**
 * Build email body
 */
function buildEmailBody(string $name, string $email, string $phone, string $company, string $subject, string $message): string {
    $phoneHtml = !empty($phone) ? "<p><strong>Phone:</strong> $phone</p>" : '';
    $companyHtml = !empty($company) ? "<p><strong>Company/Organization:</strong> $company</p>" : '';
    
    return <<<HTML
    <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            {$phoneHtml}
            {$companyHtml}
            <p><strong>Subject:</strong> $subject</p>
            <p><strong>Message:</strong></p>
            <p>{$message}</p>
        </body>
    </html>
    HTML;
}
?>
