<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class Mailer {
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP host
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@gmail.com'; // Replace with your email
            $mail->Password   = 'your-app-password'; // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom('your-email@gmail.com', 'MuscleZen');
            
            return $mail;
        } catch (Exception $e) {
            throw new Exception("Mail configuration error: {$mail->ErrorInfo}");
        }
    }

    public static function sendPasswordReset($email, $username, $resetLink) {
        try {
            $mail = self::getMailer();
            
            // Recipients
            $mail->addAddress($email, $username);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - MuscleZen';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center;'>
                        <img src='https://your-domain.com/images/Fitness logo V2.png' alt='MuscleZen Logo' style='width: 150px;'>
                    </div>
                    
                    <div style='padding: 20px; background-color: white;'>
                        <h2 style='color: #333;'>Hello {$username},</h2>
                        
                        <p>We received a request to reset your password for your MuscleZen account.</p>
                        
                        <p>Click the button below to reset your password:</p>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$resetLink}' 
                               style='background-color: #007bff; 
                                      color: white; 
                                      padding: 12px 25px; 
                                      text-decoration: none; 
                                      border-radius: 5px; 
                                      display: inline-block;'>
                                Reset Password
                            </a>
                        </div>
                        
                        <p>If the button doesn't work, copy and paste this link into your browser:</p>
                        <p style='background-color: #f8f9fa; padding: 10px; word-break: break-all;'>
                            {$resetLink}
                        </p>
                        
                        <p>This link will expire in 1 hour for security reasons.</p>
                        
                        <p>If you didn't request this password reset, you can safely ignore this email.</p>
                        
                        <hr style='margin: 30px 0; border-top: 1px solid #eee;'>
                        
                        <p style='color: #666; font-size: 14px;'>
                            Best regards,<br>
                            The MuscleZen Team
                        </p>
                    </div>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666;'>
                        <p>This is an automated message, please do not reply to this email.</p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: {$e->getMessage()}");
            throw new Exception("Failed to send reset email");
        }
    }
}
?>
