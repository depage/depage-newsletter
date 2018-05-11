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
        $this->lang = $params['lang'] ?? "en";
        $this->category = $params['category'] ?? "Default";
        $this->subscribeForm = $params['subscribeForm'] ?? Forms\Subscribe("newsletterSubscribe");
        $this->unsubscribeForm = $params['unsubscribeForm'] ?? Forms\Unsubscribe("newsletterUnsubscribe");
    }
    // }}}
    // {{{ factory()
    /**
     * @brief factory
     *
     * @param mixed $provider, $params
     * @return void
     **/
    public function factory($provider, $params)
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
        if (isset($_GET['confirm'])) {
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
            $this->subscribe(
                $values['email'],
                $values['firstname'],
                $values['lastname'],
                $values['description'],
                $values['lang'],
                $values['category']
            );
            die();
            //$form->clearSession();
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
        return $confirm;
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

    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
