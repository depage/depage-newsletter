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
    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed
     * @return void
     **/
    public function __construct()
    {
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
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
