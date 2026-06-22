<?php
$active = 'contact';
$page_title = "Contact — T.";

$status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name !== '' && $email !== '' && $message !== '') {
        // Wire this up to mail() or a database insert as needed.
        $status = 'sent';
    } else {
        $status = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title; ?></title>
<link rel="stylesheet" href="style.css">
<style>
  .contact-form{ max-width: 420px; margin-top: 24px; }
  .contact-form label{ display:block; font-size:11px; color:var(--muted); margin-bottom:6px; font-weight:600; }
  .contact-form input, .contact-form textarea{
      width:100%; padding:10px 12px; margin-bottom:16px; border:1px solid var(--line);
      border-radius:4px; font-family:var(--font-body); font-size:12.5px; color:var(--text);
  }
  .contact-form textarea{ min-height:110px; resize:vertical; }
  .form-msg{ font-size:12px; margin-bottom:16px; }
  .form-msg.sent{ color:#1fae8e; }
  .form-msg.error{ color:var(--accent); }
</style>
</head>
<body>
<div class="page-shell">

    <?php include 'header.php'; ?>

    <main class="wrap">
        <h1 class="page-title">Contact</h1>
        <p class="detail-intro">Have a project in mind, or just want to say hi? Send a message below.</p>

        <?php if ($status === 'sent'): ?>
            <p class="form-msg sent">Thanks — your message has been sent. I'll get back to you soon.</p>
        <?php elseif ($status === 'error'): ?>
            <p class="form-msg error">Please fill in your name, email and message.</p>
        <?php endif; ?>

        <form class="contact-form" method="post" action="contact.php">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

            <label for="message">Message</label>
            <textarea id="message" name="message"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>

            <button type="submit" class="btn" style="border:none;cursor:pointer;">Send message</button>
        </form>
    </main>

    <?php include 'footer.php'; ?>

</div>
</body>
</html>