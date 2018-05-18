<?php
/**
 * @file    Subscription.php
 *
 * description
 *
 * copyright (c) 2018 Frank Hellenkamp [jonas@depage.net]
 *
 * @author    Frank Hellenkamp [jonas@depage.net]
 */

namespace Depage\Newsletter;

/**
 * @brief Subscription
 * Class Subscription
 */
abstract class Subscription
{
    // {{{ variables
    /**
     * @brief lang
     **/
    protected $lang = "en";

    /**
     * @brief category
     **/
    protected $category = "Default";

    /**
     * @brief title
     **/
    protected $title = "";

    /**
     * @brief sender
     **/
    protected $sender = "";

    /**
     * @brief notify
     **/
    protected $notify = null;

    /**
     * @brief subscribeForm
     **/
    protected $subscribeForm = null;

    /**
     * @brief unsubscribeForm
     **/
    protected $unsubscribeForm = null;
    // }}}

    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed $params
     * @return void
     **/
    public function __construct($params)
    {
        // @todo check parameters
        $this->sender = $params['sender'];
        $this->notify = $params['notify'] ?? null;
        $this->lang = $params['lang'] ?? "en";
        $this->category = $params['category'] ?? "Default";
        $this->title = $params['title'] ?? "";
        $this->subscribeForm = $params['subscribeForm'] ?? new Forms\Subscribe("newsletterSubscribe");
        $this->unsubscribeForm = $params['unsubscribeForm'] ?? new Forms\Unsubscribe("newsletterUnsubscribe");
        $this->url = $params['url'] ?? (
            $_SERVER['HTTPS'] == 'on' ? "https://" : "http://" .
            $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']
        );
    }
    // }}}
    // {{{ factory()
    /**
     * @brief factory
     *
     * @param mixed $provider, $params
     * @return void
     **/
    public static function factory($provider, $params)
    {
        if ($provider == "remote" || $provider == "api") {
            return new \Depage\Newsletter\Providers\Remote($params);
        } else if ($provider == "pdo") {
            return new \Depage\Newsletter\Providers\Pdo($params);
        } else {
            return false;
        }
    }
    // }}}

    // {{{ process()
    /**
     * @brief process
     *
     * @param mixed
     * @return void
     **/
    public function process()
    {
        if (!empty($_GET['v'])) {
            return $this->processConfirmation();
        } else if (isset($_GET['unsubscribe'])) {
            return $this->processUnsubscribe();
        } else {
            return $this->processSubscribe();
        }
    }
    // }}}

    // {{{ processSubscribe()
    /**
     * @brief processSubscribe
     *
     * @param mixed
     * @return void
     **/
    protected function processSubscribe()
    {
        $form = $this->subscribeForm;
        $form->process();

        if ($form->valid) {
            $values = $form->getValues() + [
                'firstname' => "",
                'lastname' => "",
                'description' => "",
                'lang' => $this->lang,
                'category' => $this->category,
            ];
            $form->clearSession();

            if ($this->isSubscriber($values['email'], $values['lang'], $values['category'])) {
                return "<p>" . sprintf(
                    _("You're already a subscriber to our newsletter with the email '%s'."),
                    htmlentities($values['email'])
                ) . "</p>";
            }

            $this->subscribe(
                $values['email'],
                $values['firstname'],
                $values['lastname'],
                $values['description'],
                $values['lang'],
                $values['category']
            );

            return "<p>" . sprintf(
                _("Please confirm your subscription, by opening the link in the email we just send to '%s'."),
                htmlentities($values['email'])
            ) . "</p>";
        }

        return $form;
    }
    // }}}
    // {{{ processConfirmation()
    /**
     * @brief processConfirmation
     *
     * @param mixed
     * @return void
     **/
    protected function processConfirmation()
    {
        $subscriber = $this->confirm($_GET['v']);

        return "<p>" . sprintf(
            _("Thank you for subscribing our newsletter."),
            htmlentities($subscriber['email'])
        ) . "</p>";
    }
    // }}}
    // {{{ processUnsubscribe()
    /**
     * @brief processUnsubscribe
     *
     * @param mixed
     * @return void
     **/
    protected function processUnsubscribe()
    {
        $form = $this->unsubscribeForm;
        $form->process();

        if ($form->valid) {
            $values = $form->getValues();

            $this->unsubscribe($values['email'], $this->lang, $this->category);
            $form->clearSession();

            return "<p>" . sprintf(
                _("You have been unsubscribed and should no longer receive our newsletter at '%s'."),
                htmlentities($values['email'])
            ) . "</p>";
        }

        return $form;
    }
    // }}}

    // {{{ subscribe()
    /**
     * @brief
     *
     * @param mixed
     * @return void
     **/
    abstract public function subscribe($email, $firstname = "", $lastname = "", $description = "", $lang = "en", $category = "Default");
    // }}}
    // {{{ isSubscriber()
    /**
     * @brief
     *
     * @param mixed
     * @return void
     **/
    abstract public function isSubscriber($email, $lang = "en", $category = "Default");
    // }}}
    // {{{ confirm()
    /**
     * @brief confirm
     *
     * @param mixed $validation
     * @return void
     **/
    abstract public function confirm($validation);
    // }}}
    // {{{ unsubscribe()
    /**
     * @brief
     *
     * @param mixed
     * @return void
     **/
    abstract public function unsubscribe($email, $lang = "en", $category = "Default");
    // }}}

    // {{{ sendConfirmationMail()
    /**
     * @brief sendConfirmationMail
     *
     * @param mixed
     * @return void
     **/
    protected function sendConfirmationMail($email, $validation, $firstname = "", $lastname = "", $description = "", $lang = "en", $category = "Default")
    {
        $url = "$this->url?v=$validation";

        $text =
            _("Dear Subscriber,\n\nPlease open the following link to validate you registration to the newsletter:\n") .
            "{$url}\n";

        $title = $this->title;
        if (!empty($title)) {
            $title .= " . ";
        }

        $mail = new \Depage\Mail\Mail($this->sender);
        $mail
            ->setSubject($title . _("Newsletter Confirmation"))
            ->setText($text)
            ->send($email);
    }
    // }}}
    // {{{ sendSubscribeNotification()
    /**
     * @brief sendSubscribeNotification
     *
     * @param mixed
     * @return void
     **/
    protected function sendSubscribeNotification($email, $firstname = "", $lastname = "", $description = "", $lang = "en", $category = "Default")
    {
        if (empty($this->notify)) {
            return;
        }

        $text =
            _("There is a new subscriber to the newsletter:\n\n") .
            _("Email: ") . "$email\n" .
            _("First name: ") . "$firstname\n" .
            _("Last name: ") . "$lastname\n" .
            _("Description: ") . "$description\n" .
            _("Language: ") . "$lang\n" .
            _("Category: ") . "$category\n";

        $title = $this->title;
        if (!empty($title)) {
            $title .= " . ";
        }

        $mail = new \Depage\Mail\Mail($this->sender);
        $mail
            ->setSubject($title . _("New Newsletter Subscriber"))
            ->setText($text)
            ->send($this->notify);
    }
    // }}}
    // {{{ sendUnsubscribeNotification()
    /**
     * @brief sendUnsubscribeNotification
     *
     * @param mixed
     * @return void
     **/
    protected function sendUnsubscribeNotification($email, $lang = "en", $category = "Default")
    {
        if (empty($this->notify)) {
            return;
        }

        $text =
            _("A subscriber has unsubscribed the newsletter:\n\n") .
            _("Email: ") . "$email\n" .
            _("Language: ") . "$lang\n" .
            _("Category: ") . "$category\n";

        $title = $this->title;
        if (!empty($title)) {
            $title .= " . ";
        }

        $mail = new \Depage\Mail\Mail($this->sender);
        $mail
            ->setSubject($title . _("Newsletter unsubscribed"))
            ->setText($text)
            ->send($this->notify);
    }
    // }}}

    // {{{ updateSchema()
    /**
     * @brief updateSchema
     *
     * @param mixed
     * @return void
     **/
    public static function updateSchema($pdo)
    {

    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
