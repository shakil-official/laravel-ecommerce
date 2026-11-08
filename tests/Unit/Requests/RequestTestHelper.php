<?php

namespace Tests\Unit\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidatorContract;

/**
 * Builds a FormRequest the way the router would (method + bound route model)
 * so that rules() can be unit tested without an HTTP round trip.
 */
trait RequestTestHelper
{
    /**
     * @param  class-string<FormRequest>  $class
     */
    protected function validate(string $class, array $data, string $method = 'POST', array $routeParams = []): ValidatorContract
    {
        /** @var FormRequest $request */
        $request = $class::create('/test', $method, $data);

        if ($routeParams !== []) {
            $route = new Route([$method], '/test/{'.array_key_first($routeParams).'}', fn () => null);
            $route->bind($request);
            foreach ($routeParams as $key => $value) {
                $route->setParameter($key, $value);
            }
            $request->setRouteResolver(fn () => $route);
        }

        $request->setContainer(app());

        // Run prepareForValidation (trim) through reflection, like the framework does.
        $method = new \ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $validator = Validator::make($request->all(), $request->rules(), $request->messages(), $request->attributes());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        return $validator;
    }
}
