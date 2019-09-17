<?php

namespace common\components;

use Yii;
use common\components\BaseComponent;
use yii\mail\MailerInterface;
use yii\helpers\ArrayHelper;

/**
 * Class emailAlertComponent
 * Компонент для создания уведомлений по email (алертов)
 * @package common\components
 */
class emailAlertComponent extends BaseComponent
{
    /**
     * Реализация MailerInterface
     * @var MailerInterface
     */
    protected $mailer;
    /**
     * Email адреса получателей
     * @var array
     */
    protected $emailsTo;
    /**
     * Email адреса отправителей
     * @var array
     */
    protected $emailsFrom;
    /**
     * Тэг (группа) представлений
     * @var string
     */
    protected $tag;
    /**
     * Имя представления
     * @var string
     */
    protected $viewName;
    /**
     * Данные представления
     * @var array
     */
    protected $viewData;
    /**
     * emailAlertComponent constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * Отправляет email, используя компонент mailer или иную реализацию MailerInterface,
     * если передан параметр $mailer
     * @param array $params
     * @param MailerInterface|null $mailer
     * @throws \Exception
     */
    public function send(Array $params, MailerInterface $mailer = null)
    {
        $this->validateParams($params);
        $this->setParams($params, $mailer);
        $this->mailer->compose(
            ['html' => $this->viewName . '-html', 'text' => $this->viewName . '-text'],
            $this->viewData
        )
            ->setFrom($this->emailsFrom)
            ->setTo($this->emailsTo)
            ->setSubject($params['subject'])
            ->send();
    }
    /**
     * Валидаия входных параметров отправки email
     * @param array $params
     * @throws \Exception
     */
    protected function validateParams(Array $params)
    {
        if(!isset($params['tag']) || empty($params['tag'])) {
            throw new \Exception('Tag is empty');
        }
        if(!isset($params['viewName']) || empty($params['viewName'])) {
            throw new \Exception('Email view name is empty');
        }
        if(!isset($params['viewData']) || empty($params['viewData'])) {
            throw new \Exception('Email view data is empty');
        }
        if(!isset($params['subject']) || empty($params['subject'])) {
            throw new \Exception('Email subject is empty');
        }
    }
    /**
     * Устанавливает параметры
     * @param array $params
     * @param MailerInterface|null $mailer
     */
    protected function setParams(Array $params, MailerInterface $mailer = null)
    {
        if(!isset($mailer) && empty($this->mailer)) {
            $this->mailer = Yii::$app->mailer;
        }
        $this->tag = $params['tag'];
        $this->viewName = "alerts/{$this->tag}/{$params['viewName']}";
        $this->viewData = $params['viewData'];
        if(isset($params['emailsFrom'])) {
            //$this->emailsFrom = ArrayHelper::merge($this->emailsFrom, $params['emailsFrom']);
            $this->emailsFrom = $params['emailsFrom'];
        } else {
            $this->emailsFrom = Yii::$app->params['alertEmails']['from'];
        }
        if(isset($params['emailsTo'])) {
            //$this->emailsTo = ArrayHelper::merge($this->emailsTo, $params['emailsTo']);
            $this->emailsTo = $params['emailsTo'];
        } else {
            $this->emailsTo = Yii::$app->params['alertEmails']['to'];
        }
    }
}