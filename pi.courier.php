<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
    'pi_name'        => 'Postman',
    'pi_version'     => '0.1',
    'pi_author'      => 'Piccirilli Dorsey, Inc',
    'pi_description' => 'Send transactional email using Postmark directly from ExpressionEngine templates.'
);

// Get the composer goodies
require_once __DIR__ . '/vendor/autoload.php';

class Postman {

    /**
     * Disable / useful when you don't want to keep sending emails
     * while working on a page that fires them off.
     * @var boolean
     */
    private $disabled = false;

    /**
     * Dev Mode
     * @var boolean
     */
    private $devMode = false;

    /**
     * Data between the tag pair
     * @var string
     */
    public $return_data = "";

    /**
     * Postmark API key (put your API key here)
     * @var string
     */
    private $apiKey = 'XXXXXXXX';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Get main instance of EE
        $this->EE =& get_instance();
        // Grab the content between the tag pair
        $this->return_data = ee()->TMPL->tagdata;
    }

    /**
     * Get data from URL
     * @return string
     */
    private function getData($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * Send an email
     */
    public function send()
    {
        $disabled = (ee()->TMPL->fetch_param('disabled')) ? true : false;

        // Development mode, switch to test key
        if($this->devMode) $this->apiKey = 'POSTMARK_API_TEST';

        // Fetch plugin params
        $fromName = (ee()->TMPL->fetch_param('from_name')) ?: ee()->config->item('webmaster_name');
        $fromEmail = (ee()->TMPL->fetch_param('from_email')) ?: ee()->config->item('webmaster_email');
        $toEmail = (ee()->TMPL->fetch_param('to_email')) ?: ee()->session->userdata('email');

        $emailLayout = ee()->TMPL->fetch_param('layout');
        $emailSubject = ee()->TMPL->fetch_param('subject');
        $emailAttachment = ee()->TMPL->fetch_param('attachment');
        $emailAttachmentMime = (ee()->TMPL->fetch_param('mime')) ?: 'image/jpeg';
        $emailBody = $this->return_data;

        // Make sure we have a subject
        if(!$emailSubject)
        {
            ee()->output->fatal_error('You must specify an email subject.');
        }

        // Make sure we have some body content
        if(!$emailBody)
        {
            ee()->output->fatal_error('You need to have some content to send.');
        }

        // If we have a layout, let's use that
        if($emailLayout)
        {
            // Get the contents of our layout URL
            $emailLayoutContent = $this->getData(ee()->functions->create_url($emailLayout));
            // Inject our email content into the layout
            $emailBody = str_replace('[[content]]', $emailBody, $emailLayoutContent);
        }

        // Replace all of the occurances of {site_url} with the actual site url
        $emailBody = str_replace('{site_url}', ee()->config->item('site_url'), $emailBody);

        // Creat a new instance
        $transport = \Openbuildings\Postmark\Swift_PostmarkTransport::newInstance($this->apiKey);
        $mailer = \Swift_Mailer::newInstance($transport);
        $message = \Swift_Message::newInstance();

        // Create the message
        $message->setFrom(array($fromEmail => $fromName));
        $message->setTo($toEmail);
        $message->setSubject($emailSubject);
        if($emailAttachment)
            $message->attach(\Swift_Attachment::fromPath(NSM_BASEPATH . '/' . $emailAttachment, $emailAttachmentMime)->setFilename('card.jpg'));
        $message->setBody($emailBody, 'text/html');

        // Send the email
        $mailer->send($message);
    }
}

/* End of file pi.courier.php */