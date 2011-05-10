<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HttpKernelPipApplication
{
    private $kernel;

    public function __construct(HttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function __invoke($env)
    {
        $vars = array();
        if (isset($env['CONTENT_TYPE']) && 'application/x-www-form-urlencoded' == $env['CONTENT_TYPE'])
        {
            rewind($this->body);
            parse_str(stream_get_contents($this->body), $vars);
        }

        $request = Request::create($env['PATH_INFO'], $env['REQUEST_METHOD'], $vars, array(), array(), $env, $env['pip.input']);
        $response = $this->kernel->handle($request);

        $headers = array();
        foreach ($response->headers->all() as $name => $value) {
            $headers[strtolower($name)] = $value;
        }
        $headers['content-length'] = strlen($response->getContent());

        $body = fopen('php://temp', 'w+');
        fwrite($body, $response->getContent());
        return array(
            $response->getStatusCode(),
            $headers,
            $body
         );
    }
}
