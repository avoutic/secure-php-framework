<?php
require_once($includes.'base_logic.inc.php');

class PageChangeEmailVerify extends PageBasic
{
    static function get_filter()
    {
        return array(
                'code' => '.*',
                );
    }

    static function get_permissions()
    {
        return array(
                'logged_in'
                );
    }

    function get_title()
    {
        return "Change email address verification";
    }

    function do_logic()
    {
        framework_add_bad_ip_hit();

        // Check if code is present
        //
        $code = $this->get_input_var('code');
        if (!strlen($code))
        {
            framework_add_bad_ip_hit(2);
            exit();
        }

        $str = decode_and_verify_string($code);
        if (!strlen($str))
        {
            framework_add_bad_ip_hit(4);
            exit();
        }

        $msg = json_decode($str, true);
        if (!is_array($msg))
            exit();

        if ($msg['action'] != 'change_email')
        {
            framework_add_bad_ip_hit(4);
            exit();
        }

        if ($msg['timestamp'] + 600 < time())
        {
            // Expired
            header("Location: /change-email?".add_message_to_url('error', 'E-mail verification link expired'));
            exit();
        }

        $user_id = $msg['id'];
        $email = $msg['params']['email'];
        $this->page_content['email'] = $email;

        // Only allow for current user
        //
        if ($user_id != $this->state['user_id'])
        {
            $this->auth->deauthenticate();
            header('Location: /?'.add_message_to_url('error', 'Other account', 'The link you used is meant for a different account. The current account has been  logged off. Please try the link again.'));
            exit();
        }

        // Change email
        //
        $factory = new BaseFactory($this->global_info);
        $user = $factory->get_user($user_id);
        $old_email = $user->email;

        if (!isset($msg['params']) || !isset($msg['params']['iterator']) ||
            $user->get_security_iterator() != $msg['params']['iterator'])
        {
            header("Location: /change-email?".add_message_to_url('error', 'E-mail verification link expired'));
            exit();
        }

        $result = $user->change_email($email);

        if ($result == User::ERR_DUPLICATE_EMAIL)
        {
            $this->add_message('error', 'E-mail address is already in use in another account.', 'The e-mail address is already in use and cannot be re-used in this account. Please choose another address.');
            return;
        }
        if ($result != User::RESULT_SUCCESS)
        {
            $this->add_message('error', 'Unknown errorcode: \''.$result."'", "Please inform the administrator.");
            return;
        }

        // Invalidate old sessions
        //
        $this->auth->invalidate_sessions($user->id);
        $this->auth->set_logged_in($user);

        // Redirect to verification request screen
        //
        header('Location: /account?'.add_message_to_url('success', 'E-mail address changed successfully'));
        exit();
    }
};
?>
