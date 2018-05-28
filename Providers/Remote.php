<?php
/**
 * @file    Remote.php
 *
 * description
 *
 * copyright (c) 2018 Frank Hellenkamp [jonas@depage.net]
 *
 * @author    Frank Hellenkamp [jonas@depage.net]
 */

namespace Depage\Newsletter\Providers;

/**
 * @brief Remote
 * Class Remote
 */
class Remote extends \Depage\Newsletter\Subscription
{
    // {{{ variables
    /**
     * @brief apiUrl
     **/
    protected $apiUrl = "";
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
        parent::__construct($params);

        // @todo check parameters
        $this->apiUrl = $params['apiUrl'];
    }
    // }}}
    // {{{ subscribe()
    /**
     * @brief subscribe
     *
     * @param mixed $param
     * @return void
     **/
    public function subscribe($email, $firstname = "", $lastname = "", $description = "", $lang = "en", $category = "Default")
    {
        $url = $this->apiUrl . "newsletter/subscribe/";

        if (!is_array($category)) {
            $category = [$category];
        }
        foreach ($category as $cat) {
            $values = [
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'description' => $description,
                'lang' => $lang,
                'category' => $cat,
            ];

            $request = new \Depage\Http\Request($url);
            $response = $request
                ->setJson($values)
                ->execute();
        }

        $result = $response->getJson();

        if (!is_null($result['validation'])) {
            $this->sendConfirmationMail($email, $result['validation'], $firstname, $lastname, $description, $lang, $category);
        }

        return $response->getJson()['validation'];
    }
    // }}}
    // {{{ isSubscriber()
    /**
     * @brief isSubscriber
     *
     * @param mixed $param
     * @return void
     **/
    public function isSubscriber($email, $lang = "en", $category = "Default")
    {
        $url = $this->apiUrl . "newsletter/is-subscriber/";

        $values = [
            'email' => $email,
            'lang' => $lang,
            'category' => $category,
        ];

        $request = new \Depage\Http\Request($url);
        $response = $request
            ->setJson($values)
            ->execute();

        return $response->getJson()['success'];
    }
    // }}}
    // {{{ confirm()
    /**
     * @brief confirm
     *
     * @param mixed $validation
     * @return void
     **/
    public function confirm($validation)
    {
        $url = $this->apiUrl . "newsletter/confirm/";

        $values = [
            'validation' => $validation,
        ];

        $request = new \Depage\Http\Request($url);
        $response = $request
            ->setJson($values)
            ->execute();

        $subscriber = $response->getJson()['subscriber'];

        if ($subscriber) {
            $this->sendSubscribeNotification($subscriber['email'], $subscriber['firstname'], $subscriber['lastname'], $subscriber['description'], $subscriber['lang']);
        }

        return $response->getJson()['success'];
    }
    // }}}
    // {{{ unsubscribe()
    /**
     * @brief
     *
     * @param mixed
     * @return void
     **/
    public function unsubscribe($email, $lang = "en", $category = "Default")
    {
        $url = $this->apiUrl . "newsletter/unsubscribe/";

        if (!is_array($category)) {
            $category = [$category];
        }
        foreach ($category as $cat) {
            $values = [
                'email' => $email,
                'lang' => $lang,
                'category' => $cat,
            ];

            $request = new \Depage\Http\Request($url);
            $response = $request
                ->setJson($values)
                ->execute();

            $success = $response->getJson()['success'];

            if ($success) {
                $this->sendUnsubscribeNotification($email, $lang);
            }
        }

        return $success;
    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
