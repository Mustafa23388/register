<?php
include('includes/db.php');
$success = false; // default
$receipt_id = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch form data safely for registration
    if (isset($_POST['full_name'])) {
        $name = $_POST['full_name'] ?? '';
        $father_name = $_POST['father_name'] ?? '';
        $marital_status = $_POST['marital_status'] ?? '';
        $year_of_passing = $_POST['year_of_passing'] ?? '';
        $profession = $_POST['profession'] ?? '';
        $contact_number = $_POST['contact_number'] ?? '';
        $email = $_POST['email'] ?? '';

        // Generate a unique receipt ID only during registration
        $receipt_id = 'RCPT' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Insert data into database
        $insert = mysqli_query($conn, "INSERT INTO students (name, father_name, marital_status, year_of_passing_matric, profession, contact_number, email, receipt_id) 
        VALUES ('$name', '$father_name', '$marital_status', '$year_of_passing', '$profession', '$contact_number', '$email', '$receipt_id')");

        if ($insert) {
            // Redirect after successful insert to prevent re-showing on refresh
            $receipt_id = urlencode($receipt_id);
            header("Location: index.php?success=1&receipt_id=$receipt_id");
            exit();
        } else {
            header("Location: index.php?error=1");
            exit();
        }
    }

    // Recover receipt ID process (no new receipt_id generated)
    session_start(); // (already at top ideally)
    if (isset($_POST['recover_submit'])) {
        $recover_email = $_POST['recover_email'];
        $check = mysqli_query($conn, "SELECT receipt_id FROM students WHERE email='$recover_email'");

        if (mysqli_num_rows($check) > 0) {
            $row = mysqli_fetch_assoc($check);
            $_SESSION['recover_message'] = "Your Receipt ID is: " . htmlspecialchars($row['receipt_id']);
        } else {
            $_SESSION['recover_message'] = "No record found for this email!";
        }

        header("Location: index.php");
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The City Foundation School - 25th Silver Jubilee Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #87CEEB 0%, #6DB9E8 50%, #87CEEB 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            top: -10px;
            z-index: 1;
            animation: fall linear infinite;
        }

        @keyframes fall {
            to {
                transform: translateY(100vh) rotate(360deg);
            }
        }

        .balloon {
            position: fixed;
            width: 50px;
            height: 60px;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            animation: float 6s ease-in-out infinite;
            z-index: 1;
        }

        .balloon::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            width: 2px;
            height: 20px;
            background: rgba(255, 255, 255, 0.5);
            transform: translateX(-50%);
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(-5deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }


        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 600px;
        }

        .badge {
            text-align: center;
            margin-bottom: 30px;
        }

        .badge-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #1e5f7a, #2a7a9e);
            border-radius: 50%;
            border: 8px solid #f4c430;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3),
                inset 0 5px 15px rgba(255, 255, 255, 0.2);
            position: relative;
            margin: 0 auto 15px;
        }

        .badge-number {
            font-size: 80px;
            font-weight: bold;
            color: #f4c430;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.4);
            font-family: 'Arial Black', sans-serif;
            line-height: 1;
        }

        .badge-ribbon {
            background: linear-gradient(135deg, #2a7a9e, #1e5f7a);
            color: white;
            padding: 8px 40px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            position: relative;
            display: inline-block;
        }

        .badge-ribbon::before,
        .badge-ribbon::after {
            content: '';
            position: absolute;
            top: 0;
            width: 0;
            height: 0;
            border-style: solid;
        }

        .badge-ribbon::before {
            left: -15px;
            border-width: 17px 15px 17px 0;
            border-color: transparent #1e5f7a transparent transparent;
        }

        .badge-ribbon::after {
            right: -15px;
            border-width: 17px 0 17px 15px;
            border-color: transparent transparent transparent #1e5f7a;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 25px;
            padding: 35px 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .form-title {
            text-align: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-subtitle {
            text-align: center;
            color: white;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-group {
            margin-bottom: 15px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.9);
            color: #555;
            outline: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        input::placeholder {
            color: #888;
        }

        input:focus,
        select:focus {
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(33, 150, 243, 0.5);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .footer {
            text-align: center;
            color: rgba(0, 0, 0, 0.7);
            font-size: 12px;
            margin-top: 20px;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }

        .success {
            background: rgba(76, 175, 80, 0.2);
            border: 2px solid #4CAF50;
            color: #2e7d32;
        }

        .error {
            background: rgba(244, 67, 54, 0.2);
            border: 2px solid #f44336;
            color: #c62828;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }

            .badge-circle {
                width: 110px;
                height: 110px;
                border: 6px solid #f4c430;
            }

            .badge-number {
                font-size: 60px;
            }

            .badge-ribbon {
                font-size: 14px;
                padding: 6px 30px;
            }

            .badge-ribbon::before {
                left: -12px;
                border-width: 14px 12px 14px 0;
            }

            .badge-ribbon::after {
                right: -12px;
                border-width: 14px 0 14px 12px;
            }

            .form-title {
                font-size: 20px;
            }

            .form-subtitle {
                font-size: 16px;
            }

            .form-card {
                padding: 25px 20px;
            }

            input[type="text"],
            input[type="email"],
            input[type="tel"],
            select {
                padding: 12px 16px;
                font-size: 14px;
            }

            .submit-btn {
                padding: 14px;
                font-size: 16px;
            }
        }

        /* Forgot Receipt ID Button */
        .forgot-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
        }

        .forgot-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(33, 150, 243, 0.4);
        }

        .forgot-btn:active {
            transform: translateY(-1px);
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            padding-top: 60px;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 15px;
            margin: auto;
            padding: 25px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Modal Submit Button (Send Receipt ID) */
        .modal-content button[type="submit"] {
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            font-size: 16px;
            font-weight: bold;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 15px;
        }

        .modal-content button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(33, 150, 243, 0.5);
            background: linear-gradient(135deg, #42A5F5, #1976D2);
        }

        .modal-content button[type="submit"]:active {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
        }

        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: white;
            text-shadow: 1px 1px 2px rgba
        }

        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;

        }

        .close:hover {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="badge">
            <div class="badge-circle">
                <div class="badge-number">25</div>
            </div>
            <div class="badge-ribbon">SILVER JUBILEE</div>
        </div>

        <div class="form-card">
            <h1 class="form-title">The City Foundation School</h1>
            <h2 class="form-subtitle">25th Silver Jubilee Anniversary</h2>

            <?php if (isset($successMessage)): ?>
                <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['recover_message'])): ?>
                <div class="message success"><?php echo $_SESSION['recover_message'];
                                                unset($_SESSION['recover_message']); ?></div>
            <?php endif; ?>


            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="Full Name"
                        pattern="^[A-Za-z\s]{3,50}$"
                        title="Only alphabets allowed (3–50 characters)" required>
                </div>

                <div class="form-group">
                    <input type="text" name="father_name" placeholder="Father's Name"
                        pattern="^[A-Za-z\s]{3,50}$"
                        title="Only alphabets allowed (3–50 characters)" required>
                </div>

                <div class="form-group">
                    <select name="marital_status" required>
                        <option value="" disabled selected>Marital Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="text" name="year_of_passing" placeholder="Year of Passing Matric"
                        pattern="^(19|20)\d{2}$"
                        title="Enter a valid year (e.g., 2015, 2020)" required>
                </div>

                <div class="form-group">
                    <input type="text" name="profession" placeholder="Profession"
                        pattern="^[A-Za-z\s]{2,40}$"
                        title="Only alphabets allowed (2–40 characters)" required>
                </div>

                <div class="form-group">
                    <input type="tel" name="contact_number" placeholder="Contact Number"
                        pattern="^03[0-9]{9}$"
                        title="Enter a valid Pakistani number (e.g., 03123456789)" required>
                </div>

                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address"
                        pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                        title="Enter a valid email address" required>
                </div>

                <button type="submit" class="submit-btn">Register</button>
            </form>

            <!-- Forgot Receipt ID Button -->
            <div class="forgot-container" style="text-align:center; margin-top:20px;">
                <button id="forgotBtn" class="forgot-btn">Forgot Receipt ID?</button>
            </div>
            <div id="forgotModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closeForgot">&times;</span>
                    <h2>Recover Receipt ID</h2>
                    <form method="POST" action="">
                        <input type="email" name="recover_email" placeholder="Enter your email" required>
                        <button type="submit" name="recover_submit">Send Receipt ID</button>
                    </form>
                </div>
            </div>


            <div class="footer">
                &copy; 2025 The City Foundation School - All Rights Reserved
            </div>
        </div>
    </div>

    <script>
        function createConfetti() {
            const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2'];
            const confettiCount = 50;

            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDuration = (Math.random() * 3 + 4) + 's';
                confetti.style.animationDelay = Math.random() * 5 + 's';
                confetti.style.width = (Math.random() * 10 + 5) + 'px';
                confetti.style.height = (Math.random() * 10 + 5) + 'px';
                confetti.style.opacity = Math.random() * 0.5 + 0.5;

                const shapes = ['square', 'circle', 'rectangle'];
                const shape = shapes[Math.floor(Math.random() * shapes.length)];

                if (shape === 'circle') {
                    confetti.style.borderRadius = '50%';
                } else if (shape === 'rectangle') {
                    confetti.style.width = (Math.random() * 15 + 5) + 'px';
                    confetti.style.height = (Math.random() * 5 + 3) + 'px';
                }

                document.body.appendChild(confetti);
            }
        }

        function createBalloons() {
            const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#F7DC6F'];
            const positions = [{
                    left: '50px',
                    bottom: '50px'
                },
                {
                    left: '100px',
                    bottom: '150px'
                },
                {
                    right: '50px',
                    bottom: '50px'
                },
                {
                    right: '100px',
                    bottom: '200px'
                },
                {
                    right: '150px',
                    bottom: '100px'
                },
                {
                    left: '150px',
                    bottom: '250px'
                }
            ];

            positions.forEach((pos, index) => {
                const balloon = document.createElement('div');
                balloon.className = 'balloon';
                balloon.style.backgroundColor = colors[index % colors.length];
                Object.assign(balloon.style, pos);
                balloon.style.animationDelay = (index * 0.5) + 's';
                document.body.appendChild(balloon);
            });
        }

        createConfetti();
        createBalloons();
    </script>
</body>
<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === Popup 1: Registration Success ===
            const receiptId = "<?php echo htmlspecialchars($_GET['receipt_id']); ?>";

            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100vw';
            overlay.style.height = '100vh';
            overlay.style.background = 'rgba(0,0,0,0.6)';
            overlay.style.display = 'flex';
            overlay.style.justifyContent = 'center';
            overlay.style.alignItems = 'center';
            overlay.style.zIndex = '9999';

            const box = document.createElement('div');
            box.style.background = 'white';
            box.style.padding = '40px 60px';
            box.style.borderRadius = '15px';
            box.style.textAlign = 'center';
            box.style.boxShadow = '0 10px 40px rgba(0,0,0,0.3)';
            box.style.fontFamily = 'Segoe UI, sans-serif';
            box.style.color = '#2e7d32';
            box.innerHTML = `
        <h2 style="font-size:28px;margin-bottom:10px;">Registration Successful! 🎉</h2>
        <p style="font-size:18px;margin-bottom:15px;">Your Receipt ID:</p>
        <div style="font-size:24px;font-weight:bold;color:#1e5f7a;">${receiptId}</div>
        <div style="margin:15px 0;padding:10px;background:rgba(244,196,48,0.2);border:2px solid #f4c430;border-radius:8px;color:#b71c1c;font-size:16px;font-weight:bold;display:flex;align-items:center;justify-content:center;">
            <svg style="width:24px;height:24px;margin-right:8px;fill:#f4c430;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            Warning! Take a screenshot of this Receipt ID to get a ticket.
        </div>
        <p style="margin-top:15px;font-size:16px;color:#444;">Thank you for registering for our 25th Silver Jubilee.</p>
        <button id='okBtn' style="margin-top:25px;padding:10px 25px;background:#2196F3;color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">OK</button>
    `;

            overlay.appendChild(box);
            document.body.appendChild(overlay);

            // 🎊 Confetti animation
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.position = 'fixed';
                confetti.style.top = '-10px';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.width = (Math.random() * 8 + 4) + 'px';
                confetti.style.height = confetti.style.width;
                confetti.style.backgroundColor = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#F7DC6F', '#BB8FCE'][Math.floor(Math.random() * 5)];
                confetti.style.opacity = Math.random() * 0.6 + 0.4;
                confetti.style.animation = `fall ${(Math.random()*3+4).toFixed(1)}s linear ${Math.random().toFixed(1)}s infinite`;
                document.body.appendChild(confetti);
            }

            const style = document.createElement('style');
            style.innerHTML = `
        @keyframes fall {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(100vh) rotate(360deg); }
        }
    `;
            document.head.appendChild(style);

            // === Second Popup: Payment Instructions ===
            const showPaymentPopup = () => {
                const overlay2 = document.createElement('div');
                overlay2.style.position = 'fixed';
                overlay2.style.top = '0';
                overlay2.style.left = '0';
                overlay2.style.width = '100vw';
                overlay2.style.height = '100vh';
                overlay2.style.background = 'rgba(0,0,0,0.6)';
                overlay2.style.display = 'flex';
                overlay2.style.justifyContent = 'center';
                overlay2.style.alignItems = 'center';
                overlay2.style.zIndex = '9999';

                const box2 = document.createElement('div');
                box2.style.background = 'white';
                box2.style.padding = '40px 50px';
                box2.style.borderRadius = '15px';
                box2.style.textAlign = 'left';
                box2.style.boxShadow = '0 10px 40px rgba(0,0,0,0.3)';
                box2.style.fontFamily = 'Segoe UI, sans-serif';
                box2.style.maxWidth = '500px';
                box2.style.color = '#333';
                box2.innerHTML = `
            <h2 style="font-size:24px;text-align:center;color:#1976D2;">Payment Instructions 💳</h2>
            <p style="margin-top:20px;font-size:16px;line-height:1.6;">
                You have now been registered! 🎉<br><br>
                To buy your <strong>Silver Jubilee Event Ticket</strong>, please send 
                <strong>Rs. 500</strong> to the JazzCash account below:
            </p>
            <p style="font-size:18px;color:#2E7D32;font-weight:bold;text-align:center;">JazzCash: 0300-1234567</p>
            <p style="margin-top:15px;font-size:16px;line-height:1.6;">
                After payment, send a <strong>screenshot</strong> of your transaction along with your 
                <strong>Receipt ID (${receiptId})</strong> to our 
                <a href="https://wa.me/03001234567" target="_blank" style="color:#1976D2;text-decoration:none;font-weight:bold;">WhatsApp number</a>.
            </p>
            <p style="margin-top:15px;font-size:16px;">
                📍 Alternatively, you can collect your ticket directly from <strong>The City Foundation School</strong> 
                by showing your <strong>Receipt ID</strong> and <strong>payment screenshot</strong>.
            </p>
            <div style="text-align:center;margin-top:25px;">
                <button id='okBtn2' style="padding:10px 25px;background:#2196F3;color:white;border:none;border-radius:8px;font-size:16px;cursor:pointer;">Got It</button>
            </div>
        `;

                overlay2.appendChild(box2);
                document.body.appendChild(overlay2);

                document.getElementById('okBtn2').onclick = function() {
                    document.body.removeChild(overlay2);
                    window.history.replaceState(null, '', 'index.php'); // Clean URL again
                };
            };

            // When OK is clicked on first popup
            document.getElementById('okBtn').onclick = function() {
                document.body.removeChild(overlay);
                showPaymentPopup();
            };


        });
    </script>
<?php endif; ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const forgotBtn = document.getElementById("forgotBtn");
        const forgotModal = document.getElementById("forgotModal");
        const closeForgot = document.getElementById("closeForgot");

        if (!forgotBtn || !forgotModal || !closeForgot) return;

        forgotBtn.addEventListener("click", () => {
            forgotModal.style.display = "block";
        });

        closeForgot.addEventListener("click", () => {
            forgotModal.style.display = "none";
        });

        window.addEventListener("click", (event) => {
            if (event.target === forgotModal) {
                forgotModal.style.display = "none";
            }
        });
    });
</script>
<script>
document.querySelector("form").addEventListener("submit", function(e) {
  const inputs = this.querySelectorAll("input[pattern]");
  for (let input of inputs) {
    const regex = new RegExp(input.pattern);
    if (!regex.test(input.value)) {
      e.preventDefault();
      alert(input.title);
      input.focus();
      return false;
    }
  }
});
</script>


</html>


</html>