<?php

namespace Blockify;

use Blockify\Interfaces\errorApplication;
use Blockify\Models\Api;
use ReflectionClass;
use ReflectionException;

/**
 * @author king m a kh
 * @example https://thCode.ir
 * @version 1.0
 */
final class Blockify
{
    private bool $isPage;
    private string $requestController;
    private string $requestFunction;
    private array $jsonArguments;
    public errorApplication $errorController;
    private bool $showDocument = false;

    public function __construct(string $_url, string $_defaultController, errorApplication $_errorController)
    {
        $url = explode('/', filter_var($_url, FILTER_SANITIZE_URL));

        $this->errorController = $_errorController;
        $this->isPage = $_SERVER['REQUEST_METHOD'] == 'GET';

        $this->requestController = (($url[0] ?? '') == "") ? $_defaultController : $url[0];

        if (!$this->isPage) {
            $this->requestFunction = (($url[1] ?? "") == "") ? "onRequest" : $url[1];

            if ($this->requestFunction == "onRequest")
                $this->showDocument = true;

            $this->jsonArguments = json_decode(file_get_contents('php://input'), true) ?? [];
        }
    }

    /**
     * @throws ReflectionException
     */
    public function start(string $viewPath, string $appPath, string $namespace): string
    {
        if ($this->isPage) {
            if (!(file_exists("$viewPath$this->requestController.html") || file_exists("$viewPath$this->requestController.php")))
                return $this->errorController::viewError($this->requestController);

            return self::pageRequest($viewPath);

        } else {
            // check api exist
            if (!file_exists("$appPath$this->requestController.php")) {
                return $this->errorController::classError($this->requestController)->toString();
            } else {
                // require api class
                require_once "$appPath$this->requestController.php";

                // create instance of requestController
                $instance = new ReflectionClass("$namespace$this->requestController");

                if ($this->showDocument)
                    return self::documentRequest($instance);

                // check requestFunction function exist in requestController
                if (!$instance->hasMethod($this->requestFunction))
                    return $this->errorController::functionError($this->requestFunction)->toString();
                else {
                    $function = $instance->getMethod($this->requestFunction);

                    if (!$function->hasReturnType() || $function->getReturnType()->getName() != "Blockify\Models\Api") {
                        return "no standard type";

                    } else {

                        if (count($function->getParameters()) < count($this->jsonArguments))
                            return $this->errorController::parameterCountError(count($function->getParameters()))->toString();
                        else {
                            $notExistKeys = [];
                            $parameters = [];


                            foreach ($function->getParameters() as $parameter) {
                                if (!array_key_exists($parameter->getName(), $this->jsonArguments))
                                    $notExistKeys[] = $parameter->getName();

                                $parameters[] = $parameter->getName();
                            }

                            if (count($notExistKeys) > 0) {
                                $comment = $function->getDocComment();
                                preg_match_all('/@param[a-zA-Z\d $_]+/i', $comment, $prob);

                                $prob = $prob[0];
                                $docs = [];

                                foreach ($prob as $item)
                                    $docs[] = preg_replace('/@param +(string|int|bool|array) +\$[a-z1-9_]+ */i', '', $item);

                                $args = [];

                                foreach ($parameters as $index => $parameter) {
                                    $args[] = [
                                        "var" => $parameter,
                                        "doc" => $docs[$index] ?? ""
                                    ];
                                }
                                return $this->errorController::parameterError($args)->toString();
                            }
                        }
                    }
                }
            }
            return self::apiRequest($namespace);
        }
    }

    private static function documentRequest(ReflectionClass $class): string
    {
        $classFunctions = $class->getMethods();
        $functions = [];

        foreach ($classFunctions as $item) {
            $comments = $item->getDocComment();

            preg_match_all('/@param[a-zA-Z\d $_]+/i', $comments, $prob);

            $prob = $prob[0];
            $docs = [];

            foreach ($prob as $probItem)
                $docs[] = preg_replace('/@param +(string|int|bool|array) +\$[a-z1-9_]+ */i', '', $probItem);

            $args = [];

            foreach ($item->getParameters() as $index => $parameter)
                $args[] = [
                    "name" => $parameter->getName(),
                    "document" => $docs[$index] ?? ''
                ];

            $functions[] = [
                "name" => $item->getName(),
                "args" => $args
            ];
        }

        header('Content-Type: application/json;');

        return (new Api(false, $class->getName(), $functions))->toString();
    }

    private function pageRequest(string $viewPath): string
    {
        header("Content-Type: text/html;");
        if (file_exists("$viewPath$this->requestController.html"))
            return fread(
                fopen("$viewPath$this->requestController.html", "r"),
                filesize("$viewPath$this->requestController.html"));
        else {
            ob_start();
            require_once "$viewPath$this->requestController.php";
            return ob_get_clean();
        }
    }

    private function apiRequest(string $namespace): string
    {
        ob_start();

        $package = "$namespace$this->requestController";
        $instance = new $package();

        $result = call_user_func_array([$instance, $this->requestFunction], $this->jsonArguments);

        ob_clean();

        header('Content-Type: application/json; charset=utf-8');

        return $result->toString();
    }
}
