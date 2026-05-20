<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Email Helper

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper {
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('MAIL_USERNAME');
            $mail->Password   = getenv('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = getenv('MAIL_PORT') ?: 587;
            
            // Disable SSL verification for development (XAMPP Windows fix)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Default sender
            $mail->setFrom(
                getenv('MAIL_FROM_ADDRESS') ?: 'noreply@spedlms.local',
                getenv('MAIL_FROM_NAME') ?: 'SPED LMS'
            );
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer configuration failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Send role verification notification to admin
     */
    public static function sendRoleVerificationNotification($adminEmail, $userName, $requestedRole) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($adminEmail);
            $mail->Subject = 'New Role Verification Request - SPED LMS';
            $mail->isHTML(true);
            
            $mail->Body = "
                <h2>New Role Verification Request</h2>
                <p><strong>User:</strong> $userName</p>
                <p><strong>Requested Role:</strong> " . ucwords(str_replace('_', ' ', $requestedRole)) . "</p>
                <p>Please review the submitted documents and approve or reject the request.</p>
                <p><a href='" . getenv('APP_URL') . "/admin/role-requests'>View Request</a></p>
            ";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send role verification notification: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send role approval notification to user
     */
    public static function sendRoleApprovalNotification($userEmail, $userName, $approvedRole) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($userEmail);
            $mail->Subject = 'Role Verification Approved - SPED LMS';
            $mail->isHTML(true);
            
            $mail->Body = "
                <h2>Role Verification Approved</h2>
                <p>Hello $userName,</p>
                <p>Your role verification request has been <strong>approved</strong>.</p>
                <p><strong>Approved Role:</strong> " . ucwords(str_replace('_', ' ', $approvedRole)) . "</p>
                <p>You can now access your dashboard and begin using the system.</p>
                <p><a href='" . getenv('APP_URL') . "/dashboard'>Go to Dashboard</a></p>
            ";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send role approval notification: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send role rejection notification to user
     */
    public static function sendRoleRejectionNotification($userEmail, $userName, $rejectedRole, $reason) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($userEmail);
            $mail->Subject = 'Role Verification Rejected - SPED LMS';
            $mail->isHTML(true);
            
            $mail->Body = "
                <h2>Role Verification Rejected</h2>
                <p>Hello $userName,</p>
                <p>Your role verification request has been <strong>rejected</strong>.</p>
                <p><strong>Requested Role:</strong> " . ucwords(str_replace('_', ' ', $rejectedRole)) . "</p>
                <p><strong>Reason:</strong> $reason</p>
                <p>You may submit a new request with updated documentation.</p>
                <p><a href='" . getenv('APP_URL') . "/role/select'>Submit New Request</a></p>
            ";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send role rejection notification: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send IEP meeting notification
     */
    public static function sendIEPMeetingNotification($recipientEmail, $recipientName, $studentName, $meetingDate) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($recipientEmail);
            $mail->Subject = 'IEP Meeting Scheduled - SPED LMS';
            $mail->isHTML(true);
            
            $mail->Body = "
                <h2>IEP Meeting Scheduled</h2>
                <p>Hello $recipientName,</p>
                <p>An IEP meeting has been scheduled for:</p>
                <p><strong>Student:</strong> $studentName</p>
                <p><strong>Date & Time:</strong> $meetingDate</p>
                <p>Please ensure your availability for this meeting.</p>
                <p><a href='" . getenv('APP_URL') . "/iep/meetings'>View Meeting Details</a></p>
            ";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send IEP meeting notification: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send generic notification
     */
    public static function sendNotification($toEmail, $toName, $subject, $htmlBody) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send notification: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send OTP verification email
     */
    public static function sendOTPEmail($userEmail, $userName, $otp) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($userEmail, $userName);
            $mail->Subject = 'Verify Your Email - SPED LMS';
            $mail->isHTML(true);
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); padding: 20px; text-align: center;'>
                        <h1 style='color: white; margin: 0;'>SPED LMS</h1>
                    </div>
                    <div style='padding: 30px; background: #f5f5f5;'>
                        <h2 style='color: #1e4072;'>Verify Your Email Address</h2>
                        <p>Hello <strong>$userName</strong>,</p>
                        <p>Thank you for registering with SPED LMS. Please use the verification code below to complete your registration:</p>
                        
                        <div style='background: white; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; border: 2px solid #a01422;'>
                            <h1 style='color: #a01422; font-size: 36px; letter-spacing: 8px; margin: 0;'>$otp</h1>
                        </div>
                        
                        <p><strong>This code will expire in 10 minutes.</strong></p>
                        <p>If you didn't request this verification, please ignore this email.</p>
                        
                        <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                        <p style='color: #666; font-size: 12px;'>
                            This is an automated message from SPED LMS. Please do not reply to this email.
                        </p>
                    </div>
                </div>
            ";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send OTP email: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send welcome email after verification
     */
    public static function sendWelcomeEmail($userEmail, $userName) {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($userEmail, $userName);
            $mail->Subject = 'Welcome to SPED LMS!';
            $mail->isHTML(true);
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #1e4072 0%, #a01422 100%); padding: 20px; text-align: center;'>
                        <h1 style='color: white; margin: 0;'>Welcome to SPED LMS!</h1>
                    </div>
                    <div style='padding: 30px; background: #f5f5f5;'>
                        <h2 style='color: #1e4072;'>Your Account is Ready</h2>
                        <p>Hello <strong>$userName</strong>,</p>
                        <p>Welcome to the Special Education Learning Management System! Your email has been successfully verified and your account is now active.</p>
                        
                        <div style='background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #a01422;'>
                            <h3 style='color: #1e4072; margin-top: 0;'>Next Steps:</h3>
                            <ul style='color: #333;'>
                                <li>Complete your profile information</li>
                                <li>Select your role (Parent or Staff)</li>
                                <li>Explore the dashboard and available features</li>
                            </ul>
                        </div>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='" . getenv('APP_URL') . "/dashboard' style='background: #a01422; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                                Go to Dashboard
                            </a>
                        </div>
                        
                        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                        
                        <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                        <p style='color: #666; font-size: 12px;'>
                            This is an automated message from SPED LMS. Please do not reply to this email.<br>
                            For support, contact: <a href='mailto:admin@spedlms.local'>admin@spedlms.local</a>
                        </p>
                    </div>
                </div>
            ";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send welcome email: " . $mail->ErrorInfo);
            return false;
        }
    }
}
