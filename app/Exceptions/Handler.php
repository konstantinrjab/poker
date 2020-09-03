<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        GameException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        if (!in_array(env('APP_ENV'), ['local', 'testing']) && $this->shouldReport($exception)) {
            $url = '-';
            if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
                $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }

            $data = [
                'data' => [
                    'Message' => $exception->getMessage(),
                    'File' => $exception->getFile(),
                    'Line' => $exception->getLine(),
                    'Code' => $exception->getCode(),
                    'Previous' => $exception->getPrevious(),
                    'DateTime' => date('Y-m-d H:i:s'),
                    'Url' => $url,
                    'Trace' => $exception->getTraceAsString(),
                ]
            ];

            $emails = explode(',', env('EXCEPTION_EMAILS'));

            foreach ($emails as $email) {
                Mail::send('emails.exception', $data, function (Message $message) use ($email) {
                    $message->to($email, env('MAIL_FROM_ADDRESS'))->subject('Exception on ' . env('APP_NAME'));
                });
            }
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($request->wantsJson() || $request->is('api/*')) {
            $response['data'] = [
                'message' => $exception->getMessage()
            ];
            if ($exception instanceof ValidationException && isset($exception->validator)) {
                $response['data']['errors'] = $exception->validator->errors()->toArray();
            }
            if ($exception instanceof ModelNotFoundException) {
                $response['data']['message'] = 'Model not found';
            }

            if (config('app.debug')) {
                $response['exception'] = get_class($exception);
                $response['message'] = $exception->getMessage();
                $response['trace'] = $exception->getTrace();
            }

            $status = 400;

            if ($this->isHttpException($exception)) {
                /** @var HttpExceptionInterface $exception */
                $status = $exception->getStatusCode();
            }

            return response()->json($response, $status);
        }

        return parent::render($request, $exception);
    }
}
