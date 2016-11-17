<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Services;

use Snowfire\Beautymail\Beautymail;

class MailService
{
    /**
     * from email address
     *
     * @var string
     */
    protected $fromEmail;

    /**
     * to email address
     *
     * @var string
     */
    protected $toEmail;

    /**
     * to name
     *
     * @var string
     */
    protected $toName;

    /**
     * to name
     *
     * @var string
     */
    protected $subject;

    /**
     * email datas
     *
     * @var string
     */
    public $datas;

    /**
     * view
     *
     * @var string
     */
    public $view;

    /**
     * beauty mail
     *
     * @var Beautymail
     */
    public $beauty;

    /**
     * construct method
     */
    public function __construct()
    {
        $backClass = camel_case(substr(strrchr(debug_backtrace()[1]['class'], '\\'), 1));
        $this->beauty = app()->make(Beautymail::class);
        if ($backClass === 'handler') {
            $this->{$backClass . 'Options'}();
        }
    }

    /**
     * send queue mail
     */
    public function queue()
    {
        $from = $this->fromEmail;
        $to = $this->toEmail;
        $name = $this->toName;
        $subject = $this->subject;
        $this->beauty->queue($this->view, $this->datas, function($message) use($from,$to,$name,$subject)
        {
            $message->from($from)
                ->to($to, $name)
                ->subject($subject);
        });
    }

    /**
     * send mail
     */
    public function send()
    {
        $from = $this->fromEmail;
        $to = $this->toEmail;
        $name = $this->toName;
        $subject = $this->subject;
        $this->beauty->send($this->view, $this->datas, function($message) use($from,$to,$name,$subject)
        {
            $message->from($from)
                ->to($to, $name)
                ->subject($subject);
        });
    }

    /**
     * exception handler set options
     */
    public function handlerOptions()
    {
        $this->fromEmail = config('laravel-modules-base.from_mail');
        $this->toEmail = config('laravel-modules-base.developer_mail');
        $this->toName = config('laravel-modules-base.developer_name');
        $this->subject = trans('laravel-modules-base::admin.email.error.subject');
        $this->view = 'laravel-modules-base::emails.error';
    }
}
