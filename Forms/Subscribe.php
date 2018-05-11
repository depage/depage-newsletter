<?php

namespace Depage\Newsletter\Forms;

/**
 * brief Newsletter
 * Class Newsletter
 */
class Subscribe extends \Depage\HtmlForm\HtmlForm
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
        $parameters['label'] = _("Subscribe Now");

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
        $this->addHtml("<p>" . _("Subscribe to our newsletter using the following form:") . "</p>");

        $this->addEmail("email", [
            'label' => _("Email"),
            'required' => true,
        ]);
        $this->addText("firstname", [
            'label' => _("First Name"),
        ]);
        $this->addText("lastname", [
            'label' => _("Surname"),
        ]);
        $this->addText("description", [
            'label' => _("Name of Company"),
        ]);

        $this->addHtml("<p>" . _("* Required fields") . "</p>");
    }
    // }}}
}

/* vim:set ft=php sw=4 sts=4 fdm=marker et : */
