<?php

namespace ErenMustafaOzdal\LaravelModulesBase\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App;
use Carbon\Carbon;
use ErenMustafaOzdal\LaravelModulesBase\Services\MailService;

class Handler extends ExceptionHandler
{
    /**
     * default error view
     *
     * @var string
     */
    public $defaultViewPath = '';

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        $debug = config('app.debug');
        /*==========  hata olduğunda mail gönder  ==========*/
        if ( ! $debug) {
            if ($this->shouldReport($e)) {
                $mail = new MailService();
                $mail->datas = $this->getEmailDatas($request, $e);
                $mail->queue();
            }

            // ilgili hata sayfası gösterilir
            $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 200;
            if (view()->exists($this->defaultViewPath . '.' . $code))
            {
                return response()->view($this->defaultViewPath . '.' . $code, ['code' => $code], $code);
            }
            return response()->view($this->defaultViewPath . '.default', [], $code);
        }

        return parent::render($request, $e);
    }

    /**
     * get error email datas
     *
     * @param \Illuminate\Http\Request $request
     * @param Exception $e
     * @return array
     */
    private function getEmailDatas($request, $e)
    {
        return [
            'date'          => Carbon::now()->format('d.m.Y H:i:s'),
            'rUser'         => $request->user(),
            'rSessionOld'   => $request->session()->all()['flash']['old'],
            'rSessionNew'   => $request->session()->all()['flash']['new'],
            'rAll'          => $request->all(),
            'rIp'           => $request->ip(),
            'rDecodedPath'  => $request->decodedPath(),
            'rPath'         => $request->path(),
            'rFullUrl'      => $request->fullUrl(),
            'rUrl'          => $request->url(),
            'rRoot'         => $request->root(),
            'rMethod'       => $request->method(),
            'eMessage'      => method_exists($e, 'getMessage') ? $e->getMessage() : '',
            'eCode'         => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 200,
            'eFile'         => method_exists($e, 'getFile') ? $e->getFile() : '',
            'eLine'         => method_exists($e, 'getLine') ? $e->getLine() : '',
            'ePrevious'     => is_string($e->getPrevious()) ? $e->getPrevious() : '',
            'eTrace'        => nl2br($e->getTraceAsString()),
        ];
    }
}
