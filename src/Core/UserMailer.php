<?php

namespace WebFramework\Core;

/**
 * Class UserMailer.
 *
 * Handles sending various types of emails to users.
 */
class UserMailer
{
    /**
     * UserMailer constructor.
     *
     * @param MailService           $mailService       The mail service for sending emails
     * @param string                $senderEmail       The default sender email address
     * @param array<string, string> $templateOverrides Template overrides for different email types
     */
    public function __construct(
        private MailService $mailService,
        private string $senderEmail,
        private array $templateOverrides,
    ) {}

    /**
     * Send an email verification link to a user.
     *
     * @param string       $to     The recipient's email address
     * @param array<mixed> $params Parameters for the email template
     *
     * @return bool|string True if sent successfully, or an error message string
     */
    public function emailVerificationLink(string $to, array $params): bool|string
    {
        $templateId = $this->templateOverrides['email-verification-link'] ?? 'email-verification-link';
        $verifyUrl = $params['verify_url'];
        $username = $params['user']['username'];

        $vars = [
            'action_url' => $verifyUrl,
            'username' => $username,
        ];

        return $this->mailService->sendTemplateMail($templateId, $this->senderEmail, $to, $vars);
    }

    /**
     * Send a change email verification link to a user.
     *
     * @param string       $to     The recipient's email address
     * @param array<mixed> $params Parameters for the email template
     *
     * @return bool|string True if sent successfully, or an error message string
     */
    public function changeEmailVerificationLink(string $to, array $params): bool|string
    {
        $templateId = $this->templateOverrides['change-email-verification-link'] ?? 'change-email-verification-link';
        $verifyUrl = $params['verify_url'];
        $username = $params['user']['username'];

        $vars = [
            'action_url' => $verifyUrl,
            'username' => $username,
        ];

        return $this->mailService->sendTemplateMail($templateId, $this->senderEmail, $to, $vars);
    }

    /**
     * Send a password reset email to a user.
     *
     * @param string       $to     The recipient's email address
     * @param array<mixed> $params Parameters for the email template
     *
     * @return bool|string True if sent successfully, or an error message string
     */
    public function passwordReset(string $to, array $params): bool|string
    {
        $templateId = $this->templateOverrides['password-reset'] ?? 'password-reset';
        $resetUrl = $params['reset_url'];
        $username = $params['user']['username'];

        $vars = [
            'action_url' => $resetUrl,
            'username' => $username,
        ];

        return $this->mailService->sendTemplateMail($templateId, $this->senderEmail, $to, $vars);
    }

    /**
     * Send a new password email to a user.
     *
     * @param string       $to     The recipient's email address
     * @param array<mixed> $params Parameters for the email template
     *
     * @return bool|string True if sent successfully, or an error message string
     */
    public function newPassword(string $to, array $params): bool|string
    {
        $templateId = $this->templateOverrides['new-password'] ?? 'new-password';
        $username = $params['user']['username'];

        $vars = [
            'password' => $params['password'],
            'username' => $username,
        ];

        return $this->mailService->sendTemplateMail($templateId, $this->senderEmail, $to, $vars);
    }
}
