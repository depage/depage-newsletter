<?php

namespace Depage\Newsletter\Forms;

/**
 * brief Newsletter
 * Class Newsletter
 */
class Unsubscribe extends \Depage\HtmlForm\HtmlForm
{
    // {{{ __construct()
    /**
     * @brief __construct
     *
     * @param mixed $param
     * @return void
     **/
    public function __construct($name, $parameters = array())
    {
        $parameters['label'] = _("Unsubscribe Now");

        parent::__construct($name, $parameters);
    }
    // }}}
    // {{{ addChildElements()
    /**
     * @brief addChildElements
     *
     * @return void
     **/
    public function addChildElements()
    {
        $this->addHtml("<p>" . _("Please fill in your email to unsubscribe from out newsletter:") . "</p>");

        $this->addEmail("email", [
            'label' => _("Email"),
            'defaultValue' => $_GET['unsubscribe'],
            'required' => true,
        ]);

        $this->addHtml("<p>" . _("* Required fields") . "</p>");
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
