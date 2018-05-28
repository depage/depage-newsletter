<?php
/**
 * @file    Pdo.php
 *
 * description
 *
 * copyright (c) 2018 Frank Hellenkamp [jonas@depage.net]
 *
 * @author    Frank Hellenkamp [jonas@depage.net]
 */

namespace Depage\Newsletter\Providers;

/**
 * @brief Pdo
 * Class Pdo
 */
class Pdo extends \Depage\Newsletter\Subscription
{
    // {{{ variables
    /**
     * @brief pdo
     **/
    protected $pdo = null;

    /**
     * @brief tableSubscrubers
     **/
    protected $tableSubscribers = "";
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
        $this->pdo = $params['pdo'];
        $this->tableSubscribers = $params['tableSubscribers'] ?? "{$this->pdo->prefix}_newsletter_subscribers";
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
        list($validation, $validatedAt, $subscribedAt) = $this->getValidationFor($email);
        if ($validation === false) {
            $validation = sha1($email . uniqid(dechex(mt_rand(256, 4095))));
        }
        if (!is_array($category)) {
            $category = [$category];
        }
        foreach ($category as $cat) {
            $this->unsubscribe($email, $lang, $cat);

            $query = $this->pdo->prepare(
                "INSERT
                INTO
                    {$this->tableSubscribers}
                SET
                    email=:email,
                    firstname=:firstname,
                    lastname=:lastname,
                    description=:description,
                    category=:category,
                    lang=:lang,
                    validation=:validation,
                    validatedAt=:validatedAt,
                    subscribedAt=:subscribedAt
                "
            );
            $success = $query->execute([
                'email' => $email,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'description' => $description,
                'lang' => $lang,
                'category' => $cat,
                'validation' => $validation,
                'validatedAt' => $validatedAt,
                'subscribedAt' => $subscribedAt,
            ]);
        }

        if ($success) {
            $this->sendConfirmationMail($email, $validation, $firstname, $lastname, $description, $lang, $category);

            return $validation;
        }

        return false;
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
        $query = $this->pdo->prepare(
            "SELECT COUNT(*) AS n FROM
                {$this->tableSubscribers}
            WHERE
                email=:email AND
                category=:category AND
                lang=:lang AND
                validation IS NULL
            "
        );
        $success = $query->execute([
            'email' => $email,
            'lang' => $lang,
            'category' => $category,
        ]);

        return $query->fetchObject()->n > 0;
    }
    // }}}
    // {{{ getValidationFor()
    /**
     * @brief getValidationFor
     *
     * @param mixed $email
     * @return void
     **/
    protected function getValidationFor($email)
    {
        $query = $this->pdo->prepare(
            "SELECT validation, validatedAt, subscribedAt FROM
                {$this->tableSubscribers}
            WHERE
                email=:email
            "
        );
        $success = $query->execute([
            'email' => $email,
        ]);

        if ($r = $query->fetchObject()) {
            return [$r->validation, $r->validatedAt, $r->subscribedAt];
        }

        return [false, null, null];
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
        $query = $this->pdo->prepare(
            "SELECT
                email,
                firstname,
                lastname,
                description,
                lang,
                category
            FROM
                {$this->tableSubscribers}
            WHERE
                validation=:validation
            "
        );
        $success = $query->execute([
            'validation' => $validation,
        ]);

        if ($subscriber = $query->fetch()) {
            $query = $this->pdo->prepare(
                "UPDATE
                    {$this->tableSubscribers}
                SET
                    validation=NULL,
                    validatedAt=NOW()
                WHERE
                    validation=:validation
                "
            );
            $success = $query->execute([
                'validation' => $validation,
            ]);

            $this->sendSubscribeNotification($subscriber['email'], $subscriber['firstname'], $subscriber['lastname'], $subscriber['description'], $subscriber['lang']);

            return $subscriber;
        }

        return $success;
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
        $deleted = 0;

        if (!is_array($category)) {
            $category = [$category];
        }
        foreach ($category as $cat) {
            $query = $this->pdo->prepare(
                "DELETE
                FROM
                    {$this->tableSubscribers}
                WHERE
                    email=:email AND
                    lang=:lang AND
                    category=:category
                "
            );
            $success = $query->execute([
                'email' => $email,
                'lang' => $lang,
                'category' => $cat,
            ]);

            $deleted += $query->rowCount();
        }

        if ($deleted > 0 && $success) {
            $this->sendUnsubscribeNotification($email, $lang);
        }

        return $deleted > 0;
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
        parent::updateSchema($pdo);

        $schema = new \Depage\DB\Schema($pdo);

        $schema->setReplace(
            function ($tableName) use ($pdo) {
                return $pdo->prefix . $tableName;
            }
        );
        $schema->loadGlob(__DIR__ . "/../Sql/*.sql");
        $schema->update();
    }
    // }}}
}

// vim:set ft=php sw=4 sts=4 fdm=marker et :
